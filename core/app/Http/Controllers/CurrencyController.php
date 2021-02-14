<?php namespace App\Http\Controllers;

use App\Http\Requests\CurrencyFormRequest;
use App\Invoicer\Repositories\Contracts\CurrencyInterface as Currency;
use Laracasts\Flash\Flash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Artisan;

class CurrencyController extends Controller {
    private $currency;
    public function __construct(Currency $currency){
        $this->middleware('permission:edit_setting');
        $this->currency = $currency;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$currencies = $this->currency->all();
        return view('settings.currency.index', compact('currencies'));
	}
    public function create(){
        return view('settings.currency.create');
    }
    /**
     * Store a newly created resource in storage.
     * @param CurrencyFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(CurrencyFormRequest $request)
	{
		$data = array('name' => $request->name,'exchange_rate'=>$request->exchange_rate, 'symbol' => $request->symbol, 'exchange_rate'=>$request->exchange_rate);
        if($this->currency->create($data))
            Flash::success(trans('application.record_created'));
        else
            Flash::error(trans('application.create_failed'));
        return redirect('settings/currency');
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$currency = $this->currency->getById($id);
        return view('settings.currency.edit', compact('currency'));
	}
    /**
     * Update the specified resource in storage.
     * @param CurrencyFormRequest $request
     * @param $id
     * @return Response
     */
    public function update(CurrencyFormRequest $request, $id)
	{
        $data = array('active' => $request->active,'exchange_rate'=>$request->exchange_rate, 'default_currency' => $request->default_currency);
        if($request->default_currency){
            $this->currency->resetDefault();
            $data['active'] = 1;
        }
        if($this->currency->updateById($id, $data)){
            Flash::success(trans('application.record_updated'));
            return Response::json(array('success' => true, 'msg' => trans('application.record_updated')), 201);
        }
        return Response::json(array('success' => false, 'msg' => trans('application.update_failed')), 422);
	}
	public function updateCurrencyRates(){
        Artisan::call('currency:update -o');
        echo nl2br(e(Artisan::output()));
    }
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if($this->currency->deleteById($id))
            return Response::success(trans('application.record_deleted'));
        else
            return Response::error(trans('application.delete_failed'));
	}
	public function save_api_key(){
	    $key = request('key');
	    if($key){
	        saveConfiguration(['OPENEXCHANGE_RATES_KEY'=>$key]);
	        return Response::json(['success'=>true,'message'=>trans('application.record_created')],200);
        }
    }
}
