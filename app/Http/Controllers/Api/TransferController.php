<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{
    public function store(Request $request){
        $data = $request->only(['amount', 'pin', 'send_to']);


        $validator = Validator::make($data, [
            'amount' => 'required|integer|min:10000',
            'pin' => 'required|digits:6',
            'send_to' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $sender = auth()->user();
        $receiver = User::select('users.id', 'users.username')
                    ->join('wallets', 'wallets.user_id', 'users.id')
                    ->where('users.username', $data['send_to'])
                    ->orWhere('wallets.card_number', $data['send_to'])
                    ->first();
        

        $pinChecker = pinChecker( $data['pin']);
        if (!$pinChecker){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid pin'
            ], 400);
        }

        if(!$receiver){
            return response()->json([
                'status' => 'error',
                'message' => 'Receiver not found'
            ], 400);
        }

        if($sender->id == $receiver->id){
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot transfer to yourself'
            ], 400);
        }

        $senderWallet = Wallet::where('user_id', $sender->id)->first();
        if($senderWallet->balance < $data['amount']){
            return response()->json([
                'status' => 'error',
                'message' => 'Your balance is not enough'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transfer successful'
        ], 200);
        
    }
}
