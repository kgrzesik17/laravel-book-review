<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['review', 'rating'];

    public function book() {
        // return Book parent
        return $this->belongsTo(Book::class);
    }

    protected static function booted() {
        // won't be updated on mass assignment or raw sql query or db transactions
        static::updated(fn(Review $review) => cache()->forget('book:' . $review->book_id));  // forget the cache whenever the specific review is updated
        static::deleted(fn(Review $review) => cache()->forget('book:' . $review->book_id));
        static::created(fn(Review $review) => cache()->forget('book:' . $review->book_id));
    }
}
