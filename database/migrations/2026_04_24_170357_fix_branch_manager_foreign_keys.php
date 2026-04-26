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
        Schema::table('branches', function (Blueprint $table) {
            // Drop old foreign keys if they exist
            try {
                $table->dropForeign('branches_manager_id_foreign');
            } catch (\Exception $e) {}
            try {
                $table->dropForeign('branches_deputy_id_foreign');
            } catch (\Exception $e) {}

            // Add new foreign keys pointing to USERS
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deputy_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['deputy_id']);
        });
    }
};
