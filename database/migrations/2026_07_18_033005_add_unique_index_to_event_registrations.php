<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $seen = [];

        foreach (DB::table('event_registrations')->orderBy('id')->get(['id', 'event_id', 'email']) as $row) {
            $key = $row->event_id.'|'.mb_strtolower($row->email);

            if (isset($seen[$key])) {
                DB::table('event_registrations')->where('id', $row->id)->delete();
            } else {
                $seen[$key] = true;
            }
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->unique(['event_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'email']);
        });
    }
};
