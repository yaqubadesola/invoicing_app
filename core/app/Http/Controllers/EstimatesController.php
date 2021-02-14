<?php namespace App\Http\Controllers;

use App\Http\Requests\EstimateFormRequest;
use App\Http\Requests\SendEmailFrmRequest;
use App\Invoicer\Repositories\Contracts\EstimateInterface as Estimate;
use App\Invoicer\Repositories\Contracts\EstimateItemInterface as EstimateItem;
use App\Invoicer\Repositories\Contracts\ProductInterface as Product;
use App\Invoicer\Repositories\Contracts\TaxSettingInterface as Tax;
use App\Invoicer\Repositories\Contracts\ClientInterface as Client;
use App\Invoicer\Repositories\Contracts\CurrencyInterface as Currency;
use App\Invoicer\Repositories\Contracts\SettingInterface as Setting;
use App\Invoicer\Repositories\Contracts\NumberSettingInterface as Number;
use App\Invoicer\Repositories\Contracts\TemplateInterface as Template;
use App\Invoicer\Repositories\Contracts\EstimateSettingInterface as EstimateSetting;
use App\Invoicer\Repositories\Contracts\EmailSettingInterface as MailSetting;
use App\Invoicer\Repositories\Contracts\InvoiceInterface as Invoice;
use App\Invoicer\Repositories\Contracts\InvoiceSettingInterface as InvoiceSetting;
use App\Invoicer\Repositories\Contracts\InvoiceItemInterface as InvoiceItem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Laracasts\Flash\Flash;
use Yajra\DataTables\DataTables;
use PDF;
use Mail;

