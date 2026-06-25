<?php namespace Sells\SlFeedback\Classes\Enums\enums;

enum QuestionStatuses: string
{
    case NEW = 'new';
    case ANSWERED = 'answered';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Новый',
            self::ANSWERED => 'Отвечен',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => '#F44336',
            self::ANSWERED => '#4CAF50',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label()
            ])
            ->toArray();
    }

    public static function optionsWithColor(): array
    {
        return collect(self::cases())
            ->mapWithKeys(function(self $case) {
                return [$case->value => [$case->label(), $case->color()]];
            })
            ->toArray();
    }
}
