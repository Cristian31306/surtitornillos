<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'phone', 'document_id'])]
class Client extends Model
{
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