class EstimatesController extends Controller {
    protected $product,$tax,$client,$currency,$estimate,$estimateItem,$setting, $number,$template,$estimateSetting,$mail_setting,$invoiceSetting,$invoiceItem,$invoice;
    public function __construct(Product $product,Tax $tax, Client $client, Currency $currency, Estimate $estimate, EstimateItem $estimateItem, Setting $setting, Number $number,Template $template, EstimateSetting $estimateSetting, MailSetting $mail_setting,InvoiceSetting $invoiceSetting,InvoiceItem $invoiceItem,Invoice $invoice ){
        $this->product = $product;
        $this->client = $client;
        $this->currency = $currency;
        $this->tax = $tax;
        $this->estimate = $estimate;
        $this->estimateItem = $estimateItem;
        $this->setting = $setting;
        $this->number = $number;
        $this->template = $template;
        $this->estimateSetting = $estimateSetting;
        $this->mail_setting = $mail_setting;
        $this->invoiceSetting = $invoiceSetting;
        $this->invoiceItem = $invoiceItem;
        $this->invoice = $invoice;
    }
	public function index()
	{
        if (Request::ajax()) {
            $model = $this->estimate->model();
            $estimates = $model::ordered();
            return DataTables::of($estimates)
                ->editColumn('name', function($data){ return '<a href="'.route('clients.show', $data->client_id).'">'.$data->client->name.'</a>'; })
                ->addColumn('amount', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['grandTotal']).'</span>';
                })->addColumn('action', function ($row) {
                    $action_buttons = '<a href="'.route('estimate_pdf',$row->uuid).'" data-rel="tooltip" data-placement="top" title="'.trans('application.download_estimate').'" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a> ';
                    if(hasPermission('view_estimate')) {
                        $action_buttons .= show_btn('estimates.show', $row->uuid);
                    }
                    if(hasPermission('edit_estimate')) {
                        $action_buttons .= ' <a href = "'.route('estimates.edit',$row->uuid).'" data-rel="tooltip" data-placement = "top" title = "'.trans('application.edit_estimate').'" class="btn btn-xs btn-success"><i class="fa fa-pencil"></i></a>';
                    }
                    if(hasPermission('delete_estimate')){
                        $action_buttons .=  delete_btn('estimates.destroy', $row->uuid);
                    }
                    return $action_buttons;
                })
                ->rawColumns(['name','amount','action'])
                ->make(true);
        }else {
            return view('estimates.index');
        }
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
        if(!hasPermission('add_estimate', true)) return redirect('estimates');
        $settings     = $this->estimateSetting->first();
        $start        = $settings ? $settings->start_number : 0;
        $estimate_num = $this->number->prefix('estimate_number', $this->estimate->generateEstimateNum($start));
        $products     = $this->product->productSelect();
        $clients      = $this->client->clientSelect();
        $taxes        = $this->tax->taxSelect();
        $currencies   = $this->currency->currencySelect();
        $default_currency = $this->currency->defaultCurrency();
		return view('estimates.create', compact('products', 'taxes', 'currencies', 'clients', 'estimate_num','settings','default_currency'));
	}

    //Custome to Add client quotation/estimate from client menu
    public function client_estimate($client_id)
	{   
        //dd("id",  $client_id );
        if(!hasPermission('add_estimate', true)) return redirect('estimates');
        $settings     = $this->estimateSetting->first();
        $start        = $settings ? $settings->start_number : 0;
        $estimate_num = $this->number->prefix('estimate_number', $this->estimate->generateEstimateNum($start));
        $products     = $this->product->productSelect();
        $clients      = $this->client->clientSelect($client_id);
        $taxes        = $this->tax->taxSelect();
        $currencies   = $this->currency->currencySelect();
        $default_currency = $this->currency->defaultCurrency();
		return view('estimates.create', compact('products', 'taxes', 'currencies', 'clients', 'estimate_num','settings','default_currency'));
	}
    /**
     * Store a newly created resource in storage.
     * @param EstimateFormRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(EstimateFormRequest $request)
	{
        $estimateData = array(
            'client_id'     => $request->get('client'),
            'estimate_no'   => $request->get('estimate_no'),
            'estimate_date' => date('Y-m-d', strtotime($request->get('estimate_date'))),
            'notes'         => $request->get('notes'),
            'terms'         => $request->get('terms'),
            'currency'      => $request->get('currency')
        );
        $estimate = $this->estimate->create($estimateData);
        if($estimate){
            $items = json_decode($request->get('items'));
            foreach($items as $item_order=>$item){
                $itemsData = array(
                    'estimate_id'           => $estimate->uuid,
                    'item_name'             => $item->item_name,
                    'item_description'      => $item->item_description,
                    'quantity'              => $item->quantity,
                    'price'                 => $item->price,
                    'tax_id'                => $item->tax != '' ? $item->tax : null,
                    'item_order'            => $item_order+1
                );
                $this->estimateItem->create($itemsData);
            }

            $settings     = $this->estimateSetting->first();
            if($settings){
                $start = $settings->start_number+1;
                $this->estimateSetting->updateById($settings->uuid, array('start_number'=>$start));
            }
            return Response::json(array('success' => true,'redirectTo'=>route('estimates.show', $estimate->uuid), 'msg' => trans('application.record_created')), 200);
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
        $estimate = $this->estimate->getById($uuid);
        if($estimate){
            $settings = $this->setting->first();
            $estimate_settings = $this->estimateSetting->first();
            return view('estimates.show', compact('estimate', 'settings','estimate_settings'));
        }
        return Redirect::route('estimates.index');
	}
    /**
     * Show the form for editing the specified resource.
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
	public function edit($uuid)
	{
        if(!hasPermission('edit_estimate', true)) return redirect('estimates');
        $estimate = $this->estimate->getById($uuid);
        if($estimate){
            $products = $this->product->productSelect();
            $clients = $this->client->clientSelect();
            $taxes = $this->tax->taxSelect();
            $currencies = $this->currency->currencySelect();
            return view('estimates.edit', compact('estimate','products', 'taxes', 'currencies', 'clients'));
        }
        return Redirect::route('estimates.index');
	}
    /**
     * Update the specified resource in storage.
     * @param EstimateFormRequest $request
     * @param $uuid
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function update(EstimateFormRequest $request, $uuid)
	{
        $estimateData = array(
            'client_id'     => $request->get('client'),
            'estimate_no'   => $request->get('estimate_no'),
            'estimate_date' => date('Y-m-d', strtotime($request->get('estimate_date'))),
            'notes'         => $request->get('notes'),
            'terms'         => $request->get('terms'),
            'currency'      => $request->get('currency')
        );
        $estimate = $this->estimate->updateById($uuid, $estimateData);
        if($estimate){
            $items = json_decode($request->get('items'));
            foreach($items as $item_order=>$item){
                $itemsData = array(
                    'estimate_id'       => $estimate->uuid,
                    'item_name'         => $item->item_name,
                    'item_description'  => $item->item_description,
                    'quantity'          => $item->quantity,
                    'price'             => $item->price,
                    'tax_id'            => $item->tax != '' ? $item->tax : null,
                    'item_order'        => $item_order+1
                );

                if(isset($item->itemId))
                    $this->estimateItem->updateById($item->itemId,$itemsData);
                else
                    $this->estimateItem->create($itemsData);
            }
            return Response::json(array('success' => true,'redirectTo'=>route('estimates.show', $estimate->uuid), 'msg' => trans('application.record_updated')), 200);
        }
        return Response::json(array('success' => false, 'msg' => trans('application.record_update_failed')), 400);
	}
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteItem(){
        $uuid = request('id');
        if($this->estimateItem->deleteById($uuid))
            return Response::json(array('success' => true, 'msg' => trans('application.record_deleted')), 200);

        return Response::json(array('success' => false, 'msg' => trans('application.record_deletion_failed')), 400);
    }
    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function estimatePdf($uuid){
        $estimate = $this->estimate->getById($uuid);
        if($estimate){
            $settings = $this->setting->first();
            $estimate_settings = $this->estimateSetting->first();
            $estimate->estimate_logo = $estimate_settings && $estimate_settings->logo ? base64_img(config('app.images_path').$estimate_settings->logo) : '';
            $pdf = PDF::loadView('estimates.pdf', compact('settings', 'estimate','estimate_settings'));
            return $pdf->download('estimate_'.$estimate->estimate_no.'_'.date('Y-m-d').'.pdf');
        }
        return Redirect::route('estimates.index');
    }
    public function send_modal($uuid){
        $estimate = $this->estimate->getById($uuid);
        $template = $this->template->where('name', 'estimate')->first();
        return view('estimates.send_modal',compact('estimate','template'));
    }
    public function send(SendEmailFrmRequest $request){
        try {
        $uuid = $request->get('estimate_id');
        $estimate = $this->estimate->getById($uuid);
        $settings = $this->setting->first();
        $estimate_settings = $this->estimateSetting->first();
        $data_object = new \stdClass();
        $data_object->settings  = $settings;
        $data_object->client    = $estimate->client;
        $data_object->user = $estimate->client;
        $estimate->estimate_logo = $estimate_settings && $estimate_settings->logo ? base64_img(config('app.images_path').$estimate_settings->logo) : '';
        $pdf_name = 'estimate_' . $estimate->estimate_no . '_' . date('Y-m-d') . '.pdf';
        PDF::loadView('estimates.pdf', compact('settings', 'estimate', 'estimates_settings'))->save(config('app.assets_path').'attachments/'.$pdf_name);
        $params = [
            'data' => [
                'emailBody'=>parse_template($data_object, $request->get('message')),
                'emailTitle'=>parse_template($data_object,$request->get('subject')),
                'attachment' => config('app.assets_path').'attachments/'.$pdf_name
            ],
            'to' => $request->get('email'),
            'template_type' => 'markdown',
            'template' => 'emails.invoicer-mailer',
            'subject' => parse_template($data_object,$request->get('subject'))
        ];
            sendmail($params);
            Flash::success(trans('application.email_sent'));
            return response()->json(['type' => 'success','message' => trans('application.email_sent')]);
        }catch (\Exception $exception){
            $error = $exception->getMessage();
            Flash::error($error);
            return response()->json(['type' => 'fail', 'message' => $error],422);
        }
    }
    public function makeInvoice(){
        $uuid = request()->get('id');
        $estimate = $this->estimate->getById($uuid);
        $settings     = $this->invoiceSetting->first();
        $start        = $settings ? $settings->start_number : 0;
        $invoice_num  = $this->number->prefix('invoice_number', $this->invoice->generateInvoiceNum($start));
        $invoiceData = array(
            'client_id'     => $estimate->client_id,
            'number'        => $invoice_num,
            'invoice_date'  => date('Y-m-d'),
            'notes'         => $estimate->notes,
            'terms'         => $estimate->terms,
            'currency'      => $estimate->currency,
            'status'        => '0',
            'discount'      => 0,
            'recurring'     => 0,
            'recurring_cycle' => 1,
            'due_date' => date('Y-m-d')
        );
        $invoice = $this->invoice->create($invoiceData);
        if($invoice) {
            $items = $estimate->items;
            foreach ($items as $item) {
                $itemsData = array(
                    'invoice_id' => $invoice->uuid,
                    'item_name' => $item->item_name,
                    'item_description' => $item->item_description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'tax_id' => $item->tax != '' ? $item->tax->uuid : null,
                );
                $this->invoiceItem->create($itemsData);
            }
            $settings = $this->invoiceSetting->first();
            if ($settings) {
                $start = $settings->start_number + 1;
                $this->invoiceSetting->updateById($settings->uuid, array('start_number' => $start));
            }
            return Response::json(array('success' => true, 'redirectTo'=>route('invoices.show',$invoice->uuid), 'msg' => trans('application.record_created')), 200);
        }else{
            return Response::json(array('success' => false, 'msg' => trans('application.record_creation_failed')), 400);
        }
    }
	public function destroy($uuid)
	{
        if(!hasPermission('delete_estimate', true)) return redirect('estimates');
        if($this->estimate->deleteById($uuid)){
            Flash::success(trans('application.record_deleted'));
            return Redirect::route('estimates.index');
        }
        Flash::error(trans('application.record_deletion_failed'));
        return Redirect::route('estimates.index');
	}
}
