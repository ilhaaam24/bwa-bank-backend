<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    //
    public function index(){
        $banks = PaymentMethod::where('status', 'active')
                    ->where('code' ,'!=', 'bwa')->get()
                    ->map(function($item){
                        $item->thumbnail = $item->thumbnail ? url($item->thumbnail) : '';
                        return $item;
                    });

        if($banks->isEmpty()){
            return response()->json([
                'message' => 'No data found',
            ], 404);
        }

        return response()->json([
            'data' => $banks,
        ], 200);
    }
}
