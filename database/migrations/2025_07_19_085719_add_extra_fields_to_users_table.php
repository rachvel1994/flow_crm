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
            $table->string('mobile')->nullable()->after('email');
            $table->string('address', 800)->nullable()->after('mobile');
            $table->string('created_by')->nullable()->after('address');
            $table->string('surname')->nullable()->after('created_by');
            $table->date('birthdate')->nullable()->after('surname');
            $table->string('language')->nullable()->after('birthdate');
            $table->jsonb('image')->nullable()->after('language');
            $table->foreignId('type_id')->nullable()->after('image');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mobile',
                'address',
                'created_by',
                'surname',
                'birthdate',
                'language',
                'image',
                'type_id',
            ]);
        });
    }
};
