/**
 * City Autocomplete
 * 
 * This script adds autocomplete functionality to city input fields
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing city autocomplete');
    
    // Initialize autocomplete for departure and arrival locations (original format)
    initCityAutocomplete('departure_location');
    initCityAutocomplete('arrival_location');
});

/**
 * Initialize city autocomplete for a specific input field
 * 
 * @param {string} inputId - The ID of the input field
 * @param {string} coordsId - Optional: The ID of the coordinates hidden input
 * @param {string} dropdownId - Optional: The ID of the dropdown container
 */
function initCityAutocomplete(inputId, coordsId = null, dropdownId = null) {
    const input = document.getElementById(inputId);
    if (!input) {
        console.error(`Input with ID ${inputId} not found`);
        return;
    }
    
    console.log(`Initializing autocomplete for ${inputId}`);
    
    // Get or create autocomplete container
    let autocompleteContainer;
    if (dropdownId) {
        autocompleteContainer = document.getElementById(dropdownId);
        if (!autocompleteContainer) {
            console.error(`Dropdown container with ID ${dropdownId} not found`);
            return;
        }
    } else {
        // Create autocomplete container (legacy behavior)
        autocompleteContainer = document.createElement('div');
        autocompleteContainer.className = 'autocomplete-container';
        autocompleteContainer.style.display = 'none';
        input.parentNode.insertBefore(autocompleteContainer, input.nextSibling);
    }
    
    // Get or create coordinates hidden input
    let coordsInput;
    if (coordsId) {
        coordsInput = document.getElementById(coordsId);
        if (!coordsInput) {
            console.error(`Coordinates input with ID ${coordsId} not found`);
        }
    } else {
        // Create coordinates hidden input (legacy behavior)
        coordsInput = document.getElementById(inputId + '_coords');
        if (!coordsInput) {
            console.log(`Creating hidden input for ${inputId}_coords`);
            coordsInput = document.createElement('input');
            coordsInput.type = 'hidden';
            coordsInput.id = inputId + '_coords';
            coordsInput.name = inputId + '_coords';
            input.parentNode.insertBefore(coordsInput, input.nextSibling);
        }
    }
    
    let debounceTimer;
    
    // Add event listener for input changes
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        // Clear coordinates when input changes
        if (coordsInput) {
            coordsInput.value = '';
        }
        
        if (query.length < 2) {
            autocompleteContainer.style.display = 'none';
            return;
        }
        
        // Debounce the API call to avoid too many requests
        debounceTimer = setTimeout(function() {
            fetchCitySuggestions(query, autocompleteContainer, input, coordsInput);
        }, 300);
    });
    
    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== input && !autocompleteContainer.contains(e.target)) {
            autocompleteContainer.style.display = 'none';
        }
    });
    
    // Show autocomplete when input is focused
    input.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            fetchCitySuggestions(query, autocompleteContainer, input, coordsInput);
        }
    });
}

/**
 * Fetch city suggestions from the API
 * 
 * @param {string} query - The search query
 * @param {HTMLElement} container - The container for suggestions
 * @param {HTMLElement} input - The input field
 * @param {HTMLElement} coordsInput - The coordinates input field
 */
function fetchCitySuggestions(query, container, input, coordsInput = null) {
    console.log(`Fetching suggestions for: ${query}`);
    
    // Use the direct API endpoint instead of the routed one
    fetch(`/api-cities.php?q=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(cities => {
            if (cities.error) {
                console.error(cities.error);
                return;
            }
            
            console.log(`Got ${cities.length} suggestions`);
            displayCitySuggestions(cities, container, input, coordsInput);
        })
        .catch(error => {
            console.error('Error fetching city suggestions:', error);
        });
}

/**
 * Display city suggestions in the container
 * 
 * @param {Array} cities - The cities to display
 * @param {HTMLElement} container - The container for suggestions
 * @param {HTMLElement} input - The input field
 * @param {HTMLElement} coordsInput - The coordinates input field
 */
function displayCitySuggestions(cities, container, input, coordsInput = null) {
    // Clear previous suggestions
    container.innerHTML = '';
    
    if (cities.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    // Set appropriate class for styling
    if (!container.className.includes('autocomplete-container') && !container.className.includes('autocomplete-dropdown')) {
        container.className += ' autocomplete-dropdown';
    }
    
    // Create a suggestion for each city
    cities.forEach(city => {
        const suggestion = document.createElement('div');
        suggestion.className = 'autocomplete-item';
        suggestion.textContent = city.fullName;
        
        // Handle click on suggestion
        suggestion.addEventListener('click', function(e) {
            console.log(`Selected city: ${city.fullName}`);
            input.value = city.fullName;
            
            // Store coordinates in the provided coordinates input or fallback to default
            let targetCoordsInput = coordsInput;
            if (!targetCoordsInput) {
                targetCoordsInput = document.getElementById(input.id + '_coords');
            }
            
            if (targetCoordsInput && city.coordinates) {
                targetCoordsInput.value = JSON.stringify(city.coordinates);
                console.log(`Stored coordinates: ${targetCoordsInput.value}`);
            }
            
            container.style.display = 'none';
            e.preventDefault();
            e.stopPropagation();
        });
        
        container.appendChild(suggestion);
    });
    
    // Show the container
    container.style.display = 'block';
} 