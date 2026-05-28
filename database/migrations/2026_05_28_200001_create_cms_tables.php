<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type')->default('article'); // article, gallery, announcement, achievement
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cms_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cms_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('cms_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('status')->default('draft'); // draft, review, published, archived
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        Schema::create('cms_post_tags', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('cms_posts')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('cms_tags')->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });

        Schema::create('cms_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('cms_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('erp_accounts')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cms_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('cms_galleries')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_gallery_items');
        Schema::dropIfExists('cms_galleries');
        Schema::dropIfExists('cms_post_tags');
        Schema::dropIfExists('cms_posts');
        Schema::dropIfExists('cms_tags');
        Schema::dropIfExists('cms_categories');
    }
};
