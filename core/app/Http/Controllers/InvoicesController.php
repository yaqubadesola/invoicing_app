<?php namespace App\Http\Controllers;

use App\Http\Requests\InvoiceFromRequest;
use App\Http\Requests\SendEmailFrmRequest;
use Illuminate\Support\Facades\Request;
use App\Invoicer\Repositories\Contracts\InvoiceInterface as Invoice;
use App\Invoicer\Repositories\Contracts\ProductInterface as Product;
use App\Invoicer\Repositories\Contracts\ClientInterface as Client;
use App\Invoicer\Repositories\Contracts\TaxSettingInterface as Tax;
use App\Invoicer\Repositories\Contracts\CurrencyInterface as Currency;
use App\Invoicer\Repositories\Contracts\InvoiceItemInterface as InvoiceItem;
use App\Invoicer\Repositories\Contracts\SettingInterface as Setting;
use App\Invoicer\Repositories\Contracts\NumberSettingInterface as Number;
use App\Invoicer\Repositories\Contracts\InvoiceSettingInterface as InvoiceSetting;
use App\Invoicer\Repositories\Contracts\TemplateInterface as Template;
use App\Invoicer\Repositories\Contracts\EmailSettingInterface as MailSetting;
use App\Invoicer\Repositories\Contracts\SubscriptionInterface as Subscription;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Laracasts\Flash\Flash;
use Yajra\DataTables\DataTables;
use PDF;
use Mail;


class InvoicesController extends Controller {
   protected $product,$client,$tax,$currency,$invoice,$items,$setting,$number,$invoiceSetting, $template, $mail_setting,$subscription;
   public function __construct(Invoice $invoice, Product $product, Client $client,  Tax $tax, Currency $currency, InvoiceItem $items, Setting $setting, Number $number, InvoiceSetting $invoiceSetting, Template $template, MailSetting $mail_setting, Subscription $subscription){
       $this->invoice   = $invoice;
       $this->product   = $product;
       $this->client    = $client;
       $this->tax       = $tax;
       $this->currency  = $currency;
       $this->items     = $items;
       $this->setting   = $setting;
       $this->number    = $number;
       $this->invoiceSetting = $invoiceSetting;
       $this->template  = $template;
       $this->mail_setting = $mail_setting;
       $this->subscription = $subscription;
   }

