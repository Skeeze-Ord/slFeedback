<?php namespace Sells\SlFeedback\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sells_slfeedback_questions', function(Blueprint $table) {
            $table->string('answer_token')->nullable()->unique()->after('specialist_id');
        });
    }

    public function down(): void
    {
        Schema::table('sells_slfeedback_questions', function(Blueprint $table) {
            $table->dropUnique(['answer_token']);
            $table->dropColumn('answer_token');
        });
    }
};
