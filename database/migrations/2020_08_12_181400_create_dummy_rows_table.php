<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDummyRowsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('dummy_rows', static function (Blueprint $table) {
            // IDs and timestamps
            $table->id();
            $table->timestamps();

            // Field being filtered
            $table->text('test')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dummy_rows');
    }
}
