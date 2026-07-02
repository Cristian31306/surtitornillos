<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['invoice_id', 'type', 'amount', 'observation'])]
class Adjustment extends Model
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
