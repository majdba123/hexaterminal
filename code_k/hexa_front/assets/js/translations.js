/**
 * i18next Translation System
 * Handles multi-language support (Arabic/English)
 */

(function() {
    'use strict';

    // Initialize i18next
    if (typeof i18next !== 'undefined' && 
        typeof i18nextBrowserLanguageDetector !== 'undefined' && 
        typeof i18nextHttpBackend !== 'undefined') {
        
        // Get base path for translations
        // This ensures translations work whether site is in root or subdirectory
        function getTranslationsPath() {
            // Get the directory of current page
            const pathname = window.location.pathname;
            const lastSlash = pathname.lastIndexOf('/');
            const directory = lastSlash >= 0 ? pathname.substring(0, lastSlash + 1) : '/';
            
            // Return relative path from current directory
            // This works whether site is in root or subdirectory
            return directory + 'translations/{{lng}}.json';
        }
        
        const translationsPath = getTranslationsPath();
        
        i18next
            .use(i18nextBrowserLanguageDetector)
            .use(i18nextHttpBackend)
            .init({
                fallbackLng: 'en',
                debug: false,
                ns: ['translation'],
                defaultNS: 'translation',
                backend: {
                    loadPath: translationsPath,
                    crossDomain: false,
                    allowMultiLoading: false,
                    // Add requestOptions to prevent double URL
                    requestOptions: {
                        cache: 'default'
                    }
                },
                detection: {
                    order: ['localStorage', 'navigator'],
                    caches: ['localStorage']
                },
                interpolation: {
                    escapeValue: false
                }
            }, function(err, t) {
                if (err) {
                    console.error('i18next initialization error:', err);
                } else {
                    // Wait a bit for resources to be fully loaded
                    setTimeout(() => {
                        updateContent();
                        applyLanguageStyles(i18next.language);
                    }, 100);
                }
            });
    }

    /**
     * Update all translatable content on the page
     */
    function updateContent() {
        if (typeof i18next === 'undefined') {
            console.warn('i18next is not available');
            return;
        }

        // Update all elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (key) {
                try {
                    // Check if i18next is initialized
                    if (i18next.isInitialized) {
                        // Try to get translation, even if key doesn't exist yet
                        const translatedText = i18next.t(key);
                        
                        // Only update if translation exists and is different from key
                        if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                            // For option elements, update text content
                            if (el.tagName === 'OPTION') {
                                el.textContent = translatedText;
                            } else {
                                // For other elements, update text content
                                el.textContent = translatedText;
                            }
                            // Ensure element is visible
                            el.style.visibility = 'visible';
                            el.style.opacity = '1';
                            if (el.tagName === 'SPAN') {
                                el.style.display = 'inline-block';
                            }
                        }
                    }
                } catch (error) {
                    // Silently handle errors during translation
                }
            }
        });

        // Update placeholders with data-i18n-placeholder attribute
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (key && i18next.exists(key)) {
                el.placeholder = i18next.t(key);
            }
        });

        // Force update for nav-link spans specifically (including offcanvas)
        document.querySelectorAll('.nav-link span[data-i18n], .mobile-menu .nav-link span[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (key) {
                try {
                    if (i18next.isInitialized) {
                        const translatedText = i18next.t(key);
                        if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                            el.textContent = translatedText;
                            el.style.display = 'inline-block';
                            el.style.visibility = 'visible';
                            el.style.opacity = '1';
                        }
                    }
                } catch (error) {
                    // Silently handle errors
                }
            }
        });

        // Force update for offcanvas content
        document.querySelectorAll('.offcanvas [data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (key) {
                try {
                    if (i18next.isInitialized) {
                        const translatedText = i18next.t(key);
                        if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                            el.textContent = translatedText;
                            el.style.visibility = 'visible';
                            el.style.opacity = '1';
                            if (el.tagName === 'SPAN') {
                                el.style.display = 'inline-block';
                            }
                        }
                    }
                } catch (error) {
                    // Silently handle errors
                }
            }
        });
    }

    /**
     * Apply language-specific styles (RTL/LTR, fonts)
     * @param {string} lng - Language code ('ar' or 'en')
     */
    function applyLanguageStyles(lng) {
        const html = document.documentElement;
        const body = document.body;

        if (!html || !body) {
            return;
        }

        if (lng === 'ar') {
            html.dir = 'rtl';
            html.lang = 'ar';
            body.classList.add('rtl', 'arabic-font');
            body.classList.remove('ltr', 'latin-font');
        } else {
            html.dir = 'ltr';
            html.lang = lng || 'en';
            body.classList.add('ltr', 'latin-font');
            body.classList.remove('rtl', 'arabic-font');
        }

        // Load fonts using WebFont if available
        if (typeof WebFont !== 'undefined') {
            WebFont.load({
                google: {
                    families: lng === 'ar' ? ['Cairo:400,700'] : ['Jost:400,700']
                }
            });
        }
    }

    /**
     * Change application language
     * @param {string} lng - Language code ('ar' or 'en')
     */
    function changeLanguage(lng) {
        if (typeof i18next === 'undefined') {
            console.error('i18next is not loaded');
            return;
        }

        i18next.changeLanguage(lng, (err, t) => {
            if (err) {
                console.error('Error changing language:', err);
                return;
            }
            
            // Apply language styles first
            applyLanguageStyles(lng);
            
            // Force update header and offcanvas translations immediately
            const forceUpdateHeader = () => {
                if (typeof i18next === 'undefined' || !i18next.isInitialized) return;
                
                document.querySelectorAll('#header-container [data-i18n], .offcanvas [data-i18n], .nav-link [data-i18n], .nav-link span[data-i18n], .mobile-menu [data-i18n], .mobile-menu span[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (key) {
                        try {
                            const translatedText = i18next.t(key);
                            if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                                el.textContent = translatedText;
                                el.style.visibility = 'visible';
                                el.style.opacity = '1';
                                if (el.tagName === 'SPAN') {
                                    el.style.display = 'inline-block';
                                }
                            }
                        } catch (error) {
                            // Silently handle errors
                        }
                    }
                });
            };
            
            // Update content with multiple attempts to ensure all elements are updated
            updateContent();
            forceUpdateHeader();
            
            // Force update again after a short delay to catch any dynamically loaded content
            setTimeout(() => {
                updateContent();
                forceUpdateHeader();
            }, 100);
            
            // Another update after components are fully loaded
            setTimeout(() => {
                updateContent();
                forceUpdateHeader();
            }, 300);
            
            // Final update to ensure everything is translated
            setTimeout(() => {
                updateContent();
                forceUpdateHeader();
            }, 600);
            
            localStorage.setItem('selectedLanguage', lng);

            // Trigger custom event for language change
            document.dispatchEvent(new CustomEvent('languageChanged', {
                detail: { language: lng }
            }));

            // Update any additional dynamic components
            if (typeof window.reloadComponents === 'function') {
                window.reloadComponents();
            }
        });
    }

    // Language dropdown handler (if exists)
    const langDropdown = document.querySelector('.custom-language-dropdown');
    const langItems = document.querySelectorAll('.lang-options li');
    const langBtn = document.querySelector('.lang-btn');

    if (langItems.length > 0 && langDropdown) {
        langItems.forEach(item => {
            item.addEventListener('click', () => {
                const selectedLang = item.dataset.lang;
                if (selectedLang) {
                    changeLanguage(selectedLang);
                    langDropdown.classList.remove('active');
                    
                    // Update button text if exists
                    if (langBtn) {
                        langBtn.innerHTML = item.textContent + ' <i class="fa-solid fa-chevron-down"></i>';
                    }
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', e => {
            if (langDropdown && !langDropdown.contains(e.target)) {
                langDropdown.classList.remove('active');
            }
        });
    }

    // Listen for language change events
    document.addEventListener('languageChanged', (event) => {
        setTimeout(() => {
            updateContent();
            // Force update header translations
            if (typeof i18next !== 'undefined' && i18next.isInitialized) {
                document.querySelectorAll('#header-container [data-i18n], .offcanvas [data-i18n], .nav-link [data-i18n], .nav-link span[data-i18n], .mobile-menu [data-i18n], .mobile-menu span[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (key) {
                        try {
                            const translatedText = i18next.t(key);
                            if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                                el.textContent = translatedText;
                                el.style.visibility = 'visible';
                                el.style.opacity = '1';
                                if (el.tagName === 'SPAN') {
                                    el.style.display = 'inline-block';
                                }
                            }
                        } catch (error) {
                            // Silently handle errors
                        }
                    }
                });
            }
        }, 100);
    });

    // Load saved language on page load
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof i18next !== 'undefined') {
            const savedLanguage = localStorage.getItem('selectedLanguage') || 
                                 i18next.options.fallbackLng;
            setTimeout(() => {
                changeLanguage(savedLanguage);
            }, 50);
        }
    });
    
    // Also update when page is fully loaded
    window.addEventListener('load', () => {
        if (typeof i18next !== 'undefined' && typeof updateContent === 'function') {
            setTimeout(() => {
                updateContent();
                // Force update header translations
                if (i18next.isInitialized) {
                    document.querySelectorAll('#header-container [data-i18n], .offcanvas [data-i18n], .nav-link [data-i18n], .nav-link span[data-i18n], .mobile-menu [data-i18n], .mobile-menu span[data-i18n]').forEach(el => {
                        const key = el.getAttribute('data-i18n');
                        if (key) {
                            try {
                                const translatedText = i18next.t(key);
                                if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                                    el.textContent = translatedText;
                                    el.style.visibility = 'visible';
                                    el.style.opacity = '1';
                                    if (el.tagName === 'SPAN') {
                                        el.style.display = 'inline-block';
                                    }
                                }
                            } catch (error) {
                                // Silently handle errors
                            }
                        }
                    });
                }
            }, 200);
        }
    });
    
    // Listen for offcanvas show event to update translations
    document.addEventListener('shown.bs.offcanvas', function(event) {
        // Force update all offcanvas translations when it opens
        setTimeout(() => {
            if (typeof i18next !== 'undefined' && i18next.isInitialized) {
                document.querySelectorAll('.offcanvas [data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (key) {
                        try {
                            const translatedText = i18next.t(key);
                            if (translatedText && translatedText !== key && !translatedText.startsWith('translation:')) {
                                el.textContent = translatedText;
                                el.style.visibility = 'visible';
                                el.style.opacity = '1';
                                if (el.tagName === 'SPAN') {
                                    el.style.display = 'inline-block';
                                }
                            }
                        } catch (error) {
                            console.warn('Error translating offcanvas key:', key, error);
                        }
                    }
                });
            }
        }, 100);
    });
    
    // Also listen for when offcanvas is about to show
    document.addEventListener('show.bs.offcanvas', function(event) {
        if (typeof i18next !== 'undefined' && i18next.isInitialized && typeof updateContent === 'function') {
            setTimeout(() => {
                updateContent();
            }, 100);
        }
    });

    // Export functions globally
    window.changeLanguage = changeLanguage;
    window.updateContent = updateContent;
    window.applyLanguageStyles = applyLanguageStyles;
})();
