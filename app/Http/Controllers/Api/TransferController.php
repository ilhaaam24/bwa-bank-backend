<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\TransferHistory;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


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

        DB::beginTransaction();

        try{

            $trasactionType = TransactionType::whereIn('code', ['receive', 'transfer'])->orderBy('code', 'asc')->get();


            $receiveTransactionType = $trasactionType->first();
            $transferTransactionType = $trasactionType->last();

            $transactionCode = strtoupper(Str::random(10));
            $paymentMethod = PaymentMethod::where('code', 'bwa')->first();


            // transaction for sender
            $transferTransaction = Transaction::create([
                'user_id' => $sender->id,
                'transaction_type_id' => $transferTransactionType->id,
                'description' => 'Transfer  to ' . $receiver->username,
                'amount' => $data['amount'],
                'transaction_code' => $transactionCode,
                'status' => 'success',
                'payment_method_id' => $paymentMethod->id,
            ]);

            // update balance sender
            $senderWallet->decrement('balance', $data['amount']);

            // create transaction for receiver
            $transferTransaction = Transaction::create([
                'user_id' => $sender->id,
                'transaction_type_id' => $receiveTransactionType->id,
                'description' => 'Receive from ' . $sender->username,
                'amount' => $data['amount'],
                'transaction_code' => $transactionCode,
                'status' => 'success',
                'payment_method_id' => $paymentMethod->id,
            ]);
            
            Wallet::where('user_id', $receiver->id)->increment('balance', $data['amount']);

            TransferHistory::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'transaction_code' => $transactionCode,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer success'
            ], 200);


            

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Transfer failed'
            ], 400);
        }
    }
}
