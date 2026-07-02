<?php namespace Sells\SlFeedback\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Model;
use October\Rain\Database\Traits\Validation;
use Sells\MdCatalog\Models\Specialist;
use Sells\SlFeedback\Classes\Enums\enums\QuestionStatuses;

/**
 * Question Model
 *
 * @property boolean $is_active
 * @property string $name
 * @property string $email
 * @property string $question
 * @property string $answer
 * @property string $status
 * @property Carbon|null $answered_at
 * @property Carbon|null $answer_email_sent_at
 * @property string|null $answer_email_hash
 * @property int $specialist_id
 * @property string|null $answer_token
 * @property string|null $category_title
 *
 * @property Specialist $specialist
 *
 * @method static whereHas($relation, \Closure $callback = null, $operator = '>=', $count = 1)
 */
class Question extends Model
{
    use Validation;

    public $table = 'sells_slfeedback_questions';

    public $rules = [];

    public array $customMessages = [];

    protected $fillable = ['stats', 'name', 'email', 'question', 'answered_at'];

    protected $dates = ['answered_at', 'answer_email_sent_at'];

    public $belongsTo = [
        'specialist' => [
            Specialist::class,
            'key' => 'specialist_id'
        ]
    ];

    public function getStatusOptions(): array
    {
        return QuestionStatuses::optionsWithColor();
    }

    public function getStatusFilterOptions(): array
    {
        return QuestionStatuses::optionsWithColor();
    }

    public function getCategoryTitleAttribute(): ?string
    {
        return $this->specialist?->category?->title;
    }

    public function scopeFilterByCategory(Builder $query, array|string|null $categoryIds): Builder
    {
        $categoryIds = array_filter((array)$categoryIds);

        if(!$categoryIds) {
            return $query;
        }

        return $query->whereHas('specialist.category', function(Builder $categoryQuery) use ($categoryIds) {
            $categoryQuery->whereIn('id', $categoryIds);
        });
    }

    public function beforeSave(): void
    {
        $this->syncAnswerState();
    }

    public function beforeCreate(): void
    {
        if(!$this->answer_token) {
            $this->answer_token = Str::random(64);
        }
    }

    private function syncAnswerState(): void
    {
        if(trim((string)$this->answer) === '') {
            $this->status = QuestionStatuses::NEW->value;
            $this->answered_at = null;

            return;
        }

        $this->status = QuestionStatuses::ANSWERED->value;
        $originalAnsweredAt = $this->getOriginal('answered_at');

        if($originalAnsweredAt) {
            $this->answered_at = $originalAnsweredAt;

            return;
        }

        $this->answered_at = Carbon::now();
    }
}
