<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'playlist_id')) {
                $table->dropColumn('playlist_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('playlist_id');
            // أضف العلاقات أو القيود إذا كانت موجودة مسبقًا
            // $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('cascade');
        });
    }
};
