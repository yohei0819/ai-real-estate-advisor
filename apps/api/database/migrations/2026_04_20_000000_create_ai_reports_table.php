<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('model');
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('response_tokens')->default(0);
            $table->string('status')->default('success');
            $table->json('report_payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reports');
    }
};
