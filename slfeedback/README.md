# SlFeedback

Плагин обратной связи для OctoberCMS. Хранит вопросы пользователей, связывает каждый вопрос со специалистом из `Sells.MdCatalog`, отправляет специалисту письмо со ссылкой для ответа и выводит опубликованные ответы на странице `/feedback`.

## Основные возможности

- backend-раздел `/panel/sells/slfeedback/questions` для управления вопросами;
- редактируемый дисклеймер на странице списка вопросов в backend;
- публичный API для создания вопроса;
- привязка вопроса к специалисту;
- письмо специалисту после создания вопроса;
- отдельная backend-страница ответа по токен-ссылке из письма;
- frontend-компонент `SlFeedbackFeedback` для страницы обратной связи;
- фильтры списка вопросов по статусу, специалисту и направлению.

## Зависимости

Плагин использует модели из `Sells.MdCatalog`:

- `Sells\MdCatalog\Models\Category` - направление;
- `Sells\MdCatalog\Models\Specialist` - специалист.

Для корректной работы страницы `/feedback` у направления должны быть:

- `slug`;
- `responsible_id`;
- активный ответственный специалист.

Для отправки письма специалисту у модели специалиста должен быть заполнен `email`.

## Структура базы данных

### `sells_slfeedback_questions`

| Поле | Тип | Описание |
|---|---|---|
| `id` | bigint | Идентификатор вопроса |
| `is_active` | boolean | Показывать вопрос на сайте |
| `name` | string | ФИО автора |
| `email` | string | Email автора |
| `question` | text | Текст вопроса |
| `answer` | text, nullable | Ответ специалиста |
| `status` | string | Статус вопроса: `new` или `answered` |
| `answered_at` | timestamp, nullable | Дата ответа |
| `specialist_id` | bigint, nullable в БД | Связанный специалист |
| `answer_token` | string, nullable, unique | Токен ссылки для ответа |
| `created_at`, `updated_at` | timestamp | Служебные даты |

`specialist_id` исторически nullable на уровне БД, но на уровне приложения новый вопрос требует специалиста.

## Модель Question

Файл: `models/Question.php`

Связи:

- `specialist` - `belongsTo` к `Sells\MdCatalog\Models\Specialist`.

Автоматическое поведение:

- `beforeCreate()` генерирует `answer_token`;
- `beforeSave()` синхронизирует статус:
  - пустой ответ -> `new`, `answered_at = null`;
  - непустой ответ -> `answered`, `answered_at` заполняется при первом ответе.

Дополнительные методы:

- `getStatusOptions()` - варианты статуса для backend-колонки;
- `getStatusFilterOptions()` - варианты статуса для фильтра;
- `getCategoryTitleAttribute()` - название направления через `specialist.category`;
- `scopeFilterByCategory()` - фильтр вопросов по направлению.

## Backend

### Список вопросов

URL:

```text
/panel/sells/slfeedback/questions
```

Контроллер:

```text
controllers/Questions.php
```

Конфигурация списка:

```text
controllers/questions/config_list.yaml
models/question/columns.yaml
models/question/scopes.yaml
```

В списке доступны колонки:

- номер вопроса;
- статус;
- вопрос;
- специалист;
- направление.

Фильтры:

- статус, включая вариант `Все статусы`;
- специалист;
- направление.

### Дисклеймер

На странице списка вопросов над таблицей выводится форма редактирования дисклеймера.

Хранение:

```text
models/Settings.php
```

Обработчик:

```php
Questions::onSaveDisclaimer()
```

Значение используется frontend-компонентом `SlFeedbackFeedback`.

### Страница ответа по ссылке

URL:

```text
/panel/sells/slfeedback/questions/answer/{id}/{answer_token}
```

Action:

```php
Questions::answer()
```

View:

```text
controllers/questions/answer.php
```

Страница:

- проверяет пару `id + answer_token`;
- скрывает верхнее backend-меню через `hideMainMenu`;
- показывает автора и вопрос;
- выводит поле `answer` через стандартный backend `richeditor`;
- сохраняет ответ через `onSaveAnswer()`;
- содержит кнопку закрытия вкладки.

Обычная страница редактирования вопроса остается доступной по:

```text
/panel/sells/slfeedback/questions/update/{id}
```

