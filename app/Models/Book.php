<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;  // alias is needed as names are the same

class Book extends Model
{
    use HasFactory;

    public function reviews() {
        // return reviews children
        return $this->hasMany(Review::class);
    }

    // query scopes need to start with query
    public function scopeTitle(Builder $query, string $title): Builder {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    // public function scopePopular(Builder $query): Builder
    // {
    //     return $query->withCount('reviews')
    //         ->orderBy('reviews_count', 'desc');
    // }

    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ]);
    }

    public function scopeWithAvgRating(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvg([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ], 'rating');
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder  // union type - can be both Builder or QueryBui
    {
        return $query->withReviewsCount()
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder|QueryBuilder {
        return $query->withAvgRating()
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder {
        return $query->having('reviews_count', '>=', $minReviews);  // having keyword because of agregate functions
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null) {
        if ($from && !$to) {
                $query->where('created_at', '>=', $from);
            } elseif (!$from && $to) {
                $query->where('created_at', '<=', $to);
            } elseif ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            }
    }

    public function scopePopularLastMonth(Builder $query): Builder|QueryBuilder {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder|QueryBuilder {
        return $query->popular(now()->subMonth(6), now())
            ->highestRated(now()->subMonth(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder|QueryBuilder {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query): Builder|QueryBuilder {
        return $query->highestRated(now()->subMonth(6), now())
            ->popular(now()->subMonth(6), now())
            ->minReviews(5);
    }

     protected static function booted() {
        // won't be updated on mass assignment or raw sql query or db transactions
        static::updated(fn(Book $book) => cache()->forget('book:' . $book->id));  // forget the cache whenever the specific review is updated
        static::deleted(fn(Book $book) => cache()->forget('book:' . $book->id));
    }
}
