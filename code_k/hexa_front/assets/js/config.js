/**
 * API Configuration
 * Central configuration for API endpoints and settings
 */

window.API_CONFIG = {
    // Local development URL (uncomment to use)
    // BASE_URL_Renter: 'http://localhost:8000',

    // Production API URLs
    BASE_URL_Renter: 'http://62.84.188.239',
    BASE_URL_Uber: 'http://62.84.188.239',

    // API Endpoints
    ENDPOINTS: {
        LOGIN: '/api/login',
    },

    // Default HTTP Headers
    DEFAULT_HEADERS: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },

    // Google Maps API Key
    GOOGLE_MAPS_API_KEY: 'AIzaSyC5eqWELYeuHhL0gLu4BVHjbksnLlKA2uI'
};
