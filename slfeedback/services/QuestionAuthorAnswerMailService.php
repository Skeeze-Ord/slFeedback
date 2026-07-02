<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Services;

use Carbon\Carbon;
use DB;
use October\Rain\Support\Facades\Mail;
use Sells\SlFeedback\Models\Question;
use Throwable;

class QuestionAuthorAnswerMailService
{
    private const MAIL_TEMPLATE = 'sells.slfeedback::mail.answer_for_author';
    private const RESEND_TIMEOUT_MINUTES = 1;

    public function canSend(Question $question): bool
    {
        if(!$this->needsSending($question)) {
            return false;
        }

        if(!$question->answer_email_sent_at) {
            return true;
        }

        return Carbon::parse($question->answer_email_sent_at)
            ->addMinutes(self::RESEND_TIMEOUT_MINUTES)
            ->lte(Carbon::now());
    }

    public function needsSending(Question $question): bool
    {
        if(trim((string)$question->email) === '' || trim((string)$question->answer) === '') {
            return false;
        }

        return $this->makeAnswerHash($question) !== (string)$question->answer_email_hash;
    }

    public function getResendTimeoutMinutes(): int
    {
        return self::RESEND_TIMEOUT_MINUTES;
    }

    public function send(Question $question): bool
    {
        if(!$this->canSend($question)) {
            return false;
        }

        $email = trim((string)$question->email);
        $mailSubject = 'Ответ на ваш вопрос';
        $question->load('specialist.category');

        try {
            Mail::send(
                self::MAIL_TEMPLATE,
                [
                    'mailSubject' => $mailSubject,
                    'question' => $question,
                    'specialist' => $question->specialist,
                    'feedbackUrl' => $this->getFeedbackUrl($question),
                ],
                function($message) use ($mailSubject, $email) {
                    $message->to($email);
                    $message->subject($mailSubject);
                }
            );
        } catch(Throwable $throwable) {
            trace_log($throwable->getMessage());

            return false;
        }

        $this->markSent($question);

        return true;
    }

    private function markSent(Question $question): void
    {
        $sentAt = Carbon::now();

        DB::table($question->getTable())
            ->where('id', $question->id)
            ->update([
                'answer_email_sent_at' => $sentAt,
                'answer_email_hash' => $this->makeAnswerHash($question),
            ]);

        $question->answer_email_sent_at = $sentAt;
        $question->answer_email_hash = $this->makeAnswerHash($question);
    }

    private function makeAnswerHash(Question $question): string
    {
        return hash('sha256', trim((string)$question->answer));
    }

    private function getFeedbackUrl(Question $question): ?string
    {
        $slug = $question->specialist?->category?->slug;

        if(!$slug) {
            return null;
        }

        return url('/feedback/' . $slug);
    }
}
