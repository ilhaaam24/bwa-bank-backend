<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
}
