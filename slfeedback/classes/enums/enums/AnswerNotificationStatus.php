<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Classes\Enums\enums;

enum AnswerNotificationStatus: string
{
    case NOT_NEEDED = 'not_needed';
    case TIMEOUT = 'timeout';
    case FAILED = 'failed';
    case SENT = 'sent';
}
