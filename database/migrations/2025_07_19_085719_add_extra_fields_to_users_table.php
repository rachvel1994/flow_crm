<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('email');
            $table->date('birthdate')->nullable()->after('surname');
            $table->string('country')->nullable()->after('birthdate');
            $table->jsonb('image')->nullable()->after('country');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'surname',
                'birthdate',
                'country',
                'image',
            ]);
        });
    }
};
