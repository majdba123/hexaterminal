
document.addEventListener('alpine:init', () => {
    Alpine.data('contactForm', () => ({
        formData: {
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: ''
        },
        errors: {
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: ''
        },
        isSubmitting: false,
        apiBaseUrl: typeof API_CONFIG !== 'undefined' && API_CONFIG.BASE_URL_Renter
            ? API_CONFIG.BASE_URL_Renter
            : 'https://your-production-api.com', // Replace with your production URL

        validateForm() {
            this.errors = {
                name: this.formData.name ? '' : 'Name is required',
                email: this.formData.email ? (this.validateEmail(this.formData.email) ? '' : 'Invalid email format') : 'Email is required',
                phone: this.formData.phone ? (this.validatePhone(this.formData.phone) ? '' : 'Invalid phone number') : 'Phone is required',
                subject: this.formData.subject ? '' : 'Subject is required',
                message: this.formData.message ? '' : 'Message is required'
            };
            return Object.values(this.errors).every(error => !error);
        },

        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        validatePhone(phone) {
            // More flexible phone regex allowing spaces, dashes, and parentheses
            const re = /^\+?[\d\s()-]{7,15}$/;
            return re.test(phone);
        },

        async submitForm() {
            if (!this.validateForm()) {
                this.showToast('error', 'Please fill in all required fields correctly');
                return;
            }

            this.isSubmitting = true;
            const payload = {
                email: this.formData.email,
                name: this.formData.name,
                phone: this.formData.phone,
                subject: this.formData.subject,
                message: this.formData.message
            };

            try {
                const response = await fetch(`${this.apiBaseUrl}/api/contact_us/store`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors && typeof data.errors === 'object') {
                        this.errors = {
                            name: data.errors.name?.[0] || '',
                            email: data.errors.email?.[0] || '',
                            phone: data.errors.phone?.[0] || '',
                            subject: data.errors.subject?.[0] || '',
                            message: data.errors.message?.[0] || ''
                        };
                        this.showToast('error', 'Please correct the errors in the form');
                    } else {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }
                    return;
                }

                if (data.status === 'success') {
                    this.showToast('success', 'Your message has been sent successfully!');
                    this.formData = { name: '', email: '', phone: '', subject: '', message: '' };
                    this.errors = { name: '', email: '', phone: '', subject: '', message: '' };
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('error', error.message || 'Failed to send message');
            } finally {
                this.isSubmitting = false;
            }
        },

        showToast(icon, message) {
            // Validate icon to prevent XSS
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
