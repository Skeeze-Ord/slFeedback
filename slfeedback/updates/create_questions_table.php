<?php namespace Sells\SlFeedback\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sells_slfeedback_questions', function(Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('is_active')->default(true);

            $table->string('name');
            $table->string('email');

            $table->text('question');
            $table->text('answer')->nullable();

            $table->string('status')->default('new');

            $table->timestamp('answered_at')->nullable();

            $table->unsignedBigInteger('specialist_id')->nullable();
            $table->foreign('specialist_id')
                ->references('id')
                ->on('sells_mdcatalog_specialists')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sells_slfeedback_questions');
    }
};
