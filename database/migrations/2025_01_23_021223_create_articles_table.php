<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->longText('title');
            $table->mediumText('author');
            $table->longText('description');
            $table->longText('url');
            $table->longText('urlToImage')->nullable();
            $table->longText('publishedAt');
            $table->longText('content')->nullable();
            $table->mediumText('source_name');
            $table->string('provider')->default('newsapi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
