<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public function reviews() {
        // return reviews children
        return $this->hasMany(Review::class);
    }
}
