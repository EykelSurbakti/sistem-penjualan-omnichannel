<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'sales_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_count')->default(0)->after('soldout_strategy')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'sales_count')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('sales_count');
            });
        }
    }
};
