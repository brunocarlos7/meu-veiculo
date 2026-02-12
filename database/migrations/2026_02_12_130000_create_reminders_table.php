<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // oil_change, ipva, insurance, licensing, tire_change, revision
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->integer('due_odometer')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index('due_date');
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
