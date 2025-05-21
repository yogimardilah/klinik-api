<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPasiensTable extends Migration
{
    public function up()
    {
        Schema::table('pasiens', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('nama');
            $table->string('no_hp')->nullable()->after('alamat');
            $table->date('tanggal_lahir')->nullable()->after('no_hp');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('tanggal_lahir');
        });
    }

    public function down()
    {
        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropColumn(['nik', 'no_hp', 'tanggal_lahir', 'jenis_kelamin']);
        });
    }
}
