<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataPlan;
use App\Models\DataPlanHistory;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DataPlanController extends Controller
{
    public function store(Request $request){
       $validator = Validator::make($request->all(), [
        'data_plan_id' => 'required|integer',
        'phone_number' => 'required|string',
        'pin' => 'required|numeric|digits:6',
       ]);

       if($validator->fails()){
        return response()->json(['errors' => $validator->errors()], 400);
       }


       $pinChecker = pinChecker($request->pin);

       
       $dataPlan = DataPlan::find($request->data_plan_id);
       $transactionType = TransactionType::where('code', 'internet')->first();
       $paymentMethod = PaymentMethod::where('code', 'bwa')->first();
       $userWallet = Wallet::where('user_id', $request->user()->id)->first();
       
       
       if(!$dataPlan){
           return response()->json(['errors' => 'Data plan not found'], 400);
        }
        if(!$pinChecker){
            return response()->json(['errors' => 'Invalid pin'], 400);
        }

       if($userWallet->balance < $dataPlan->price){
        return response()->json(['errors' => 'Insufficient balance'], 400);
       }
       DB::beginTransaction();

       try {
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'transaction_type_id' => $transactionType->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $dataPlan->price,
                'transaction_code' => strtoupper(\Illuminate\Support\Str::random(10)),
                'description' => 'Data Plan ' . $dataPlan->name . ' ' . $dataPlan->price,
                'status' => 'success',
            ]);

            DataPlanHistory::create([
                'data_plan_id' => $dataPlan->id,
                'transaction_id' => $transactionType->id,
                'phone_number' => $request->phone_number,
            ]);

            $userWallet->decrement('balance', $dataPlan->price);
            
            DB::commit();
            return response()->json(['message' => 'Data plan purchased successfully'], 200);

       } catch (\Throwable $th) {
        //throw $th;
        DB::rollBack();
        echo($th);
        return response()->json(['errors' => 'Failed to purchase data plan'], 500);
       }
}
}
