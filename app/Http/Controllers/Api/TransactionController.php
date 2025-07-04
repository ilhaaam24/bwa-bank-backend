<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //

    public function index(Request $request){

        $limit = $request->query('limit') ? $request->query('limit') : 10;

        $user = auth()->user();


        $relations = [
            'paymentMethod:id,name,code,thumbnail',
            'transactionType:id,name,code,thumbnail',
        ];

        $transactions = Transaction::with($relations)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $transactions->getCollection()->transform(function($item){
            $item->paymentMethod->thumbnail = $item->paymentMethod->thumbnail ? url($item->paymentMethod->thumbnail) : '';
            $item->transactionType->thumbnail = $item->transactionType->thumbnail ? url($item->transactionType->thumbnail) : '';
            return $item;
        });


        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ], 200);
        
    } 
}
