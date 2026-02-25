<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'ip_consignment_permit',
            'consignment_permits',
            'inspection_items',
        ];

        foreach ($tables as $tableName) {

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                if (!Schema::hasColumn($tableName, 'print_calc')) {
                    $table->integer('print_calc')->nullable()->after('updated_at');
                }

                if (!Schema::hasColumn($tableName, 'print_reason')) {
                    $table->text('print_reason')->nullable()->after('print_calc');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'ip_consignment_permit',
            'consignment_permits',
            'inspection_items',
        ];

        foreach ($tables as $tableName) {

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {

                if (Schema::hasColumn($tableName, 'print_calc')) {
                    $table->dropColumn('print_calc');
                }

                if (Schema::hasColumn($tableName, 'print_reason')) {
                    $table->dropColumn('print_reason');
                }
            });
        }
    }
};
