<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function book() {
        // return Book parent
        return $this->belongsTo(Book::class);
    }
}
