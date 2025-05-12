<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('book_id');  // unsignedBigInteger because id() is of that type
            $table->text('review');
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            // $table->foreign('book_id')->references('id')->on('books')  // specify what column it is referencing
            //     ->onDelete('cascade');  // if a book is removed, all its reviews should be removed as well

            $table->foreignId('book_id')->constrained()->cascadeOnDelete();  // shorter syntax
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
