<?php

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;

function getUser($param){
    $user = User::where('id', $param)
                ->orWhere('email', $param)
                ->orWhere('username', $param)
                ->first();

    if (!$user) return null;

    $wallet = Wallet::where('user_id', $user->id)->first();

    $user->profile_picture = $user->profile_picture ? url('storage/'.$user->profile_picture) : "";
    $user->ktp = $user->ktp ? url('storage/'.$user->ktp) : "";
    $user->balance = $wallet?->balance;
    $user->card_number = $wallet?->card_number;
    $user->pin = $wallet?->pin;

    return $user;
}


function pinChecker($pin){
  $userId = Auth::user()->id;
  $wallet = Wallet::where('user_id', $userId)->first();

  if($wallet->pin == $pin){
    return true;
  }
  return false;
}

function upload64Image($image64){
        $decoder = new Base64ImageDecoder($image64, $allowedFormats = ['jpeg', 'png', 'gif', 'jpg']);
        $deocedContent = $decoder->getDecodedContent();
        $format = $decoder->getFormat();
        $image = Str::random(10).'.'.$format;

        Storage::disk('public')->put($image, $deocedContent);

        return $image;
    }
