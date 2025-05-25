/**
 * Ride Status Tracking
 * 
 * This script handles real-time ride status updates and actions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find the ride status element and buttons
    const rideStatusEl = document.getElementById('ride-status');
    const statusButtons = document.querySelectorAll('.status-btn');
    const rideId = document.getElementById('ride-id')?.value;
    
    if (!rideId) return;
    
    // Initialize the event source for server-sent events
    let eventSource = null;
    
    function connectEventSource() {
        if (eventSource) {
            eventSource.close();
        }
        
        // Create a new EventSource connection
        eventSource = new EventSource(`/api-ride-events.php?ride_id=${rideId}`);
        
        // Handle incoming status updates
        eventSource.addEventListener('status', function(event) {
            try {
                const data = JSON.parse(event.data);
                updateStatusDisplay(data.status);
            } catch (error) {
                console.error('Error parsing status event:', error);
            }
        });
        
        // Handle errors
        eventSource.addEventListener('error', function(event) {
            console.error('EventSource error:', event);
            // Try to reconnect after a delay
            setTimeout(connectEventSource, 5000);
        });
        
        // Handle ping events to keep the connection alive
        eventSource.addEventListener('ping', function(event) {
            console.log('Ping received:', event.data);
        });
        
        // Handle close events
        eventSource.addEventListener('close', function(event) {
            console.log('Connection closed by server:', event.data);
            eventSource.close();
            // Try to reconnect after a delay
            setTimeout(connectEventSource, 5000);
        });
    }
    
    // Connect to the event source
    connectEventSource();
    
    // Add event listeners to status buttons
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const newStatus = this.getAttribute('data-status');
            updateRideStatus(newStatus);
        });
    });
    
    /**
     * Update the ride status display based on the current status
     */
    function updateStatusDisplay(status) {
        if (!rideStatusEl) return;
        
        // Update status text
        rideStatusEl.textContent = getStatusLabel(status);
        
        // Update status class
        rideStatusEl.className = 'ride-status';
        rideStatusEl.classList.add(`status-${status}`);
        
        // Update available buttons based on the current status
        updateAvailableActions(status);
        
        // Update page elements based on status
        const rideCard = document.querySelector('.detail-card');
        if (rideCard) {
            // Remove existing status classes
            rideCard.classList.remove('status-pending', 'status-ongoing', 'status-completed', 'status-cancelled');
            // Add the current status class
            rideCard.classList.add(`status-${status}`);
        }
    }
    
    /**
     * Update which action buttons are available based on the current status
     */
    function updateAvailableActions(status) {
        statusButtons.forEach(button => {
            const buttonStatus = button.getAttribute('data-status');
            
            // Reset button state
            button.disabled = false;
            
            // Disable the current status button and buttons that aren't valid transitions
            if (buttonStatus === status) {
                button.disabled = true;
            } else if (status === 'completed' || status === 'cancelled') {
                // Can't change status after completion or cancellation
                button.disabled = true;
            } else if (status === 'pending' && buttonStatus === 'completed') {
                // Can't complete a ride that hasn't started
                button.disabled = true;
            } else if (status === 'ongoing' && buttonStatus === 'pending') {
                // Can't go back to pending once ongoing
                button.disabled = true;
            }
        });
    }
    
    /**
     * Send a request to update the ride status
     */
    function updateRideStatus(newStatus) {
        // Create form data
        const formData = new FormData();
        formData.append('ride_id', rideId);
        formData.append('status', newStatus);
        
        // Send the request
        fetch('/api-update-ride-status.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Status updated successfully:', data.status);
                // Show success message
                showMessage('Status updated successfully', 'success');
                // Update UI immediately instead of waiting for SSE
                updateStatusDisplay(data.status);
            } else {
                console.error('Error updating status:', data.error);
                showMessage(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating ride status:', error);
            showMessage('Failed to update status. Please try again.', 'error');
        });
    }
    
    /**
     * Get a human-readable label for the status
     */
    function getStatusLabel(status) {
        const labels = {
            'pending': 'En attente',
            'ongoing': 'En cours',
            'completed': 'Terminé',
            'cancelled': 'Annulé'
        };
        
        return labels[status] || status;
    }
    
    /**
     * Show a message to the user
     */
    function showMessage(message, type = 'info') {
        // Create or get the messages container
        let messagesContainer = document.querySelector('.status-messages');
        if (!messagesContainer) {
            messagesContainer = document.createElement('div');
            messagesContainer.className = 'status-messages';
            document.querySelector('.container').prepend(messagesContainer);
        }
        
        // Create the message element
        const messageEl = document.createElement('div');
        messageEl.className = `message ${type}`;
        messageEl.textContent = message;
        
        // Add the message to the container
        messagesContainer.appendChild(messageEl);
        
        // Remove the message after a delay
        setTimeout(() => {
            messageEl.classList.add('fade-out');
            setTimeout(() => {
                messageEl.remove();
            }, 500);
        }, 3000);
    }
    
    // Handle page unload to close EventSource connection
    window.addEventListener('beforeunload', function() {
        if (eventSource) {
            eventSource.close();
        }
    });
}); 