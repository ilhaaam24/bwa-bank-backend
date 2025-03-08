<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
        try {
            $profilePicture = null;
            $ktp = null;

            if($request->profile_picture){
                $profilePicture = $this->upload64Image($request->profile_picture);
            }

            if($request->ktp){
                $ktp = $this->upload64Image($request->ktp);
            }



            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' =>$request->email,
                'password' => bcrypt($request->password),
                'profile_picture' => $profilePicture,
                'ktp' => $ktp,
                'verified' => ($ktp) ? true : false,
            ]);

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pin' => $request->pin,
                'card_number' => $this->generateCardNumber( 16 ),
            ]);

            DB::commit();


        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()],500);
        }


        return response()->json(['message' => 'success'],200);

    }


    private function generateCardNumber($length){
        $result = '';
        for($i = 0; $i < $length; $i++){
            $result .= mt_rand(0, 9);
        }

        $wallet = Wallet::where('card_number', $result)->exists();
        if($wallet){
            return $this->generateCardNumber($length);
        }
        return $result;
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
