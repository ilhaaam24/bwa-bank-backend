<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth as FacadesAuth;
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


            $params = $this->buildMidtransParams([
                'transaction_code' => $transaction->transaction_code,
                'amount' => $transaction->amount,
                'payment_method' => $paymentMethod->code,
            ]);

            $midtrans = $this->callMidtrans($params);

            $transaction->save();
            DB::commit();

            return response()->json([
                'data' => $midtrans,
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollBack();
            return response()->json(['errors' => $th->getMessage()], 500);
        }
    }

    private function callMidtrans(array $params){
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction =(bool) env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = (bool) env('MIDTRANS_IS_SANITIZED');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_IS_3DS');
        
        $createTransaction = \Midtrans\Snap::createTransaction($params);

        return[
            'redirect_url' => $createTransaction->redirect_url,
            'token' => $createTransaction->token,
        ];
    }

    private function buildMidtransParams(array $params){
        $transactionDetails = [
            'order_id' => $params['transaction_code'],
            'gross_amount' => $params['amount'],
        ];


        $user = FacadesAuth::user();
        $splitName = $this->splitName($user->name);
        $customerDetails = [
            'first_name' => $splitName['first_name'],
            'last_name' => $splitName['last_name'],
            'email' => $user->email,
        ];

        $enablePayment = [
           $params['payment_method']
        ];

        return[
            'transaction_details' => $transactionDetails,   
            'customer_details' => $customerDetails,
            'enabled_payments' => $enablePayment,
        ];
    }

    private function splitName(string $name){
        $name = explode(' ', $name);
        $lastName = count($name) > 1 ? $name[count($name) - 1] : '';
        $firstName = implode('', $name);

        return[
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }
}
