<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

#[Fillable(['name', 'phone', 'document_id'])]
class Client extends Model
{
    /**
     * Mutador para guardar el nombre siempre en mayúscula.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Str::upper($value) : $value,
        );
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
