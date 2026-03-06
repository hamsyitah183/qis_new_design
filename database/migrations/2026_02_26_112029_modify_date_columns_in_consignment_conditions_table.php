<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['consignment_conditions', 'ip_condition'];

        foreach ($tables as $tableName) {

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                if (Schema::hasColumn($tableName, 'date_limit')) {
                    $table->dropColumn('date_limit');
                }

                if (!Schema::hasColumn($tableName, 'start_date')) {
                    $table->date('start_date')->nullable()->after('quantity_limit');
                }

                if (!Schema::hasColumn($tableName, 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }

                if (!Schema::hasColumn($tableName, 'measurement_unit')) {
                    $table->string('measurement_unit')->nullable()->after('end_date');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['consignment_conditions', 'ip_condition'];

        foreach ($tables as $tableName) {

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                if (Schema::hasColumn($tableName, 'start_date')) {
                    $table->dropColumn('start_date');
                }

                if (Schema::hasColumn($tableName, 'end_date')) {
                    $table->dropColumn('end_date');
                }

                if (Schema::hasColumn($tableName, 'measurement_unit')) {
                    $table->dropColumn('measurement_unit');
                }

                if (!Schema::hasColumn($tableName, 'date_limit')) {
                    $table->date('date_limit')->nullable()->after('quantity_limit');
                }
            });
        }
    }
};
