<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('stored_filename')->nullable()->after('filename')
                ->comment('The actual filename stored on disk (may be different from original filename)');
            $table->string('mime_type')->nullable()->after('stored_filename')
                ->comment('MIME type of the uploaded file');
            $table->string('path')->nullable()->after('mime_type')
                ->comment('Storage path relative to storage disk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn(['stored_filename', 'mime_type', 'path']);
        });
    }
};
