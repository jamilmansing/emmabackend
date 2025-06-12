<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    public function getFamilyQrAttribute($value)
    {
        if ($this->family) {
            return env('APP_URL') . "/api/qr/{$this->family->id}";
        }
        return null;
    }

}
