<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Services;

use October\Rain\Exception\ApplicationException;
use Sells\SlFeedback\Models\Question;
use Sells\SlGeneral\Models\General;

class QuestionSubmitService
{
    /**
     * @throws ApplicationException
     */
    public function handle(array $input): Question
    {
        if(empty($input['specialist_id'])) {
            throw new ApplicationException('Не указан специалист для вопроса');
        }

        $question = new Question();
        $question->name = $input['name'];
        $question->email = $input['email'];
        $question->question = $input['question'];
        $question->specialist_id = (int)$input['specialist_id'];
        $question->answer = '';
        $question->save();
        $question->load('specialist');

        if(!(new QuestionSpecialistMailService())->send($question)) {
            throw new ApplicationException('Не удалось отправить письмо специалисту. Позвоните нам по номеру: ' . General::first()->info['phone']);
        }

        return $question;
    }
}
