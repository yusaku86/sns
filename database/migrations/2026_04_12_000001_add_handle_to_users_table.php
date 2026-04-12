<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('handle')->nullable()->unique()->after('name');
        });

        // 既存ユーザーのハンドルをメールアドレスから自動生成
        DB::table('users')->get()->each(function ($user) {
            $base = preg_replace('/[^a-z0-9_]/', '', strtolower(explode('@', $user->email)[0])) ?: 'user';
            $handle = $base.'_'.substr(str_replace('-', '', $user->id), 0, 6);
            DB::table('users')->where('id', $user->id)->update(['handle' => $handle]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('handle')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('handle');
        });
    }
};
