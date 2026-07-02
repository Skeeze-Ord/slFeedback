<div class="layout-row">
    <div class="scoreboard">
        <div data-control="toolbar">
            <div class="scoreboard-item title-value">
                <p><?= e($question->id) ?></p>
                <p class="description">Номер вопроса</p>
            </div>
            <div class="scoreboard-item title-value">
                <p><?= e($question->specialist?->name ?? '-') ?></p>
                <p class="description">Специалист</p>
            </div>
        </div>
    </div>
</div>

<?= Form::open([
    'data-request' => 'onSaveAnswer',
    'data-request-message' => 'Сохранение ответа...',
    'class' => 'layout',
]) ?>
    <input type="hidden" name="question_id" value="<?= e($question->id) ?>">
    <input type="hidden" name="answer_token" value="<?= e($question->answer_token) ?>">

    <div class="form-widget">
        <div class="form-group span-full">
            <div style="border: 1px solid #dfe3e8; border-radius: 4px; background: #fff; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #eef0f3; display: flex; gap: 16px; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <div style="font-size: 12px; line-height: 1.2; color: #7b8199; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 5px;">Автор</div>
                        <div style="font-size: 16px; line-height: 1.35; color: #2b303b; font-weight: 600;"><?= e($question->name) ?></div>
                    </div>
                    <div style="font-size: 14px; line-height: 1.35; color: #5f667a; text-align: right; padding-top: 18px;">
                        <?= e($question->email) ?>
                    </div>
                </div>
                <div style="padding: 18px 20px 20px;">
                    <div style="font-size: 12px; line-height: 1.2; color: #7b8199; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px;">Вопрос</div>
                    <div style="font-size: 15px; line-height: 1.6; color: #2b303b; background: #f7f8fa; border-radius: 4px; padding: 14px 16px;">
                        <?= nl2br(e($question->question)) ?>
                    </div>
                </div>
            </div>
        </div>

        <?= $this->formRenderField('answer') ?>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn btn-primary oc-icon-save">
            Сохранить ответ
        </button>
        <span class="btn-text">
            <span class="button-separator">или</span>
            <button type="button" class="btn btn-link p-0" onclick="window.close()">закрыть</button>
        </span>
    </div>
<?= Form::close() ?>
