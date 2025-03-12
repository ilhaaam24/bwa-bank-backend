<?php

namespace App\Http\Controllers\Api;



use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTGuard;
use tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Wallet;
use App\Providers\helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{
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


            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);

            $userResponse = $this->getUser(param: $request->email);
            $userResponse->token = $token ;
            $userResponse->token_expires_in = auth()->factory()->getTTL() * 60;
            $userResponse->token_type = 'bearer';



            return response()->json($userResponse);


        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()],500);
        }


        return response()->json(['message' => 'success'],200);

    }


    public function login(Request $request){
        $credentials = $request->only('email', 'password');


        $validator = Validator::make($credentials,[
            'email' =>  'required|email',
            'password' => 'required|string|min:8',
        ]);


        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()],400);
        }
        try {
            $token = JWTAuth::attempt($credentials);

            if(!$token){
                return response()->json(['message' => 'Login credentials are invalid'],401);
            }

       
            $userResponse = $this->getUser(param: $request->email);
            $userResponse->token = $token ;
            $userResponse->token_expires_in = auth()->factory()->getTTL() * 60;
            $userResponse->token_type = 'bearer';



            return response()->json($userResponse);
        } catch (JWTException $th) {
            return response()->json(['message' => 'Could not create token', 'error' => $th->getMessage()], 500);
        }

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


    private function getUser($param){
    $user = User::where('id', $param)
                ->orWhere('email', $param)
                ->orWhere('username', $param)
                ->first();

    if (!$user) {
        return null;
    }

    $wallet = Wallet::where('user_id', $user->id)->first();

    $user->profile_picture = $user->profile_picture ? url('storage/' . $user->profile_picture) : "";
    $user->ktp = $user->ktp ? url('storage/' . $user->ktp) : "";
    $user->balance = $wallet ? $wallet->balance : 0;
    $user->card_number = $wallet ? $wallet->card_number : "";
    $user->pin = $wallet ? $wallet->pin : "";

    return $user;
}

}
