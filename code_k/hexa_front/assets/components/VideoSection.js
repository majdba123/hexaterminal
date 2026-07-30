document.addEventListener('alpine:init', () => {
    const loadingIndicator = {
        showVideoLoader: function () {
            const videoLoading = document.getElementById('videoLoading');
            const videoContainer = document.getElementById('videoContainer');
            const videoEmptyState = document.getElementById('videoEmptyState');
            if (videoLoading) videoLoading.classList.remove('hidden');
            if (videoContainer) videoContainer.classList.add('hidden');
            if (videoEmptyState) videoEmptyState.classList.add('hidden');
        },
        hideVideoLoader: function () {
            const videoLoading = document.getElementById('videoLoading');
            const videoContainer = document.getElementById('videoContainer');
            if (videoLoading) videoLoading.classList.add('hidden');
            if (videoContainer) videoContainer.classList.remove('hidden');
        },
        showEmptyState: function () {
            const videoEmptyState = document.getElementById('videoEmptyState');
            const videoContainer = document.getElementById('videoContainer');
            const videoLoading = document.getElementById('videoLoading');
            if (videoEmptyState) videoEmptyState.classList.remove('hidden');
            if (videoContainer) videoContainer.classList.add('hidden');
            if (videoLoading) videoLoading.classList.add('hidden');
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

    Alpine.data('videoSection', () => ({
        videoData: {},
        embedUrl: '',
        apiBaseUrl:
            typeof API_CONFIG !== "undefined" &&
            API_CONFIG.BASE_URL_Renter,
        async init() {
            await this.$nextTick();
            await this.fetchVideoData();
        },

        async fetchVideoData() {
            try {
                loadingIndicator.showVideoLoader();

                const response = await fetch(`${this.apiBaseUrl}/api/video/index`);

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();

                if (data.status === 'success' && data.data?.length > 0) {
                    this.videoData = data.data[0];

                    if (this.videoData.video_url?.trim()) {
                        // تحويل الرابط إلى رابط مضمن
                        this.embedUrl = this.convertToEmbedUrl(this.videoData.video_url);

                        loadingIndicator.hideVideoLoader();
                    } else {
                        throw new Error('No video URL available');
                    }
                } else {
                    throw new Error(data.message || 'No video data available');
                }
            } catch (error) {
                console.error('Error fetching video data:', error);
                loadingIndicator.hideVideoLoader();
                loadingIndicator.showEmptyState();
                coloredToast('danger', error.message || 'Failed to fetch video data');
            }
        },

        convertToEmbedUrl(url) {
            if (!url) return '';


            // YouTube - جميع الأنماط
            if (url.includes('youtube.com') || url.includes('youtu.be')) {
                return this.getYouTubeEmbedUrl(url);
            }

            // Vimeo
            if (url.includes('vimeo.com')) {
                return this.getVimeoEmbedUrl(url);
            }

            // DailyMotion
            if (url.includes('dailymotion.com')) {
                return this.getDailyMotionEmbedUrl(url);
            }

            // إذا كان الرابط مضمن بالفعل، نعيده كما هو
            if (url.includes('/embed/')) {
                return url;
            }

            // إذا لم نستطع تحويل الرابط، نعيد الرابط الأصلي
            // (قد يعمل إذا كان الموقع يدعم التضمين)
            return url;
        },

        getYouTubeEmbedUrl(url) {
            try {
                let videoId = '';

                // النمط 1: https://www.youtube.com/watch?v=VIDEO_ID
                if (url.includes('youtube.com/watch')) {
                    const urlObj = new URL(url);
                    videoId = urlObj.searchParams.get('v');
                }
                // النمط 2: https://youtu.be/VIDEO_ID
                else if (url.includes('youtu.be')) {
                    videoId = url.split('/').pop()?.split('?')[0];
                }
                // النمط 3: https://www.youtube.com/embed/VIDEO_ID
                else if (url.includes('/embed/')) {
                    return url; // الرابط مضمن بالفعل
                }
                // النمط 4: https://www.youtube.com/v/VIDEO_ID
                else if (url.includes('/v/')) {
                    videoId = url.split('/v/')[1]?.split('?')[0];
                }

                if (videoId) {
                    return `https://www.youtube.com/embed/${videoId}`;
                }
            } catch (error) {
                console.error('Error parsing YouTube URL:', error);
            }

            return url;
        },

        getVimeoEmbedUrl(url) {
            try {
                const videoId = url.split('/').pop()?.split('?')[0];
                if (videoId) {
                    return `https://player.vimeo.com/video/${videoId}`;
                }
            } catch (error) {
                console.error('Error parsing Vimeo URL:', error);
            }

            return url;
        },

        getDailyMotionEmbedUrl(url) {
            try {
                const videoId = url.split('/').pop()?.split('?')[0];
                if (videoId) {
                    return `https://www.dailymotion.com/embed/video/${videoId}`;
                }
            } catch (error) {
                console.error('Error parsing DailyMotion URL:', error);
            }

            return url;
        },

        formatDate(dateString) {
            if (!dateString) return 'غير محدد';
            try {
                return new Date(dateString).toLocaleDateString('ar-EG', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            } catch {
                return 'غير محدد';
            }
        }
    }));
});
