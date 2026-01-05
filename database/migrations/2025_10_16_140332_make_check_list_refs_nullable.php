<?php

// database/migrations/2025_10_16_130000_make_check_list_refs_nullable.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('check_lists', function (Blueprint $table) {
            // allow either invoice OR bill flows
            $table->unsignedBigInteger('invoice_id')->nullable()->change();
            $table->unsignedBigInteger('invoice_payment_id')->nullable()->change();
            $table->unsignedBigInteger('bill_id')->nullable()->change();
            $table->unsignedBigInteger('bill_payment_id')->nullable()->change();

            // keep status sane (varchar is fine). If you used ENUM earlier, ensure it includes both values you write.
            // $table->string('status', 191)->default('Pending')->change();
        });
    }

    public function down()
    {
        // no-op (or restore old nullability if you really need)
    }
};

