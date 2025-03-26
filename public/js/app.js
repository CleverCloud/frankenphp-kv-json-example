document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.list-group-item').forEach(function(item) {
        const priorityBadge = item.querySelector('.badge:nth-child(2)');
        if (priorityBadge) {
            const priorityText = priorityBadge.textContent.toLowerCase();
            if (priorityText.includes('high')) {
                item.classList.add('priority-high');
            } else if (priorityText.includes('medium')) {
                item.classList.add('priority-medium');
            } else if (priorityText.includes('low')) {
                item.classList.add('priority-low');
            }
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('new')) {
        const taskId = urlParams.get('new');
        const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
        if (taskElement) {
            taskElement.classList.add('highlight');
        }
    }

    document.querySelectorAll('a[href*="action=delete"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
                e.preventDefault();
            }
        });
    });

    const taskForm = document.querySelector('form[action*="action=create"], form[action*="action=update"]');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            const titleInput = document.getElementById('title');
            if (!titleInput.value.trim()) {
                e.preventDefault();
                alert('Le titre de la tâche est obligatoire.');
                titleInput.focus();
            }
        });
    }
});
