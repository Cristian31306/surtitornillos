<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['invoice_id', 'amount', 'payment_date', 'payment_method', 'observation'])]
class Payment extends Model
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