## Frontend-компонент

Компонент:

```text
components/SlFeedbackFeedback.php
```

Alias:

```text
SlFeedbackFeedback
```

Основной шаблон:

```text
components/slfeedbackfeedback/default.htm
```

Partial вопроса:

```text
components/slfeedbackfeedback/question.htm
```

JS для дозагрузки:

```text
assets/js/feedback.js
```

### Роут страницы

Страница темы должна принимать необязательный slug направления:

```ini
url = "/feedback/:category_slug?"

[SlFeedbackFeedback]
```

Если `category_slug` не указан, компонент редиректит на первое доступное направление. Если подходящих направлений нет, выводится partial `site/no-results`.

### Данные компонента

В шаблоне доступны:

| Переменная | Описание |
|---|---|
| `categories` | коллекция направлений с ответственными специалистами |
| `categoriesJson` | JSON для мобильного select |
| `selectedCategory` | выбранное направление |
| `responsible` | ответственный специалист выбранного направления |
| `questions` | первая страница вопросов по специалистам выбранного направления |
| `disclaimer` | текст дисклеймера из настроек |

### Логика вопросов на странице

Компонент берет вопросы:

- только активные (`is_active = true`);
- только с непустым ответом;
- только от специалистов, привязанных к выбранному направлению;
- сортировка: свежие ответы выше.

Пагинация работает через кнопку `#feedback-load-more` и общий проектный helper `initLoadMore`.

AJAX-обработчик:

```php
SlFeedbackFeedback::onLoadMoreQuestions()
```

Ответ:

```php
[
    'questions_partial' => $html,
    'has_more' => bool,
]
```

## API создания вопроса

Route:

```http
POST /api/v1/slfeedback/questions
```

Контроллер:

```text
http/controllers/QuestionsController.php
```

Request:

```text
requests/QuestionSubmitRequest.php
```

Поля запроса:

| Поле | Правила |
|---|---|
| `name` | required, string, max:255 |
| `email` | required, email, max:255 |
| `question` | required, string, max:5000 |
| `specialist_id` | required, integer, exists в `sells_mdcatalog_specialists` |

Успешный ответ содержит ресурс:

```json
{
  "question": {
    "id": 1,
    "status": "new"
  }
}
```

## Сервис создания вопроса

Файл:

```text
services/QuestionSubmitService.php
```

Порядок работы:

1. Проверяет наличие `specialist_id`.
2. Создает `Question`.
3. Привязывает вопрос к специалисту.
4. Сохраняет вопрос.
5. Загружает связь `specialist`.
6. Отправляет письмо через `QuestionSpecialistMailService`.

Если письмо специалисту не отправлено, сервис выбрасывает `ApplicationException`.

## Письмо специалисту

Сервис:

```text
services/QuestionSpecialistMailService.php
```

Шаблон:

```text
views/mail/question_for_specialist.htm
```

Mail template:

```text
sells.slfeedback::mail.question_for_specialist
```

Письмо отправляется на `specialist.email` и содержит ссылку:

```text
/panel/sells/slfeedback/questions/answer/{question_id}/{answer_token}
```

Через эту ссылку специалист видит вопрос и может заполнить ответ.

## Связь с формой обратной связи

Для формы `feedback` в `Sells.SlForms` прокидывается скрытое поле `specialist_id` из текущего компонента `SlFeedbackFeedback.responsible`.

Это нужно, чтобы каждый новый вопрос был привязан к ответственному специалисту выбранного направления.

## Права доступа

Плагин регистрирует permission:

```text
sells.slfeedback.questions
```

Право дает доступ к управлению вопросами в backend.

## Миграции

Файлы:

```text
updates/create_questions_table.php
updates/modify_questions_table_add_answer_token_column.php
updates/version.yaml
```

После изменения структуры БД нужно выполнить проектную команду миграций October:
```bash
php artisan october:migrate
```
## Проверки при изменениях

Минимальные проверки после правок:

```bash
php -l plugins/sells/slfeedback/models/Question.php
php -l plugins/sells/slfeedback/controllers/Questions.php
php artisan plugin:refresh Sells.SlFeedback
```

`plugin:refresh` пересоздает таблицы плагина и удаляет данные, поэтому применять только на локальном окружении или когда это безопасно.
