document.addEventListener('DOMContentLoaded', function () {
    const relatedPostsForm = document.getElementById('related-posts-settings-form');
    
    if (relatedPostsForm) {
        const filterSelect = document.getElementById('related_posts_filter');
        const postsCountInput = document.getElementById('related_posts_count');

        filterSelect.addEventListener('change', function () {
            const selectedOption = this.value;
            console.log(`Filter type changed to: ${selectedOption}`);
        });

        postsCountInput.addEventListener('input', function () {
            const value = parseInt(this.value, 10);
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > 10) {
                this.value = 10; 
            }
        });

        relatedPostsForm.addEventListener('submit', function (e) {
            const postsCount = parseInt(postsCountInput.value, 10);

            if (isNaN(postsCount) || postsCount < 1 || postsCount > 10) {
                e.preventDefault();
                alert('تعداد مطالب مرتبط باید بین 1 تا 10 باشد.');
            }
        });
    }
});
