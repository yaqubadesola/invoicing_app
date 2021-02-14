<?php namespace App\Http\Controllers\ClientArea;

use App\Invoicer\Repositories\Contracts\EstimateInterface as Estimate;
use App\Invoicer\Repositories\Contracts\EstimateItemInterface as EstimateItem;
use App\Invoicer\Repositories\Contracts\EstimateSettingInterface as EstimateSetting;
use App\Invoicer\Repositories\Contracts\ProductInterface as Product;
use App\Invoicer\Repositories\Contracts\TaxSettingInterface as Tax;
use App\Invoicer\Repositories\Contracts\ClientInterface as Client;
use App\Invoicer\Repositories\Contracts\CurrencyInterface as Currency;
use App\Invoicer\Repositories\Contracts\SettingInterface as Setting;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class EstimatesController extends Controller {
    protected $product,$tax,$client,$currency,$estimate,$estimateItem,$setting,$logged_user,$estimateSetting;
    public function __construct(Product $product,Tax $tax, Client $client, Currency $currency, Estimate $estimate, EstimateItem $estimateItem, Setting $setting,EstimateSetting $estimateSetting){
        $this->product = $product;
        $this->client = $client;
        $this->currency = $currency;
        $this->tax = $tax;
        $this->estimate = $estimate;
        $this->estimateItem = $estimateItem;
        $this->setting = $setting;
        $this->estimateSetting = $estimateSetting;
        $this->middleware(function ($request, $next) {
            $this->logged_user = auth()->guard('user')->user()->uuid;
            return $next($request);
        });
    }
	/**
	 * Display a listing of the resource.
	 *
	 */
	public function index()
	{
        if (Request::ajax()) {
            $model = $this->estimate->model();
            $estimates = $model::where('client_id',$this->logged_user)->select('client_id','estimate_no','estimate_date','uuid','currency')->ordered();
            return DataTables::of($estimates)
                ->addColumn('amount', function($data){
                    return '<span style="display:inline-block">'.$data->currency.'</span> <span style="display:inline-block"> '.format_amount($data->totals['grandTotal']).'</span>';
                })->addColumn('action', function($data) {
                    return '<a href="'.route('cestimate_pdf',$data->uuid).'" data-rel="tooltip" data-placement="top" title="{{trans(\'application.download_estimate\')}}" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a> '.
                        show_btn('cestimates.show', $data->uuid);
                })
                ->rawColumns(['action','amount'])
                ->make(true);
        }else {
            return view('clientarea.estimates.index');
        }
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
            return view('clientarea.estimates.show', compact('estimate', 'settings','estimate_settings'));
        }
        return Redirect::route('cestimates');
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
            $pdf = \PDF::loadView('clientarea.estimates.pdf', compact('settings', 'estimate','estimate_settings'));
            return $pdf->download('estimate_'.$estimate->estimate_no.'_'.date('Y-m-d').'.pdf');
        }
        return Redirect::route('cestimates');
    }
}
