<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Services;

use October\Rain\Exception\ApplicationException;
use Sells\SlFeedback\Classes\Enums\enums\AnswerNotificationStatus;
use Sells\SlFeedback\Models\Question;

class QuestionAnswerService
{
    private QuestionAuthorAnswerMailService $mailService;

    public function __construct(?QuestionAuthorAnswerMailService $mailService = null)
    {
        $this->mailService = $mailService ?? new QuestionAuthorAnswerMailService();
    }

    /**
     * @throws ApplicationException
     */
    public function saveAnswer(Question $question, string $answer): AnswerNotificationStatus
    {
        $answer = trim($answer);

        if ($answer === '') {
            throw new ApplicationException('Введите ответ');
        }

        $question->answer = $answer;
        $question->save();

        return $this->sendNotificationToAuthor($question);
    }

    public function sendNotificationToAuthor(Question $question): AnswerNotificationStatus
    {
        $question->load('specialist.category');

        if (!$this->mailService->needsSending($question)) {
            return AnswerNotificationStatus::NOT_NEEDED;
        }

        if (!$this->mailService->canSend($question)) {
            return AnswerNotificationStatus::TIMEOUT;
        }

        if (!$this->mailService->send($question)) {
            return AnswerNotificationStatus::FAILED;
        }

        return AnswerNotificationStatus::SENT;
    }

    public function getResendTimeoutMinutes(): int
    {
        return $this->mailService->getResendTimeoutMinutes();
    }
}
