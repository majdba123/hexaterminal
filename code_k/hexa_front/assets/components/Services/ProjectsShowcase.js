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
        showProjectsLoader: function () {
            const projectsLoading = document.getElementById('projectsLoading');
            const projectsContainer = document.getElementById('projectsContainer');
            const projectsEmptyState = document.getElementById('projectsEmptyState');
            if (projectsLoading) projectsLoading.classList.remove('hidden');
            if (projectsContainer) projectsContainer.classList.add('hidden');
            if (projectsEmptyState) projectsEmptyState.classList.add('hidden');
        },
        hideProjectsLoader: function () {
            const projectsLoading = document.getElementById('projectsLoading');
            const projectsContainer = document.getElementById('projectsContainer');
            if (projectsLoading) projectsLoading.classList.add('hidden');
            if (projectsContainer) projectsContainer.classList.remove('hidden');
        },
        showEmptyState: function () {
            const projectsEmptyState = document.getElementById('projectsEmptyState');
            const projectsContainer = document.getElementById('projectsContainer');
            const projectsLoading = document.getElementById('projectsLoading');
            if (projectsEmptyState) projectsEmptyState.classList.remove('hidden');
            if (projectsContainer) projectsContainer.classList.add('hidden');
            if (projectsLoading) projectsLoading.classList.add('hidden');
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

    Alpine.data('projectsSection', () => ({
        projectsData: [],
        apiBaseUrl: typeof API_CONFIG !== 'undefined' && API_CONFIG.BASE_URL_Renter ? API_CONFIG.BASE_URL_Renter : 'http://62.84.188.239',

        async init() {
            await this.$nextTick();
            this.fetchProjectsData();
        },

        async fetchProjectsData() {
            try {
                loadingIndicator.showProjectsLoader();
                const token = localStorage.getItem('authToken') || 'your-test-token';
                if (!token) {
                    coloredToast('danger', 'Authentication token missing');
                    loadingIndicator.hideProjectsLoader();
                    loadingIndicator.showEmptyState();
                    return;
                }

                const response = await fetch(`${this.apiBaseUrl}/api/projects/index`, {
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
                    this.projectsData = data.data || [];
                    if (this.projectsData.length === 0) {
                        loadingIndicator.showEmptyState();
                    } else {
                        this.populateProjectsSection();
                        loadingIndicator.hideProjectsLoader();
                    }
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                loadingIndicator.hideProjectsLoader();
                loadingIndicator.showEmptyState();
                coloredToast('danger', error.message || 'Failed to fetch projects data');
            }
        },

        populateProjectsSection() {
            const projectContainers = document.querySelectorAll('.project-boxs.grid-wrapper');
            if (projectContainers.length < 2) {
                return;
            }

            projectContainers.forEach(container => container.innerHTML = '');
            const leftContainer = projectContainers[1];
            const rightContainer = projectContainers[0];

            this.projectsData.forEach((project, index) => {
                const imagePath = project.images && project.images[0]?.image_path;
                const projectItem = document.createElement('div');
                projectItem.className = 'team-item team-style-2';
                projectItem.innerHTML = `
                    <div class="team-img">
                        <img
                            loading="lazy"
                            class="img-fluid"
                            src="${imagePath}"
                            alt="${project.title || 'Project'}"
                        />
                        <div class="image-overlay"></div>
                        <div class="team-social">
                            <div class="share-icon">
                                <a href="project.html?id=${project.id}">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="team-info">
                        <a href="project.html?id=${project.id}" class="team-title">${project.title || 'Unknown'}</a>
                        <span class="team-destination">${project.category || 'N/A'}</span>
                        <div class="team-details">
                            <p><strong>Description:</strong> ${project.description || 'N/A'}</p>
                        </div>
                    </div>
                `;
                if (index % 2 === 0) {
                    rightContainer.appendChild(projectItem);

                } else {
                    leftContainer.appendChild(projectItem);

                }
            });
        }
    }));
});
