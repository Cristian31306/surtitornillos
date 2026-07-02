<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['client_id', 'seller_id', 'invoice_number', 'issue_date', 'total_amount', 'discount', 'status', 'observation'])]
class Invoice extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function adjustments()
    {
        return $this->hasMany(Adjustment::class);
    }
}