	public function index()
	{
        if (Request::ajax()) {
            $model = $this->invoice->model();
            $invoices = $model::select('client_id','number','status','due_date','uuid','currency')->ordered();
            return DataTables::of($invoices)
                ->editColumn('name', function($data){ return '<a href="'.route('clients.show', $data->client_id).'">'.$data->client->name ?? ''.'</a>'; })
                ->editColumn('status', function($data){ return '<span class="label '.statuses()[$data->status]['class'].'">'.ucwords(statuses()[$data->status]['label']).'</span>'; })
                ->addColumn('grand_total', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['grandTotal']).'</span>';
                })->addColumn('paid', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['paid']).'</span>';
                })->addColumn('amountDue', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['amountDue']).'</span>';
                })->addColumn('action',function($row){
                    $buttons_html = '<a href="'.route('invoice_pdf',$row->uuid).'" data-rel="tooltip" data-placement="top" title="'.trans('application.download_invoice').'" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a> ';
                    if(hasPermission('view_invoice')) {
                        $buttons_html .= show_btn('invoices.show', $row->uuid);
                    }
                    if(hasPermission('add_payment')){
                        $buttons_html .=' <a href="'.route('payments.create','invoice_id='.$row->uuid).'" data-rel="tooltip" data-toggle="ajax-modal" data-placement="top" title="'.trans('application.add_payment').'" class="btn btn-xs btn-warning"><i class="fa fa-ngn">&#8358;</i> </a>';
                    }
                    if(hasPermission('edit_invoice')) {
                        $buttons_html .=' <a href="'.route('invoices.edit',$row->uuid).'" data-rel="tooltip" data-placement="top" title="'.trans('application.edit_invoice').'" class="btn btn-xs btn-success"><i class="fa fa-pencil"></i></a>';
                    }
                    if(hasPermission('delete_invoice')) {
                        $buttons_html .= delete_btn('invoices.destroy', $row->uuid);
                    }
                    return $buttons_html;
                })
                ->rawColumns(['status','name','grand_total','paid','amountDue','action'])
                ->make(true);
        }else {
            return view('invoices.index');
        }
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
        if(!hasPermission('add_invoice', true)) return redirect('invoices');
        $settings     = $this->invoiceSetting->first();
        $start        = $settings ? $settings->start_number : 0;
        $invoice_num  = $this->number->prefix('invoice_number', $this->invoice->generateInvoiceNum($start));
        $clients    = $this->client->clientSelect();
        $taxes      = $this->tax->taxSelect();
        $currencies = $this->currency->currencySelect();
        $default_currency = $this->currency->defaultCurrency();
        $statuses   = statuses();
        return view('invoices.create', compact('invoice_num', 'clients','taxes','currencies', 'statuses', 'settings','default_currency'));
	}

    public function client_invoice($client_id)
	{
        if(!hasPermission('add_invoice', true)) return redirect('invoices');
        $settings     = $this->invoiceSetting->first();
        $start        = $settings ? $settings->start_number : 0;
        $invoice_num  = $this->number->prefix('invoice_number', $this->invoice->generateInvoiceNum($start));
        $clients    = $this->client->clientSelect($client_id);
        $taxes      = $this->tax->taxSelect();
        $currencies = $this->currency->currencySelect();
        $default_currency = $this->currency->defaultCurrency();
        $statuses   = statuses();
        return view('invoices.create', compact('invoice_num', 'clients','taxes','currencies', 'statuses', 'settings','default_currency'));
	}

    /**
     * Store a newly created resource in storage.
     * @param InvoiceFromRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(InvoiceFromRequest $request)
	{
	    $due_date = $request->get('due_date');
        $invoiceData = array(
            'client_id'     => $request->get('client'),
            'number'        => $request->get('number'),
            'invoice_date'  => date('Y-m-d', strtotime($request->get('invoice_date'))),
            'notes'         => $request->get('notes'),
            'terms'         => $request->get('terms'),
            'currency'      => $request->get('currency'),
            'status'        => $request->get('status'),
            'discount'      => $request->get('discount') != '' ? $request->get('discount') : 0,
            'discount_mode' => $request->get('discount_mode'),
            'recurring'     => $request->get('recurring'),
            'recurring_cycle' => $request->get('recurring_cycle')
        );
        if($due_date != ''){
            $invoiceData['due_date'] = date('Y-m-d', strtotime($request->get('due_date')));
        }
        $invoice = $this->invoice->create($invoiceData);
        if($invoice){
            $items = json_decode($request->get('items'));
            foreach($items as $item_order=>$item){
                $itemsData = array(
                    'invoice_id'        => $invoice->uuid,
                    'item_name'         => $item->item_name,
                    'item_description'  => $item->item_description,
                    'quantity'          => $item->quantity,
                    'price'             => $item->price,
                    'tax_id'            => $item->tax != '' ? $item->tax : null,
                    'item_order'        => $item_order+1
                );
               $this->items->create($itemsData);
            }
            $settings     = $this->invoiceSetting->first();
            if($settings){
                $start = $settings->start_number+1;
                $this->invoiceSetting->updateById($settings->uuid, array('start_number'=>$start));
            }
            if($request->get('recurring') == 1){
                $cycle = $request->get('recurring_cycle');
                $invoice_date = strtotime($invoice->invoice_date);
                switch ($cycle) {
                    case 1:
                        $next_due_date = date("Y-m-d", strtotime("+1 month", $invoice_date));
                        break;
                    case 2:
                        $next_due_date = date("Y-m-d", strtotime("+3 month", $invoice_date));
                        break;
                    case 3:
                        $next_due_date = date("Y-m-d", strtotime("+6 month", $invoice_date));
                        break;
                    case 4:
                        $next_due_date = date("Y-m-d", strtotime("+12 month", $invoice_date));
                        break;
                    default:
                        $next_due_date = date("Y-m-d", strtotime("+12 month", $invoice_date));
                }
                $subscriptionData = array(
                    'invoice_id' => $invoice->uuid,
                    'billingcycle' => $cycle,
                    'nextduedate' => $next_due_date,
                    'status' => '1'
                );
                $this->subscription->create($subscriptionData);
            }
            return Response::json(array('success' => true,'redirectTo'=>route('invoices.show', $invoice->uuid), 'msg' =>  trans('application.record_created')), 200);
        }
        return Response::json(array('success' => false, 'msg' => trans('application.record_creation_failed')), 400);
	}
    /**
     * Display the specified resource.
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
	public function show($uuid)
	{
        if(!hasPermission('view_invoice', true)) return redirect('invoices');
        $invoice = $this->invoice->getById($uuid);
      
        if ($invoice) {
            $settings = $this->setting->first();
            $invoiceSettings = $this->invoiceSetting->first();
            //dd($invoiceSettings);
            return view('invoices.show', compact('invoice', 'settings', 'invoiceSettings'));
        }
        return Redirect::route('invoices.index');
	}
    /**
     * Show the form for editing the specified resource.
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
	public function edit($uuid)
	{
        if(!hasPermission('edit_invoice', true)) return redirect('invoices');
        $invoice = $this->invoice->getById($uuid);
        if ($invoice) {
            $clients = $this->client->clientSelect();
            $taxes = $this->tax->taxSelect();
            $currencies = $this->currency->currencySelect();
            $statuses = statuses();
            return view('invoices.edit', compact('invoice', 'clients', 'taxes', 'currencies', 'statuses'));
        }
        return Redirect::route('invoices.index');
	}
    /**
     * Update the specified resource in storage.
     * @param InvoiceFromRequest $request
     * @param $uuid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(InvoiceFromRequest $request, $uuid)
	{
        $due_date = $request->get('due_date');
        $invoiceData = array(
            'client_id'     => $request->get('client'),
            'number'        => $request->get('number'),
            'invoice_date'  => date('Y-m-d', strtotime($request->get('invoice_date'))),
            'notes'         => $request->get('notes'),
            'terms'         => $request->get('terms'),
            'currency'      => $request->get('currency'),
            'status'        => $request->get('status'),
            'discount'      => $request->get('discount') != '' ? $request->get('discount') : 0,
            'discount_mode' => $request->get('discount_mode'),
            'recurring'     => $request->get('recurring'),
            'recurring_cycle' => $request->get('recurring_cycle')
        );
        if($due_date != ''){
            $invoiceData['due_date'] = date('Y-m-d', strtotime($request->get('due_date')));
        }
        $invoice = $this->invoice->updateById($uuid, $invoiceData);
        if($invoice){
            $items = json_decode($request->get('items'));
            foreach($items as $item_order=>$item){
                $itemsData = array(
                    'invoice_id'         => $invoice->uuid,
                    'item_name'          => $item->item_name,
                    'item_description'   => $item->item_description,
                    'quantity'           => $item->quantity,
                    'price'              => $item->price,
                    'tax_id'             => $item->tax != '' ? $item->tax : null,
                    'item_order'         => $item_order+1
                );

                if(isset($item->itemId))
                    $this->items->updateById($item->itemId,$itemsData);
                else
                    $this->items->create($itemsData);
            }
            $this->invoice->changeStatus($uuid);
            $cycle = $request->get('recurring_cycle');
            $model = $this->subscription->model();
            $subscription = $model::where('invoice_id',$invoice->uuid)->first();
            if($subscription){
                if($request->get('recurring') == 1) {
                    $today = date('Y-m-d');
                    if(strtotime($subscription->nextduedate) <= strtotime($today)) {
                        switch ($cycle) {
                            case 1:
                                $next_due_date = date("Y-m-d", strtotime("+1 month", strtotime($today)));
                                break;
                            case 2:
                                $next_due_date = date("Y-m-d", strtotime("+3 month", strtotime($today)));
                                break;
                            case 3:
                                $next_due_date = date("Y-m-d", strtotime("+6 month", strtotime($today)));
                                break;
                            case 4:
                                $next_due_date = date("Y-m-d", strtotime("+12 month", strtotime($today)));
                                break;
                            default:
                                $next_due_date = date("Y-m-d", strtotime("+12 month", strtotime($today)));
                        }
                    }
                    else{
                        $next_due_date = $subscription->nextduedate;
                    }
                    $subscriptionData = array(
                        'invoice_id' => $invoice->uuid,
                        'billingcycle' => $cycle,
                        'nextduedate' => $next_due_date,
                        'status' => '1'
                    );
                    $this->subscription->updateById($subscription->uuid,$subscriptionData);
                }else{
                    $subscriptionData = array(
                        'status' => '0'
                    );
                    $this->subscription->updateById($subscription->uuid,$subscriptionData);
                }
            }else {
                if ($request->get('recurring') == 1) {
                    $invoice_date = strtotime($invoice->invoice_date);
                    switch ($cycle) {
                        case 1:
                            $next_due_date = date("Y-m-d", strtotime("+1 month", $invoice_date));
                            break;
                        case 2:
                            $next_due_date = date("Y-m-d", strtotime("+3 month", $invoice_date));
                            break;
                        case 3:
                            $next_due_date = date("Y-m-d", strtotime("+6 month", $invoice_date));
                            break;
                        case 4:
                            $next_due_date = date("Y-m-d", strtotime("+12 month", $invoice_date));
                            break;
                        default:
                            $next_due_date = date("Y-m-d", strtotime("+12 month", $invoice_date));
                    }
                    $subscriptionData = array(
                        'invoice_id' => $invoice->uuid,
                        'billingcycle' => $cycle,
                        'nextduedate' => $next_due_date,
                        'status' => '1'
                    );
                    $this->subscription->create($subscriptionData);
                }
            }
            return Response::json(array('success' => true,'redirectTo'=>route('invoices.show', $invoice->uuid), 'msg' => trans('application.record_updated')), 200);
        }
        return Response::json(array('success' => false, 'msg' => trans('application.record_update_failed')), 400);
	}
    /**
     * @return mixed
     */
    public function ajaxSearch(){
        return $this->invoice->ajaxSearch();
    }
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteItem(){
        $id = request('id');
        if($this->items->deleteById($id)) {
            return Response::json(array('success' => true, 'msg' => trans('application.record_deleted')), 201);
        }
        return Response::json(array('success' => false, 'msg' => trans('application.record_deletion_failed')), 400);
    }
    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function invoicePdf($uuid){
        $invoice = $this->invoice->getById($uuid);
        if($invoice){
            $settings = $this->setting->first();
            $invoiceSettings = $this->invoiceSetting->first();
            $invoice->pdf_logo = $invoiceSettings && $invoiceSettings->logo ? base64_img(config('app.images_path').$invoiceSettings->logo) : '';
            $pdf = PDF::loadView('invoices.pdf', compact('settings', 'invoice', 'invoiceSettings'));
            return $pdf->download('invoice_'.$invoice->number.'_'.date('Y-m-d').'.pdf');
        }
        return Redirect::route('invoices');
    }
    public function send_modal($uuid){
        $invoice = $this->invoice->getById($uuid);
        $template = $this->template->where('name', 'invoice')->first();
        return view('invoices.send_modal',compact('invoice','template'));
    }

    public function send(SendEmailFrmRequest $request){
        //dd($request->all());
        $uuid = $request->get('invoice_id');
        $invoice = $this->invoice->getById($uuid);
        $settings = $this->setting->first();
        $invoiceSettings = $this->invoiceSetting->first();
        $data_object = new \stdClass();
        $data_object->invoice = $invoice;
        $data_object->settings = $settings;
        $data_object->client = $invoice->client;
        $data_object->user = $invoice->client;
        $invoice->pdf_logo = $invoiceSettings && $invoiceSettings->logo ? base64_img(config('app.images_path').$invoiceSettings->logo) : '';
        $pdf_name = 'invoice_' . $invoice->number . '_' . date('Y-m-d') . '.pdf';
        PDF::loadView('invoices.pdf', compact('settings', 'invoice', 'invoiceSettings'))->save(config('app.assets_path').'attachments/'.$pdf_name);
        $params = [
            'data' => [
                'emailBody'=>parse_template($data_object, $request->get('message')),
                'emailTitle'=>parse_template($data_object,$request->get('subject')),
                'attachment' => config('app.assets_path').'attachments/'.$pdf_name
            ],
            'to' => $request->get('email'),
            'cc' => $request->get('cc_email'),
            'bcc' => $request->get('bcc_email'),
            'template_type' => 'markdown',
            'template' => 'emails.invoicer-mailer',
            'subject' => parse_template($data_object,$request->get('subject'))
        ];
        
        try {
            //dd($params);
            sendmail($params);
            Flash::success(trans('application.email_sent'));
            return response()->json(['type' => 'success', 'message' => trans('application.email_sent')]);
        }catch (\Exception $exception){
            $error = $exception->getMessage();
            Flash::error($error);
            return response()->json(['type' => 'fail','message' => $error],422);
        }
    }
    /**
     * Remove the specified resource from storage.
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	public function destroy($uuid)
	{
        if(!hasPermission('send_invoice', true)) return redirect('invoices');
        if ($this->invoice->deleteById($uuid)) {
            Flash::success(trans('application.record_deleted'));
            return redirect('invoices');
        }
        Flash::error(trans('application.record_deletion_failed'));
        return redirect('invoices');
	}
}
