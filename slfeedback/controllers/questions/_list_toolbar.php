<div data-control="toolbar loader-container">
    <div class="scoreboard">
        <div data-control="toolbar">
            <div class="scoreboard-item title-value">
                <p><?= $questionsCount ?></p>
                <p class="description">Всего</p>
            </div>
            <div class="scoreboard-item control-chart" data-control="chart-pie">
                <ul>
                    <li data-color="#4CAF50">Отвечено <span><?= $answeredCount ?></span></li>
                    <li data-color="#F44336">Не отвечено <span><?= $unansweredCount ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
