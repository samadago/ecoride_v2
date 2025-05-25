// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize any charts on the dashboard
    initCharts();
    
    // Setup confirmation dialogs for delete actions
    setupDeleteConfirmations();
    
    // Initialize status change handlers
    setupStatusChangeHandlers();
});

/**
 * Initialize Chart.js charts if they exist on the page
 */
function initCharts() {
    // Rides by Status chart
    const rideStatusChart = document.getElementById('rideStatusChart');
    if (rideStatusChart) {
        const ctx = rideStatusChart.getContext('2d');
        
        // Get data from the data attributes
        const pending = parseInt(rideStatusChart.getAttribute('data-pending') || 0);
        const ongoing = parseInt(rideStatusChart.getAttribute('data-ongoing') || 0);
        const completed = parseInt(rideStatusChart.getAttribute('data-completed') || 0);
        const cancelled = parseInt(rideStatusChart.getAttribute('data-cancelled') || 0);
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'En cours', 'Terminé', 'Annulé'],
                datasets: [{
                    data: [pending, ongoing, completed, cancelled],
                    backgroundColor: [
                        '#f39c12', // pending - orange
                        '#3498db', // ongoing - blue
                        '#2ecc71', // completed - green
                        '#e74c3c'  // cancelled - red
                    ],
                    borderColor: [
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
    }
    
    // Users over time chart (if exists)
    const usersChart = document.getElementById('usersChart');
    if (usersChart) {
        const ctx = usersChart.getContext('2d');
        
        // This would typically be populated with real data from the backend
        // For now, we'll use some sample data
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Nouveaux utilisateurs',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: 'rgba(78, 206, 93, 0.2)',
                    borderColor: '#4ECE5D',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

/**
 * Setup confirmation dialogs for delete actions
 */
function setupDeleteConfirmations() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Setup status change handlers for rides
 */
function setupStatusChangeHandlers() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            // Find the closest form and submit it
            this.closest('form').submit();
        });
    });
}

/**
 * Toggle admin status confirmation
 */
function confirmToggleAdmin(userId, isAdmin) {
    const action = isAdmin ? "retirer les droits d'administrateur de" : "donner les droits d'administrateur à";
    if (confirm(`Êtes-vous sûr de vouloir ${action} cet utilisateur ?`)) {
        window.location.href = `/admin/users/toggle-admin/${userId}`;
    }
}

/**
 * Toggle eco-friendly status confirmation
 */
function confirmToggleEco(vehicleId, isEco) {
    const action = isEco ? "retirer le statut écologique de" : "définir comme écologique";
    if (confirm(`Êtes-vous sûr de vouloir ${action} ce véhicule ?`)) {
        window.location.href = `/admin/vehicles/toggle-eco/${vehicleId}`;
    }
} 