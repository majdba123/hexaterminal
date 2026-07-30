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
        showTeamLoader: function () {
            const teamLoading = document.getElementById('teamLoading');
            const teamContainer = document.getElementById('teamContainer');
            const teamEmptyState = document.getElementById('teamEmptyState');
            if (teamLoading) teamLoading.classList.remove('hidden');
            if (teamContainer) teamContainer.classList.add('hidden');
            if (teamEmptyState) teamEmptyState.classList.add('hidden');
        },
        hideTeamLoader: function () {
            const teamLoading = document.getElementById('teamLoading');
            const teamContainer = document.getElementById('teamContainer');
            if (teamLoading) teamLoading.classList.add('hidden');
            if (teamContainer) teamContainer.classList.remove('hidden');
        },
        showEmptyState: function () {
            const teamEmptyState = document.getElementById('teamEmptyState');
            const teamContainer = document.getElementById('teamContainer');
            const teamLoading = document.getElementById('teamLoading');
            if (teamEmptyState) teamEmptyState.classList.remove('hidden');
            if (teamContainer) teamContainer.classList.add('hidden');
            if (teamLoading) teamLoading.classList.add('hidden');
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

    Alpine.data('teamSection', () => ({
        teamData: [],
        apiBaseUrl: typeof API_CONFIG !== 'undefined' && API_CONFIG.BASE_URL_Renter ? API_CONFIG.BASE_URL_Renter : 'http://62.84.188.239',

        async init() {
            await this.$nextTick();
            this.fetchTeamData();
        },

        async fetchTeamData() {
            try {
                loadingIndicator.showTeamLoader();
                const token = localStorage.getItem('authToken') || 'your-test-token';
                if (!token) {
                    coloredToast('danger', 'Authentication token missing');
                    loadingIndicator.hideTeamLoader();
                    loadingIndicator.showEmptyState();
                    return;
                }

                const response = await fetch(`${this.apiBaseUrl}/api/teams/index`, {
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
                    this.teamData = data.data || [];
                    if (this.teamData.length === 0) {
                        loadingIndicator.showEmptyState();
                    } else {
                        this.populateTeamSection();
                        loadingIndicator.hideTeamLoader();
                    }
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                loadingIndicator.hideTeamLoader();
                loadingIndicator.showEmptyState();
                coloredToast('danger', error.message || 'Failed to fetch team data');
            }
        },

        populateTeamSection() {
            const teamContainers = document.querySelectorAll('.team-boxs.grid-wrapper');
            if (teamContainers.length < 2) {
                return;
            }

            teamContainers.forEach(container => container.innerHTML = '');
            const leftContainer = teamContainers[0];
            const rightContainer = teamContainers[1];

            this.teamData.forEach((member, index) => {
                const fullName = `${member.first_name || ''} ${member.last_name || ''}`.trim();
                const teamItem = document.createElement('div');
                teamItem.className = 'team-item team-style-2';
                teamItem.innerHTML = `
                    <div class="team-img">
                        <img
                            loading="lazy"
                            class="img-fluid"
                            src="${member.photo || 'assets/images/person1.jpg'}"
                            alt="${fullName || 'Team Member'}"
                            onerror="this.src='assets/images/person1.jpg';"
                        />
                        <div class="image-overlay"></div>
                        <div class="team-social">
                            <div class="share-icon">
                                <img class="img-fluid" src="assets/icons/share.svg" alt="Share" />
                                <ul>
                                    <li>
                                        <a href="${member.github_url || '#'}" target="_blank">
                                            <span data-i18n="social.github">GitHub</span>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank">
                                            <span data-i18n="social.linkedin">LinkedIn</span>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank">
                                            <span data-i18n="social.facebook">Facebook</span>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </a>
                                    </li>
                                    ${member.cv_file ? `
                                        <li>
                                            <a href="${member.cv_file}" target="_blank" download>
                                                <span data-i18n="social.cv">Download CV</span>
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </a>
                                        </li>
                                    ` : ''}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="team-info">
                        <a href="#" class="team-title">${fullName || 'Unknown'}</a>
                        <span class="team-destination">${member.position || 'N/A'}</span>
                        <div class="team-details">
                            <p><strong>Email:</strong> <a href="mailto:${member.email || '#'}" class="${member.email ? '' : 'text-muted'}">${member.email || 'N/A'}</a></p>
                            <p><strong>Phone:</strong> <a href="tel:${member.phone || '#'}" class="${member.phone ? '' : 'text-muted'}">${member.phone || 'N/A'}</a></p>
                            <p><strong>Specialization:</strong> ${member.specialization || 'N/A'}</p>
                        </div>
                    </div>
                `;
                if (index % 2 === 0) {
                    leftContainer.appendChild(teamItem);
                } else {
                    rightContainer.appendChild(teamItem);
                }
            });
        }
    }));
});
