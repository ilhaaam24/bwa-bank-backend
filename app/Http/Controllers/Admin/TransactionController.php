<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(){
        $relations = [
            'user:id,name',
            'transactionType:id,code,action',
            'paymentMethod:id,name,code,thumbnail'
        ];


        $transactions = Transaction::with($relations)->orderBy('created_at', 'desc')->get();

        return view('transaction', ['transactions' => $transactions]);
    }
}
