<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
              $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->dateTime('check_in');
    $table->dateTime('check_out')->nullable();
    $table->decimal('total_hours', 8, 2)->nullable(); // 8 digits total, 2 decimal places
    $table->string('status', 20); // present/absent/late/half-day
    $table->text('notes')->nullable();
    $table->timestamps();
    
    // Composite unique constraint
    $table->unique(['user_id', 'date']);
    
    // Indexes
    $table->index('date');
    $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
