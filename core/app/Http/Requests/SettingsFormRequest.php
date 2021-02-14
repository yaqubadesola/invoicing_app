<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class SettingsFormRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
        return auth()->guard('admin')->check();
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
        $rules =
            [   'name' => 'required',
                'email'    => 'required|email',
                'phone'    => 'required',
                'address1' => 'required',
                'city'     => 'required',
                'state'    => 'required',
                'country'  => 'required',
                'logo'     => 'image|max:2000',
                'favicon'  => 'mimes:png|image|dimensions:width=16,height=16',
            ];
        return $rules;
	}

}
