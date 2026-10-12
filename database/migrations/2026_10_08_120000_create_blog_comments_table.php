<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_id')->index();   // the blog's frontends row id
            $table->string('name', 100);
            $table->string('email', 191);                     // never shown publicly
            $table->string('website', 255)->nullable();
            $table->text('comment');
            $table->tinyInteger('status')->default(1);        // 1 shown, 0 held (link spam)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};
