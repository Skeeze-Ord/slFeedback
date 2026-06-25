window.addEventListener('DOMContentLoaded', () => {
    initLoadMore({
        buttonSelector: '#feedback-load-more',
        containerSelector: '#feedback-questions-container',
        partialKey: 'questions_partial',
        ajaxHandler: 'onLoadMoreQuestions',
    });
});
