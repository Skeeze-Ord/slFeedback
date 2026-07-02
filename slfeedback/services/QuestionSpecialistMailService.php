<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Services;

use Backend;
use October\Rain\Support\Facades\Mail;
use Sells\SlFeedback\Models\Question;
use Throwable;

class QuestionSpecialistMailService
{
    private const MAIL_TEMPLATE = 'sells.slfeedback::mail.question_for_specialist';

    public function send(Question $question): bool
    {
        $specialist = $question->specialist;
        $email = trim((string)$specialist?->email);

        if($email === '') {
            return false;
        }

        try {
            $mailSubject = 'Новый вопрос с сайта';

            Mail::send(
                self::MAIL_TEMPLATE,
                [
                    'mailSubject' => $mailSubject,
                    'question' => $question,
                    'specialist' => $specialist,
                    'answerUrl' => $this->getAnswerUrl($question),
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

        return true;
    }

    private function getAnswerUrl(Question $question): string
    {
        return Backend::url('sells/slfeedback/questions/answer/' . $question->id . '/' . $question->answer_token);
    }
}
