/**
 * Website Bundle — All dependencies loaded locally
 * Zero CDN requests, maximum performance
 */

/* ─── Axios ─── */
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/* ─── AOS (Animate On Scroll) ─── */
import AOS from 'aos';
window.AOS = AOS;

/* ─── Initialize on DOM ready ─── */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize AOS
    AOS.init({
        duration: 700,
        once: true,
        offset: 60,
        easing: 'ease-out-cubic'
    });
});

