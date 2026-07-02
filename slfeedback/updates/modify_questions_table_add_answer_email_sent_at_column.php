<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sells_slfeedback_questions', function(Blueprint $table) {
            $table->timestamp('answer_email_sent_at')->nullable()->after('answered_at');
            $table->string('answer_email_hash', 64)->nullable()->after('answer_email_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('sells_slfeedback_questions', function(Blueprint $table) {
            $table->dropColumn(['answer_email_sent_at', 'answer_email_hash']);
        });
    }
};
