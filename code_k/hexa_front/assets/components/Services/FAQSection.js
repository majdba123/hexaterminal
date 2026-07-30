document.addEventListener('alpine:init', () => {
    Alpine.data('faqComponent', () => ({
        faqs: [],
        apiBaseUrl: typeof API_CONFIG !== 'undefined' && API_CONFIG.BASE_URL_Renter ? API_CONFIG.BASE_URL_Renter : 'http://62.84.188.239',

        async init() {
            await this.fetchFaqs();
        },

        async fetchFaqs() {
            try {
                const response = await fetch(`${this.apiBaseUrl}/api/faq/index`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    this.faqs = data.data.map(faq => ({
                        id: faq.id,
                        question: faq.question,
                        answer: faq.answer,
                        created_at: faq.created_at,
                        updated_at: faq.updated_at,
                        isOpen: false
                    }));
                } else {
                    throw new Error(data.message || 'Failed to fetch FAQs');
                }
            } catch (error) {
                console.error('Error fetching FAQs:', error);
                this.showToast('error', 'Failed to load FAQs. Please try again later.');
            }
        },

        toggleFaq(index) {
            this.faqs = this.faqs.map((faq, i) => ({
                ...faq,
                isOpen: i === index ? !faq.isOpen : faq.isOpen
            }));
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
                alert(message);
            }
        }
    }));
});
