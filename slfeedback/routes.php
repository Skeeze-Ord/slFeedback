<?php

declare(strict_types=1);

namespace Sells\SlFeedback;

use Illuminate\Support\Facades\Route;
use Sells\SlFeedback\Http\Controllers\QuestionsController;

Route::group([
    'prefix' => 'api/v1/slfeedback',
], function(): void {
    Route::post('questions', [QuestionsController::class, 'store']);
});
