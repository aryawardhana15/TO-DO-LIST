// Animation for task completion
document.addEventListener('DOMContentLoaded', function() {
    const statusForms = document.querySelectorAll('.todo-status-form');
    
    statusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('button');
            const isCompleted = this.querySelector('input[name="is_completed"]').value === '1';
            
            // Add animation class
            button.classList.add('animate-pulse');
            
            // Change appearance immediately for better UX
            if (isCompleted) {
                button.innerHTML = '<i class="fas fa-check text-xs"></i>';
                button.classList.remove('border-gray-300', 'hover:border-indigo-400');
                button.classList.add('bg-green-500', 'border-green-500', 'text-white');
            } else {
                button.innerHTML = '';
                button.classList.remove('bg-green-500', 'border-green-500', 'text-white');
                button.classList.add('border-gray-300', 'hover:border-indigo-400');
            }
            
            // Submit form after animation
            setTimeout(() => {
                this.submit();
            }, 300);
        });
    });
    
    // Set minimum date for due date (today)
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        const today = new Date().toISOString().split('T')[0];
        dueDateInput.min = today;
    }
});