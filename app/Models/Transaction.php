<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //

    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'transaction_type_id',
        'payment_method_id',
        'product_id',
        'amount',
        'description',
        'status',
        'transaction_code',
    ];

    public function transactionType(){
        return $this->belongsTo(TransactionType::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
}
