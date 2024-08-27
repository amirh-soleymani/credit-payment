<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditRequest extends Model
{
    use HasFactory;

    CONST PENDING = 'pending';
    CONST ACCEPT = 'accept';
    CONST DENY = 'deny';

    protected $table = 'credit_request';

    protected $fillable = [
        'seller_id',
        'client_id',
        'status'
    ];

    public function client(){
        return $this->belongsTo(User::class,'client_id', 'id');
    }

    public function seller(){
        return $this->belongsTo(User::class,'seller_id', 'id');
    }
}
