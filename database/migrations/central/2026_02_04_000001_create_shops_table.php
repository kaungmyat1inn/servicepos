<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('api_key', 64)->unique();
            $table->string('db_host');
            $table->unsignedInteger('db_port')->default(3306);
            $table->string('db_name');
            $table->string('db_username');
            $table->string('db_password');
            $table->string('db_timezone')->default('UTC');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('shops');
    }
};
