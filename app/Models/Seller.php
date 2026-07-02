<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = ['name', 'document_id', 'phone', 'status'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
