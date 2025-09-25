<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('locale', 5);
            $table->text('content');
            $table->string('context')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->unique(['key', 'locale', 'context']);
            $table->index('locale');
            $table->index('context');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
