<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'amount',
        'status',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
