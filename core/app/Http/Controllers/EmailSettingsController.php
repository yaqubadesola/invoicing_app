<?php namespace App\Http\Controllers;

use App\Http\Requests\EmailSettingsRequest;
use App\Invoicer\Repositories\Contracts\EmailSettingInterface as Setting;
use Laracasts\Flash\Flash;

class EmailSettingsController extends Controller {

	private $setting;

	public function __construct(Setting $setting){
		$this->setting = $setting;
        $this->middleware('permission:edit_setting');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$setting = $this->setting->count() > 0 ? $this->setting->first() : array();
		return view('settings.email.index', compact('setting'));
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(EmailSettingsRequest $request)
	{
		$data =  array(
			'protocol'		    =>$request->protocol,
			'smtp_host' 	    =>$request->smtp_host,
			'smtp_username'     =>$request->smtp_username,
			'smtp_password'     =>$request->smtp_password,
			'smtp_port' 	    =>$request->smtp_port,
			'from_email' 	    =>$request->from_email,
			'mailgun_domain' 	=>$request->mailgun_domain,
			'mailgun_secret' 	=>$request->mailgun_secret,
			'mandrill_secret' 	=>$request->mandrill_secret,
			'from_name' 	    =>$request->from_name,
			'encryption' 	    =>$request->encryption
		);
		if($this->setting->create($data)){
		    saveConfiguration([
		        'MAIL_DRIVER'       =>$request->protocol,
		        'MAILGUN_DOMAIN'    =>$request->mailgun_domain,
                'MAILGUN_SECRET'    =>$request->mailgun_secret,
                'MANDRILL_SECRET'   =>$request->mandrill_secret,
                'MAIL_FROM_ADDRESS' =>$request->from_email,
                'MAIL_FROM_NAME'    =>$request->from_name,
                'MAIL_USERNAME'     =>$request->smtp_username,
                'MAIL_PASSWORD'     =>$request->smtp_password,
                'MAIL_HOST'         =>$request->smtp_host,
                'MAIL_PORT'         =>$request->smtp_port,
                'MAIL_ENCRYPTION'   =>$request->encryption
            ]);
			Flash::success(trans('application.record_updated'));
		}
		else{
			Flash::error(trans('application.update_failed'));
		}
		return redirect('settings/email');
	}
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(EmailSettingsRequest $request, $uuid)
	{
		$data =  array(
			'protocol'		    =>$request->protocol,
			'smtp_host' 	    =>$request->smtp_host,
			'smtp_username'     =>$request->smtp_username,
			'smtp_password'     =>$request->smtp_password,
			'smtp_port' 	    =>$request->smtp_port,
			'from_email' 	    =>$request->from_email,
            'mailgun_domain' 	=>$request->mailgun_domain,
            'mailgun_secret' 	=>$request->mailgun_secret,
            'mandrill_secret' 	=>$request->mandrill_secret,
            'from_name' 	    =>$request->from_name,
            'encryption' 	    =>$request->encryption
		);

		if($this->setting->updateById($uuid, $data)){
            saveConfiguration([
                'MAIL_DRIVER'       =>$request->protocol,
                'MAILGUN_DOMAIN'    =>$request->mailgun_domain,
                'MAILGUN_SECRET'    =>$request->mailgun_secret,
                'MANDRILL_SECRET'   =>$request->mandrill_secret,
                'MAIL_FROM_ADDRESS' =>$request->from_email,
                'MAIL_FROM_NAME'    =>$request->from_name,
                'MAIL_USERNAME'     =>$request->smtp_username,
                'MAIL_PASSWORD'     =>$request->smtp_password,
                'MAIL_HOST'         =>$request->smtp_host,
                'MAIL_PORT'         =>$request->smtp_port,
                'MAIL_ENCRYPTION'   =>$request->encryption
            ]);
			Flash::success(trans('application.record_updated'));
		}
		else{
			Flash::error(trans('application.update_failed'));
		}
		return redirect('settings/email');
	}
}
