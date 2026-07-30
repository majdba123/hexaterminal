/**
 * Font Loader Utility
 * Ensures fonts are loaded before displaying content to prevent "images without text" issue
 */

(function() {
    'use strict';

    // Font families to check
    const fontsToLoad = [
        'Cairo',
        'Jost',
        'Russo One'
    ];

    /**
     * Check if a font is loaded
     * @param {string} fontFamily - Font family name
     * @returns {Promise<boolean>}
     */
    function checkFontLoaded(fontFamily) {
        return new Promise((resolve) => {
            if (!document.fonts || !document.fonts.check) {
                // Fallback for browsers without Font Loading API
                resolve(true);
                return;
            }

            // Check if font is available
            if (document.fonts.check(`16px "${fontFamily}"`)) {
                resolve(true);
                return;
            }

            // Wait for font to load
            const timeout = setTimeout(() => {
                resolve(false);
            }, 3000); // 3 second timeout

            document.fonts.ready.then(() => {
                clearTimeout(timeout);
                resolve(document.fonts.check(`16px "${fontFamily}"`));
            });
        });
    }

    /**
     * Load all fonts
     * @returns {Promise<void>}
     */
    async function loadFonts() {
        const fontPromises = fontsToLoad.map(font => checkFontLoaded(font));
        await Promise.allSettled(fontPromises);
    }

    /**
     * Show content after fonts are loaded
     */
    async function initializeContent() {
        try {
            // Wait for fonts to load
            await loadFonts();
            
            // Ensure DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showContent);
            } else {
                showContent();
            }
        } catch (error) {
            console.error('Font loading error:', error);
            // Show content anyway after a delay
            setTimeout(showContent, 1000);
        }
    }

    /**
     * Show page content
     */
    function showContent() {
        const page = document.getElementById('page');
        const loadingScreen = document.getElementById('loading-screen');
        
        if (page) {
            page.classList.add('loaded');
            page.style.opacity = '1';
        }
        
        if (loadingScreen) {
            loadingScreen.style.opacity = '0';
            setTimeout(() => {
                if (loadingScreen) {
                    loadingScreen.style.display = 'none';
                }
            }, 500);
        }

        // Trigger font-loaded event
        document.dispatchEvent(new CustomEvent('fonts-loaded'));
    }

    // Initialize when script loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeContent);
    } else {
        initializeContent();
    }

    // Export for use in other scripts
    window.FontLoader = {
        loadFonts: loadFonts,
        checkFontLoaded: checkFontLoaded
    };
})();

