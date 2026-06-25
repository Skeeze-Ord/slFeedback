<?php namespace Sells\SlFeedback\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\Eloquent\Collection;
use Sells\MdCatalog\Models\Category;
use Sells\MdCatalog\Models\Specialist;
use Sells\SlFeedback\Models\Question;
use Sells\SlFeedback\Models\Settings;

class SlFeedbackFeedback extends ComponentBase
{
    public array|Collection $categories = [];
    public string $categoriesJson = '';
    public string $disclaimer = '';
    public ?Category $selectedCategory = null;
    public array|Collection $questions = [];

    public ?Specialist $responsible = null;

    private const QUESTIONS_LIMIT = 3;

    public function componentDetails(): array
    {
        return [
            'name' => 'Sl Feedback Feedback Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties(): array
    {
        return [];
    }

    public function onRun()
    {
        $this->addJs('assets/js/feedback.js');

        if(!$this->param('category_slug')) {
            $redirect = $this->redirectToFirstCategory();

            if($redirect) {
                return $redirect;
            }
        }

        $this->setVars();
    }

    private function setVars(): void
    {
        $this->selectedCategory = $this->getSelectedCategory();
        $this->categories = $this->getCategories();

        $this->categoriesJson = $this->getCategoriesJson($this->categories, $this->selectedCategory);
        $this->disclaimer = Settings::get('disclaimer', '');
        $this->responsible = $this->selectedCategory?->responsible;
        $this->questions = $this->getQuestions();
    }

    private function getCategoriesJson(Collection $categories, ?Category $selectedCategory): string|false
    {
        return json_encode(
            $this->transformCategoriesForSelect($categories, $selectedCategory),
            JSON_UNESCAPED_UNICODE
        );
    }

    private function getSelectedCategory(): ?Category
    {
        $categorySlug = $this->param('category_slug');

        if(!$categorySlug) {
            return null;
        }

        return Category::query()
            ->with('responsible')
            ->whereNotNull('responsible_id')
            ->where('slug', $categorySlug)
            ->first();
    }

    private function getCategories(): Collection
    {
        return Category::query()
            ->with('responsible')
            ->whereNotNull('responsible_id')
            ->whereNotNull('slug')
            ->orderBy('title')
            ->get();
    }

    private function getQuestions(): Collection
    {
        $result = $this->getQuestionsQuery()
            ->paginate(self::QUESTIONS_LIMIT, 1);

        return new Collection($result->items());
    }

    public function hasMoreQuestions(): bool
    {
        return $this->getQuestionsQuery()
            ->paginate(self::QUESTIONS_LIMIT, 1)
            ->hasMorePages();
    }

    public function onLoadMoreQuestions(): array
    {
        $page = (int) post('page', 1);

        $this->selectedCategory = $this->getSelectedCategory();

        $result = $this->getQuestionsQuery()
            ->paginate(self::QUESTIONS_LIMIT, $page);

        $html = '';

        foreach($result->items() as $question) {
            $html .= $this->renderPartial('@question', compact('question'));
        }

        return [
            'questions_partial' => $html,
            'has_more' => $result->hasMorePages(),
        ];
    }

    private function getQuestionsQuery()
    {
        if (!$this->selectedCategory) {
            return Question::query()->where('id', 0);
        }

        return Question::query()
            ->with('specialist')
            ->where('is_active', true)
            ->whereNotNull('answer')
            ->where('answer', '<>', '')
            ->whereHas('specialist', function($query) {
                $query->where('category_id', $this->selectedCategory->id);
            })
            ->orderByDesc('answered_at')
            ->orderByDesc('id');
    }

    private function redirectToFirstCategory()
    {
        $firstCategory = $this->getCategories()->first();

        if(!$firstCategory) {
            return null;
        }

        return redirect('/feedback/' . $firstCategory->slug);
    }

    private function transformCategoriesForSelect(Collection $categories, ?Category $selectedCategory): array
    {
        $result = [];

        foreach($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'text' => $category->title,
                'selected' => $selectedCategory && $selectedCategory->id === $category->id,
                'link' => '/feedback/' . $category->slug,
            ];
        }

        return $result;
    }
}
