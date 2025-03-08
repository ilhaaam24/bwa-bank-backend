<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;

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

        try {
            $profilePicture = null;
            $ktp = null;

            if($request->profile_picture){
                $profilePicture = $this->upload64Image($request->profile_picture);
            }

            if($request->ktp){
                $ktp = $this->upload64Image($request->ktp);
            }




        } catch (\Throwable $th) {
            echo $th;
            return response()->json(['message' => 'Failed to upload image'],500);
        }


        return response()->json(['message' => 'success'],200);

    }
    private function upload64Image($image64){
        $decoder = new Base64ImageDecoder($image64, $allowedFormats = ['jpeg', 'png', 'gif', 'jpg']);
        $deocedContent = $decoder->getDecodedContent();
        $format = $decoder->getFormat();
        $image = Str::random(10).'.'.$format;

        Storage::disk('public')->put($image, $deocedContent);

        return $image;
    }
}
