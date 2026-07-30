document.addEventListener('alpine:init', () => {
    Alpine.data('reviewsComponent', () => ({
        reviews: [],
        apiBaseUrl: typeof API_CONFIG !== 'undefined' && API_CONFIG.BASE_URL_Renter
            ? API_CONFIG.BASE_URL_Renter
            : 'http://62.84.188.239',

        // مؤقتًا، نعرض جميع التقييمات بدلاً من تصفية المعتمدة فقط
        get approvedReviews() {
            return this.reviews; // إزالة شرط is_approved لعرض جميع التقييمات
            // إذا أردت العودة إلى تصفية المعتمدة فقط، استخدم:
            // return this.reviews.filter(review => review.is_approved === 1);
        },

        async init() {
            await this.fetchReviews();
        },

        async fetchReviews() {
            try {
                const response = await fetch(`${this.apiBaseUrl}/api/review/index`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    this.reviews = data.data.map(review => ({
                        id: review.id,
                        name: review.name,
                        content: review.content,
                        rating: review.rating,
                        review_date: review.review_date,
                        is_approved: review.is_approved,
                        created_at: review.created_at,
                        updated_at: review.updated_at
                    }));
                } else {
                    throw new Error(data.message || 'Failed to fetch reviews');
                }
            } catch (error) {
                console.error('Error fetching reviews:', error);
                this.showToast('error', 'فشل تحميل التقييمات. حاول مرة أخرى لاحقًا.');
            }
        },

        formatDate(dateString) {
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    console.error('Invalid date:', dateString);
                    return 'تاريخ غير صالح';
                }
                return date.toLocaleDateString('ar-EG', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'تاريخ غير صالح';
            }
        },

        showToast(icon, message) {
            const validIcons = ['success', 'error', 'warning', 'info'];
            const safeIcon = validIcons.includes(icon) ? icon : 'error';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'bottom-start',
                    icon: safeIcon,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000,
                    showCloseButton: true,
                    customClass: { popup: `color-${safeIcon}` }
                });
            } else {
                console.warn('SweetAlert2 not loaded, using alert');
                alert(message);
            }
        }
    }));
});
