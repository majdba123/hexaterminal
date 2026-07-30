document.addEventListener('alpine:init', () => {
    const loadingIndicator = {
        show: function () {
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) indicator.classList.remove('hidden');
        },
        hide: function () {
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) indicator.classList.add('hidden');
        },
        showServicesLoader: function () {
            const servicesLoading = document.getElementById('servicesLoading');
            const servicesContainer = document.getElementById('servicesContainer');
            const servicesEmptyState = document.getElementById('servicesEmptyState');
            if (servicesLoading) servicesLoading.classList.remove('hidden');
            if (servicesContainer) servicesContainer.classList.add('hidden');
            if (servicesEmptyState) servicesEmptyState.classList.add('hidden');
        },
        hideServicesLoader: function () {
            const servicesLoading = document.getElementById('servicesLoading');
            const servicesContainer = document.getElementById('servicesContainer');
            if (servicesLoading) servicesLoading.classList.add('hidden');
            if (servicesContainer) servicesContainer.classList.remove('hidden');
        },
        showEmptyState: function () {
            const servicesEmptyState = document.getElementById('servicesEmptyState');
            const servicesContainer = document.getElementById('servicesContainer');
            const servicesLoading = document.getElementById('servicesLoading');
            if (servicesEmptyState) servicesEmptyState.classList.remove('hidden');
            if (servicesContainer) servicesContainer.classList.add('hidden');
            if (servicesLoading) servicesLoading.classList.add('hidden');
        }
    };

    function coloredToast(color, message) {
        if (typeof Swal !== 'undefined') {
            const toast = Swal.mixin({
                toast: true,
                position: 'bottom-start',
                icon: color === 'success' ? 'success' : 'error',
                title: message,
                showConfirmButton: false,
                timer: 3000,
                showCloseButton: true,
                customClass: { popup: `color-${color}` },
            });
            toast.fire();
        }
    }

    Alpine.data('servicesSection', () => ({
        servicesData: [],
        apiBaseUrl: typeof API_CONFIG !== 'undefined' && API_CONFIG.BASE_URL_Renter ? API_CONFIG.BASE_URL_Renter : 'http://62.84.188.239',

        async init() {
            await this.$nextTick();
            this.fetchServicesData();
        },

        async fetchServicesData() {
            try {
                loadingIndicator.showServicesLoader();
                const token = localStorage.getItem('authToken') || 'your-test-token';
                if (!token) {
                    coloredToast('danger', 'Authentication token missing');
                    loadingIndicator.hideServicesLoader();
                    loadingIndicator.showEmptyState();
                    return;
                }

                const response = await fetch(`${this.apiBaseUrl}/api/services/index`, {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (data.status === 'success' && data.data) {
                    this.servicesData = data.data || [];
                    if (this.servicesData.length === 0) {
                        loadingIndicator.showEmptyState();
                    } else {
                        this.populateServicesSection();
                        loadingIndicator.hideServicesLoader();
                    }
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                loadingIndicator.hideServicesLoader();
                loadingIndicator.showEmptyState();
                coloredToast('danger', error.message || 'Failed to fetch services data');
            }
        },

        populateServicesSection() {
            const servicesContainer = document.querySelector('.row.gy-4.mt-2');
            if (!servicesContainer) {
                return;
            }

            servicesContainer.innerHTML = ''; // Clear static content

            this.servicesData.forEach(service => {
                const serviceItem = document.createElement('div');
                serviceItem.className = 'col-xxl-3 col-lg-4 col-sm-6';
                serviceItem.innerHTML = `
                    <div class="service-wrapper service-style-1">
                        <div class="service-inner">
                            <div class="service-icon">
                                <img
                                    class="img-fluid"
                                    src="${service.image_path || 'assets/icons/placeholder.svg'}"
                                    alt="${service.title || 'Service'}"
                                    onerror="this.src='assets/icons/placeholder.svg';"
                                />
                            </div>
                            <div class="bg-icon">
                                <img
                                    class="img-fluid"
                                    src="${service.image_path || 'assets/icons/placeholder.svg'}"
                                    alt=""
                                    onerror="this.src='assets/icons/placeholder.svg';"
                                />
                            </div>
                            <div class="service-content">
                                <h5 class="service-title">${service.title || 'N/A'}</h5>
                                <p>${service.description || 'No description available'}</p>
                                <div class="service-links">
                                    <a class="btn-arrow" href="#">
                                        <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="25" cy="25" r="25" fill="#F8F8F8"/>
                                            <path d="M15 25H35" stroke="#191A23" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M27 17L35 25L27 33" stroke="#191A23" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                servicesContainer.appendChild(serviceItem);
            });
        }
    }));
});
