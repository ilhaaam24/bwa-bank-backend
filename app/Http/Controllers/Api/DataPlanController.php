<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataPlan;
use App\Models\PaymentMethod;
use App\Models\TransactionType;
use App\Models\Wallet;
use Illuminate\Http\Request;
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

       return response()->json(['message' => 'Data plan purchased successfully'], 200);
       }
}
