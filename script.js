// Main website functionality
class WebsiteManager {
    constructor() {
        this.currentLang = localStorage.getItem('language') || 'cs';
        this.gaInitialized = false;
        this.init();
    }

    init() {
        this.setupNavigation();
        this.setupLanguageSwitcher();
        this.setupAnimations();
        this.setupCookieBanner();
        this.applyLanguage(this.currentLang);
        this.updateCurrentYear();
        this.initGAConsent();
    }

    // Google Analytics GDPR Consent Management
    initGAConsent() {
        const consent = localStorage.getItem('cookieConsent');
        if (consent === 'accepted' && !this.gaInitialized) {
            this.loadGoogleAnalytics();
        }
    }

    loadGoogleAnalytics() {
        if (this.gaInitialized) return;

        const gaId = 'G-J1EYDZ6G3W';

        // Načíst gtag script
        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
        document.head.appendChild(script);

        // Inicializace dataLayer
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', gaId, {
            'anonymize_ip': true,
            'storage': 'none', // Neukládat cookies dokud nemáme souhlas
            'ad_storage': 'denied',
            'analytics_storage': 'granted'
        });

        this.gaInitialized = true;
    }

    updateCurrentYear() {
        document.querySelectorAll('.current-year').forEach(el => {
            el.textContent = new Date().getFullYear();
        });
    }

    setupNavigation() {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        if (hamburger && navMenu) {
            // Remove any existing listeners by cloning
            const newHamburger = hamburger.cloneNode(true);
            hamburger.parentNode.replaceChild(newHamburger, hamburger);

            newHamburger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isActive = newHamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
                newHamburger.setAttribute('aria-expanded', isActive);
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!newHamburger.contains(e.target) && !navMenu.contains(e.target)) {
                    newHamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    newHamburger.setAttribute('aria-expanded', 'false');
                }
            });

            // Close menu when clicking on a link
            const navLinks = navMenu.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    newHamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    newHamburger.setAttribute('aria-expanded', 'false');
                });
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }

    setupLanguageSwitcher() {
        const langToggle = document.getElementById('lang-toggle');
        if (langToggle) {
            const langText = langToggle.querySelector('.lang-text');
            
            const updateToggleText = (lang) => {
                if (langText) langText.textContent = lang === 'cs' ? 'EN' : 'CZ';
            };

            updateToggleText(this.currentLang);

            langToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.currentLang = this.currentLang === 'cs' ? 'en' : 'cs';
                localStorage.setItem('language', this.currentLang);
                this.applyLanguage(this.currentLang);
                updateToggleText(this.currentLang);
            });
        }
    }

    applyLanguage(lang) {
        document.documentElement.lang = lang;
        const elements = document.querySelectorAll('[data-cs][data-en]');
        elements.forEach(element => {
            const text = element.getAttribute(`data-${lang}`);
            if (text) {
                if (element.tagName === 'INPUT' && (element.type === 'submit' || element.type === 'button')) {
                    element.value = text;
                } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = text;
                } else {
                    element.innerHTML = text;
                }
            }
        });

        this.updatePageTitle(lang);
    }

    updatePageTitle(lang) {
        const titleMap = {
            'index': { cs: 'Nech mě růst - Domů', en: 'Let Me Grow - Home' },
            'o-nas': { cs: 'Nech mě růst - O nás', en: 'Let Me Grow - About Us' },
            'landing': { cs: 'Nech mě růst - Rozcestník', en: 'Let Me Grow - Navigation' },
            'jak-se-zapojit': { cs: 'Nech mě růst - Jak se zapojit', en: 'Let Me Grow - Get Involved' },
            'novinky': { cs: 'Nech mě růst - Novinky', en: 'Let Me Grow - News' },
            'zvireci-obyvatele': { cs: 'Nech mě růst - Zvířecí obyvatelé', en: 'Let Me Grow - Animal Residents' },
            'udalosti': { cs: 'Nech mě růst - Události', en: 'Let Me Grow - Events' },
            'kontakt': { cs: 'Nech mě růst - Kontakt', en: 'Let Me Grow - Contact' },
            'galerie': { cs: 'Nech mě růst - Galerie', en: 'Let Me Grow - Gallery' }
        };

        let path = window.location.pathname;
        let page = path.split('/').pop().replace('.html', '') || 'index';
        
        if (titleMap[page]) {
            document.title = titleMap[page][lang];
        }
    }

    setupAnimations() {
        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        const animatedElements = document.querySelectorAll('.card, .value-card, .animal-card, .event-card, .team-card');
        animatedElements.forEach(el => {
            el.classList.add('fade-in');
            observer.observe(el);
        });
    }

    setupCookieBanner() {
        const banner = document.getElementById('cookie-banner');
        const acceptBtn = document.getElementById('accept-cookies');
        const rejectBtn = document.getElementById('reject-cookies');

        if (!banner || !acceptBtn || !rejectBtn) return;

        // Kontrola stávajícího souhlasu
        const existingConsent = localStorage.getItem('cookieConsent');
        if (!existingConsent) {
            setTimeout(() => banner.classList.remove('hidden'), 1000);
        }

        const handleChoice = (choice) => {
            localStorage.setItem('cookieConsent', choice);
            banner.classList.add('hidden');

            // Načíst GA pouze po souhlasu
            if (choice === 'accepted') {
                this.loadGoogleAnalytics();
            }
        };

        acceptBtn.addEventListener('click', () => handleChoice('accepted'));
        rejectBtn.addEventListener('click', () => handleChoice('rejected'));
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    window.websiteManager = new WebsiteManager();

    // Lazy load images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });
        document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
    }
});
