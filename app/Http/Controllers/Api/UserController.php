<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function show (){
        $user = getUser(auth()->user()->id);

        return response()->json([
            'status' => true,
            'message' => 'User fetched successfully',
            'data' => $user
        ]);
    }

    public function getUserByUsername(Request $request, $username){
        $users = User::select('id', 'name', 'username', 'profile_picture', 'verified')->where('username', 'LIKE', '%'.$username.'%')->where('id', '<>', auth()->user()->id)->get();

        $users = $users->map(function($item){
            $item->profile_picture = $item->profile_picture ? url('storage/'.$item->profile_picture) : "";
            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully',
            'data' => $users
        ]);
    }

    public function update(Request $request){

        try {
            $user = User::find(auth()->user()->id); 

            $data = $request->only('name', 'username','email' ,'ktp', 'password');

            if($request->username != $user->username){
                $usernameExist = User::where('username', $request->username)->exists();
                if($usernameExist){
                    return response()->json(['message' => 'Username already exists'], 409);
                }
            }

            if($request->email != $user->email){
                $emailExists = User::where('email', $request->email)->exists();
                if($emailExists){
                    return response()->json(['message' => 'Email already exists'], 409);
                }
            }
            
            if($request->password){
                $data['password'] = bcrypt($request->password);
            }

            if($request->profile_picture){
                $profilePicture = upload64Image($request->profile_picture);
                $data['profile_picture'] = $profilePicture;
                if($user->profile_picture){
                    Storage::delete('public/'.$user->profile_picture);
                }
            }

            if($request->ktp){
                $ktpPicture = upload64Image($request->ktp);
                $data['profile_picture'] = $ktpPicture;
                $data['verified'] = true;
                if($user->ktp){
                    Storage::delete('public/'.$user->ktp);
                }
            }

            $user->update($data);
            return response()->json(['message'=> 'User Updated']);
        } catch (\Throwable $th) {
            return response()->json(['message'=> $th->getMessage()], 500);
        }

    }

    public function isEmailExists(Request $request){
        $validator = Validator::make($request->only('email'),[
            'email'=> 'required|email'
        ]);

        if($validator->fails()){
            return response()->json(['message'=> $validator->messages()],400);
        }


        $isExists = User::where('email', $request->email)->exists();

        return response()->json(['is_email_exists'=> $isExists ]);

    }
}
