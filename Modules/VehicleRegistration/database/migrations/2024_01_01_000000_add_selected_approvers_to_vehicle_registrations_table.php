<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_registrations', 'selected_approvers')) {
                $table->json('selected_approvers')->nullable()->after('director_approved_at');
            }
        });
    }

    public function down()
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_registrations', 'selected_approvers')) {
                $table->dropColumn('selected_approvers');
            }
        });
    }
};

