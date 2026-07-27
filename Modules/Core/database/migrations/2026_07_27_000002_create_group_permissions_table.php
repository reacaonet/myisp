<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('user_groups')->cascadeOnDelete();
            $table->string('permission_key');
            $table->boolean('granted')->default(true);
            $table->timestamps();

            $table->unique(['group_id', 'permission_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_permissions');
    }
};
