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
        Schema::create('blog_post_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_post_id');
            $table->unsignedBigInteger('blog_tag_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->onDelete('cascade');
            $table->foreign('blog_tag_id')->references('id')->on('blog_tags')->onDelete('cascade');

            // Unique constraint to prevent duplicate relationships
            $table->unique(['blog_post_id', 'blog_tag_id']);

            // Indexes for performance
            $table->index(['blog_post_id']);
            $table->index(['blog_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_tags');
    }
};
