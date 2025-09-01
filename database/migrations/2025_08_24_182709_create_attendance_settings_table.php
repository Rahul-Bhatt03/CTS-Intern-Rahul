<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
         Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('attendance_settings')->insert([
            ['key' => 'default_work_hours', 'value' => '8', 'description' => 'Default work hours per day'],
            ['key' => 'overtime_threshold', 'value' => '8', 'description' => 'Hours after which overtime starts'],
            ['key' => 'late_grace_period', 'value' => '60', 'description' => 'Grace period for late arrival in minutes'],
            ['key' => 'allow_wfh', 'value' => 'true', 'description' => 'Allow work from home check-ins'],
            ['key' => 'location_validation', 'value' => 'false', 'description' => 'Enable GPS location validation'],
            ['key' => 'office_coordinates', 'value' => '{"lat": 0, "lng": 0, "radius": 100}', 'description' => 'Office location for GPS validation'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_settings');
    }
};
