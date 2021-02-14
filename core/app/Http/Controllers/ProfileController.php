<?php namespace App\Http\Controllers;

use App\Http\Requests\ProfileFormRequest;
use App\Invoicer\Repositories\Contracts\ProfileInterface as Profile;
use Illuminate\Support\Str;

class ProfileController extends Controller {
    private $profile;
    public function __construct(Profile $profile){
        $this->profile = $profile;
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(){
        if (auth()->guard('admin')->user()){
            $user = $this->profile->getById(auth()->guard('admin')->user()->uuid);
            return view('users.profile', compact('user'));
        }
        return redirect('profile');
	}
    /**
     * Update the specified resource in storage.
     * @param ProfileFormRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(ProfileFormRequest $request){
        if (auth()->guard('admin')->user()){
            $user = $this->profile->getById(auth()->guard('admin')->user()->uuid);
            $data =  array(
                      'username'=>$request->username,
                      'name'=>$request->name,
                      'email'=>$request->email,
                      'phone'=> $request->phone,
            );
            if ($request->hasFile('photo')){
                $file = $request->file('photo');
                $filename = strtolower(Str::random(50) . '.' . $file->getClientOriginalExtension());
                $file->move(config('app.uploads_path'), $filename);
                \Image::make(sprintf(config('app.uploads_path').'%s', $filename))->resize(200, 200)->save();
                \File::delete(config('app.uploads_path').$user->photo);
                $data['photo']= $filename;
            }
            if($request->get('password') != ''){
                $data['password']= bcrypt($request->password);
            }
            $this->profile->updateById($user->uuid, $data);
            flash()->success('Profile updated');
        }
        return redirect('profile');
	}
}
