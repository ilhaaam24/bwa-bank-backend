<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TopUpController extends Controller
{
    public function store(Request $request){
        $data = $request->only('amount', 'pin', 'payment_method_code');

        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:10000',
            'pin' => 'required|numeric|digits:6',
            'payment_method_code' => 'required|in:bni_va,bri_va,bca_va',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $pinChecker = pinChecker($data['pin']);

        if(!$pinChecker){
            return response()->json(['errors' => 'Invalid pin'], 400);
        }


        $transactionType = TransactionType::where('code', 'top_up')->first();
        $paymentMethod = PaymentMethod::where('code', $data['payment_method_code'])->first();



        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'transaction_type_id' => $transactionType->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $data['amount'],
                'transaction_code' =>strtoupper( Str::random(10)),
                'status' => 'pending',
                'description' => 'Top up via ' . $paymentMethod->name,

            ]);

            $transaction->save();

            DB::commit();
            return response()->json(['message' => 'Top up successful'], 200);
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollBack();
            return response()->json(['errors' => $th->getMessage()], 500);
        }

        
      
    }
}
