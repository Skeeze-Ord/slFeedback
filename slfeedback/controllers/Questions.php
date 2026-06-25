<?php namespace Sells\SlFeedback\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use Backend;
use BackendMenu;
use Flash;
use October\Rain\Exception\ApplicationException;
use Sells\SlFeedback\Classes\Enums\enums\QuestionStatuses;
use Sells\SlFeedback\Models\Question;
use Sells\SlFeedback\Models\Settings;

class Questions extends Controller
{
    public $implement = [
        FormController::class,
        ListController::class,
    ];

    public string $formConfig = 'config_form.yaml';
    public string $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['sells.slfeedback.questions'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Sells.SlFeedback', 'slfeedback', 'questions');
    }

    public function beforeDisplay(): void
    {
        $this->setVars();
    }

    public function listExtendQuery($query): void
    {
        $query->with('specialist.category');
    }

    private function setVars(): void
    {
        $this->vars['questionsCount'] = $this->getQuestionsCount();
        $this->vars['answeredCount'] = $this->getAnsweredQuestionsCount();
        $this->vars['unansweredCount'] = $this->getUnansweredQuestionsCount();
        $this->vars['disclaimer'] = Settings::get('disclaimer', '');
    }

    public function onSaveDisclaimer(): void
    {
        Settings::set('disclaimer', trim((string) post('disclaimer', '')));

        Flash::success('Дисклеймер сохранён');
    }

    public function answer(?string $questionId = null, ?string $token = null): void
    {
        $question = $this->findQuestionByToken($questionId, $token);

        $this->asExtension('FormController')->initForm($question, 'update');

        $this->pageTitle = 'Ответ на вопрос';
        $this->vars['hideMainMenu'] = true;
        $this->vars['question'] = $question;
        $this->vars['answerUrl'] = $this->makeAnswerUrl($question);
    }

    /**
     * @throws ApplicationException
     */
    public function onSaveAnswer(): void
    {
        $question = $this->findQuestionByToken(
            (string) post('question_id', ''),
            (string) post('answer_token', '')
        );

        $questionData = (array) post('Question', []);
        $answer = trim((string) post('answer', $questionData['answer'] ?? ''));

        if ($answer === '') {
            throw new ApplicationException('Введите ответ');
        }

        $question->answer = $answer;
        $question->save();

        Flash::success('Ответ сохранён');
    }

    /**
     * @throws ApplicationException
     */
    private function findQuestionByToken(?string $questionId, ?string $token): Question
    {
        if (!$questionId || !$token) {
            throw new ApplicationException('Ссылка для ответа некорректна');
        }

        $question = Question::query()
            ->with('specialist')
            ->where('id', (int) $questionId)
            ->where('answer_token', $token)
            ->first();

        if (!$question) {
            throw new ApplicationException('Вопрос не найден или ссылка устарела');
        }

        return $question;
    }

    private function makeAnswerUrl(Question $question): string
    {
        return Backend::url('sells/slfeedback/questions/answer/' . $question->id . '/' . $question->answer_token);
    }

    private function getQuestionsCount(): int
    {
        return Question::count();
    }

    private function getAnsweredQuestionsCount(): int
    {
        return Question::where('status', QuestionStatuses::ANSWERED)->count();
    }

    private function getUnansweredQuestionsCount(): int
    {
        return Question::where('status', QuestionStatuses::NEW)->count();
    }
}
