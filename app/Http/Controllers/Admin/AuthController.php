<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){
        $credential = $request->only('email', 'password');

        if(auth()->guard('web')->attempt($credential)){
            return redirect()->route('admin.dashboard'); 
        }

        return redirect()->back()->with('error', 'Invalid Credential')->withInput();
    }
    public function logout(){
        auth()->guard('web')->logout();

        return redirect()->route('admin.auth.index');
    }
}
