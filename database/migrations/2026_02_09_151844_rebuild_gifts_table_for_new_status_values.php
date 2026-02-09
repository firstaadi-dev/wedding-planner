<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RebuildGiftsTableForNewStatusValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gifts_v2', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('paid_amount', 15, 2)->nullable();
            $table->string('link')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('status')->default('not_started');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement("
            INSERT INTO gifts_v2 (id, name, brand, price, paid_amount, link, budget, status, notes, created_at, updated_at)
            SELECT
                id,
                name,
                brand,
                COALESCE(price, budget, 0),
                COALESCE(paid_amount, 0),
                link,
                budget,
                CASE
                    WHEN status = 'pending' THEN 'not_started'
                    WHEN status = 'ordered' THEN 'on_delivery'
                    WHEN status = 'arrived' THEN 'complete'
                    ELSE status
                END,
                notes,
                created_at,
                updated_at
            FROM gifts
        ");

        Schema::drop('gifts');
        Schema::rename('gifts_v2', 'gifts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('gifts_legacy', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('link')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->enum('status', ['pending', 'ordered', 'arrived'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement("
            INSERT INTO gifts_legacy (id, name, link, budget, status, notes, created_at, updated_at)
            SELECT
                id,
                name,
                link,
                COALESCE(price, budget, 0),
                CASE
                    WHEN status = 'not_started' THEN 'pending'
                    WHEN status = 'on_delivery' THEN 'ordered'
                    WHEN status = 'complete' THEN 'arrived'
                    ELSE 'pending'
                END,
                notes,
                created_at,
                updated_at
            FROM gifts
        ");

        Schema::drop('gifts');
        Schema::rename('gifts_legacy', 'gifts');
    }
}
