<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function update(Request $request){
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');

        $notif = new \Midtrans\Notification();
        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;


        DB::beginTransaction();
        try {

            $status = null;



            if ($transaction == 'capture'){
                if ($fraud == 'accept'){
                        // TODO set transaction status on your database to 'success'
                        // and response with 200 OK
                        $status = 'success';
                    }
                } else if ($transaction == 'settlement'){
                    // TODO set transaction status on your database to 'success'
                    // and response with 200 OK
                    $status = 'success';
            } else if ($transaction == 'cancel' ||
            $transaction == 'deny' ||
            $transaction == 'expire'){
            // TODO set transaction status on your database to 'failure'
            // and response with 200 OK
            $status = 'failed';
            } else if ($transaction == 'pending'){
            // TODO set transaction status on your database to 'pending' / waiting payment
            // and response with 200 OK
            $status = 'pending';
            }

            $transaction = Transaction::where('order_id', $order_id)->first();

            if($transaction->status != 'success'){
                $transactionAmount = $transaction->amount;
                $userId = $transaction->user_id;

                $transaction->update([
                    'status' => $status,
                ]);

               if($status == 'success'){
                Wallet::where('user_id', $userId)->increment('balance', $transactionAmount);
               }
            }

            DB::commit();
            return response()->json();



        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['errors' => $th->getMessage()], 500);
        }
    }   
        
}
