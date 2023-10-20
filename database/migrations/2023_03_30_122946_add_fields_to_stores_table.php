<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('is_tax_prepaid')->nullable();
            $table->boolean('use_client_sku')->nullable();
            $table->boolean('submit_with_tax')->nullable();
            $table->boolean('submit_all_items')->nullable();
            $table->string('channel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('is_tax_prepaid');
            $table->dropColumn('use_client_sku');
            $table->dropColumn('submit_with_tax');
            $table->dropColumn('submit_all_items');
            $table->dropColumn('channel');
        });
    }
}
