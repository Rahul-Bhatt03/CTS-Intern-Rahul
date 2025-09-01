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
        Schema::table('attendances', function (Blueprint $table) {
            // Work type and location
            $table->enum('work_type', ['office', 'wfh', 'hybrid'])->default('office');
            $table->json('location_data')->nullable(); // GPS coordinates, IP address
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            
            // Overtime tracking
            $table->decimal('regular_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->time('expected_check_in')->nullable();
            $table->time('expected_check_out')->nullable();
            
            // Break tracking
            $table->json('breaks')->nullable(); // Store break times as JSON
            $table->decimal('break_hours', 5, 2)->default(0);
            $table->decimal('productive_hours', 5, 2)->default(0);
            
            // Additional fields
            $table->string('ip_address')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'work_type', 'location_data', 'is_late', 'late_minutes',
                'regular_hours', 'overtime_hours', 'expected_check_in', 'expected_check_out',
                'breaks', 'break_hours', 'productive_hours',
                'ip_address', 'admin_notes', 'submitted_at', 'approved_at', 'approved_by'
            ]);
        });
    }
};
