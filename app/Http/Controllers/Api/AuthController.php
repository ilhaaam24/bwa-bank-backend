<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Wallet;

class AuthController extends Controller
{
    //

    public function register(Request $request){
        $data = $request->all();

        $validator = Validator::make($data,[
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'pin'=> 'required|digits:6',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()],400);
        }

        $user = User::where('email', $request->email)->exists();

        if($user){
            return response()->json(['message' => 'Email already exists'],409);
        }


        return response()->json(['message' => 'success'],200);

    }
}
