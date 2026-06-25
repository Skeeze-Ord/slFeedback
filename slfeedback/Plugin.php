<?php namespace Sells\SlFeedback;

use Backend;
use Sells\SlFeedback\Components\SlFeedbackFeedback;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name' => 'SlFeedback',
            'description' => 'No description provided yet...',
            'author' => 'Sells',
            'icon' => 'icon-leaf'
        ];
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function registerComponents(): array
    {
        return [
            SlFeedbackFeedback::class => 'SlFeedbackFeedback',
        ];
    }

    public function registerMailTemplates(): array
    {
        return [
            'sells.slfeedback::mail.question_for_specialist' => 'sells.slfeedback::mail.question_for_specialist',
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'sells.slfeedback.questions' => [
                'tab' => 'Обратная связь',
                'label' => 'Управление вопросами'
            ],
        ];
    }

    public function registerNavigation(): array
    {
        return [
            'slfeedback' => [
                'label' => 'Обратная связь',
                'url' => Backend::url('sells/slfeedback/questions'),
                'icon' => 'icon-question',
                'permissions' => ['sells.slfeedback.*'],
                'order' => 600,
            ],
        ];
    }
}
