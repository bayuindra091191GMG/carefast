<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function editPassword(){
        return view('admin.setting.password');
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password_current'      => 'required|max:100',
            'password_new'          => 'required|max:100',
            'password_confirm'      => 'required|max:100'
        ],[
            'password_current.required'     => 'Kata Sandi Sekarang wajib diisi!',
            'password_new.required'         => 'Kata Sandi Baru wajib diisi!',
            'password_confirm.required'     => 'Konfirmasi Kata Sandi wajib diisi!'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        $passwordCurrent = $request->input('password_current');
        $passwordNew = $request->input('password_new');
        $passwordConfirm = $request->input('password_confirm');

        if($passwordNew !== $passwordConfirm){
            return back()->withErrors("Konfirmasi Kata Sandi harus sama dengan Kata Sandi Baru!")->withInput($request->all());
        }

        if($passwordNew == $passwordCurrent){
            return back()->withErrors("Kata Sandi Baru harus berbeda dengan Kata Sandi Sekarang!")->withInput($request->all());
        }

        $adminUser = Auth::guard('admin')->user();
        if(Hash::check($passwordCurrent, $adminUser->password)){
            $adminUser->password = Hash::make($passwordNew);
            $adminUser->save();
        }

        Session::flash('success','Berhasil mengubah kata sandi!');
        return redirect()->route('admin.setting.password.edit');
    }
}