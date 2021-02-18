<?php namespace App\Http\Controllers\ClientArea;

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
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class InvoicesController extends Controller {
   protected $product,$client,$tax,$currency,$invoice,$items,$setting,$number,$invoiceSetting, $template, $mail_setting,$logged_user;
   public function __construct(Invoice $invoice, Product $product, Client $client,  Tax $tax, Currency $currency, InvoiceItem $items, Setting $setting, Number $number, InvoiceSetting $invoiceSetting, Template $template, MailSetting $mail_setting){
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
       $this->middleware(function ($request, $next) {
           $this->logged_user = auth()->guard('user')->user();
           return $next($request);
       });
   }
	public function index()
	{
        if (Request::ajax()) {
            return DataTables::of($this->logged_user->invoices)
                ->editColumn('status', function($data){ return '<span class="label '.statuses()[$data->status]['class'].'">'.ucwords(statuses()[$data->status]['label']).'</span>'; })
                ->addColumn('grand_total', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['grandTotal']).'</span>';
                })->addColumn('paid', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['paid']).'</span>';
                })->addColumn('amountDue', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['amountDue']).'</span>';
                })->addColumn('action', function($data) {
                    $action_buttons = '<a href="'.route('cinvoice_pdf',$data->uuid).'" data-rel="tooltip" data-placement="top" title="{{trans(\'application.download_invoice\')}}" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a> '.
                             show_btn('cinvoices.show', $data->uuid);
                   // if($data->totals['amountDue'] > 0){
                       //$action_buttons .= ' <a href="'.url('clientarea/payment_methods',$data->uuid).'" data-rel="tooltip" data-toggle="ajax-modal" data-placement="top" title="{{trans(\'application.add_payment\')}}" class="btn btn-xs btn-warning"><i class="fa fa-usd"></i> </a>';
                    //}
                  return $action_buttons;
                })
                ->rawColumns(['status','grand_total','paid','amountDue','action','amountDue'])
                ->make(true);
        }else {
            return view('clientarea.invoices.index');
        }
	}
    /**
     * Display the specified resource.
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
	public function show($uuid)
	{
        $invoice = $this->invoice->getById($uuid);
        if ($invoice) {
            $settings = $this->setting->first();
            $invoiceSettings = $this->invoiceSetting->first();
            return view('clientarea.invoices.show', compact('invoice', 'settings', 'invoiceSettings'));
        }
	}
    /**
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function invoicePdf($uuid){
        $invoice = $this->invoice->with('items')->getById($uuid);
        if($invoice){
            $settings = $this->setting->first();
            $invoiceSettings = $this->invoiceSetting->first();
            $invoice->pdf_logo = $invoiceSettings && $invoiceSettings->logo ? base64_img(config('app.images_path').$invoiceSettings->logo) : '';
            $pdf = \PDF::loadView('clientarea.invoices.pdf', compact('settings', 'invoice', 'invoiceSettings'));
            return $pdf->download('invoice_'.$invoice->number.'_'.date('Y-m-d').'.pdf');
        }
        return Redirect::route('cinvoices');
    }
}
