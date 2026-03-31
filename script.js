// Main website functionality
class WebsiteManager {
    constructor() {
        this.currentLang = localStorage.getItem('language') || 'cs';
        this.init();
    }

    async init() {
        this.setupNavigation();
        this.setupLanguageSwitcher();
        this.setupAnimations();
        this.setupCookieBanner();
        this.setupErrorHandling();
        this.applyLanguage(this.currentLang);
    }

    setupNavigation() {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        if (hamburger && navMenu) {
            hamburger.addEventListener('click', () => {
                const isExpanded = hamburger.getAttribute('aria-expanded') === 'true';
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
                hamburger.setAttribute('aria-expanded', !isExpanded);
            });

            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    hamburger.setAttribute('aria-expanded', 'false');
                });
            });

            document.addEventListener('click', (e) => {
                if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                    navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.15)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                    navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
                }
            }
        });
    }

    setupLanguageSwitcher() {
        const langToggle = document.getElementById('lang-toggle');
        const langText = document.querySelector('.lang-text');

        if (langToggle && langText) {
            langText.textContent = this.currentLang === 'cs' ? 'EN' : 'CZ';

            langToggle.addEventListener('click', () => {
                this.currentLang = this.currentLang === 'cs' ? 'en' : 'cs';
                this.applyLanguage(this.currentLang);
                localStorage.setItem('language', this.currentLang);
                langText.textContent = this.currentLang === 'cs' ? 'EN' : 'CZ';
            });
        }
    }

    applyLanguage(lang) {
        document.documentElement.lang = lang;

        const elements = document.querySelectorAll('[data-cs][data-en]');
        elements.forEach(element => {
            const text = element.getAttribute(`data-${lang}`);
            if (text) {
                if (element.tagName === 'INPUT' && element.type === 'submit') {
                    element.value = text;
                } else if (element.tagName === 'INPUT' && element.placeholder !== undefined) {
                    element.placeholder = text;
                } else {
                    element.innerHTML = text;
                }
            }
        });

        const titleMap = {
            'index.html': { cs: 'Nech mě růst - Domů', en: 'Let Me Grow - Home' },
            'zvireci-obyvatele.html': { cs: 'Nech mě růst - Zvířecí obyvatelé', en: 'Let Me Grow - Animal Residents' },
            'virtualni-adopce.html': { cs: 'Nech mě růst - Virtuální adopce', en: 'Let Me Grow - Virtual Adoption' },
            'udalosti.html': { cs: 'Nech mě růst - Události', en: 'Let Me Grow - Events' },
            'kontakt.html': { cs: 'Nech mě růst - Kontakt', en: 'Let Me Grow - Contact' },
            'prispet-kryptem.html': { cs: 'Nech mě růst - Přispět kryptem', en: 'Let Me Grow - Donate with Crypto' },
            'o-nas.html': { cs: 'Nech mě růst - O nás' , en: 'Let Me Grow - About Us' },
            'obchod/index.html': { cs: 'Nech mě růst - Obchod', en: 'Let Me Grow - Shop' },
            'putovani-se-zviraty.html': { cs: 'Nech mě růst - Putování se zvířaty', en: 'Let Me Grow - Journey with Animals' }
        };

        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        if (titleMap[currentPage]) {
            document.title = titleMap[currentPage][lang];
        }
    }

    setupCookieBanner() {
        const banner = document.getElementById('cookie-banner');
        const acceptBtn = document.getElementById('accept-cookies');
        const rejectBtn = document.getElementById('reject-cookies');

        if (!banner || !acceptBtn || !rejectBtn) return;

        const cookieChoice = localStorage.getItem('cookieConsent');

        if (!cookieChoice) {
            setTimeout(() => {
                banner.classList.remove('hidden');
            }, 1000);
        }

        const handleChoice = (choice) => {
            localStorage.setItem('cookieConsent', choice);
            banner.classList.add('hidden');

            if (choice === 'accepted') {
                this.loadAnalytics();
            }
        };

        acceptBtn.addEventListener('click', () => handleChoice('accepted'));
        rejectBtn.addEventListener('click', () => handleChoice('rejected'));
    }

    loadAnalytics() {
        // Load Google Analytics only after consent
        if (window.dataLayer) {
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=G-J1EYDZ6G3W';
            document.head.appendChild(script);

            function gtag(){window.dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-J1EYDZ6G3W');
        }
    }

    setupErrorHandling() {
        // Global error handler for uncaught errors
        window.addEventListener('error', (e) => {
            console.error('Global error:', e.error);
        });

        // Handle failed API calls
        window.addEventListener('unhandledrejection', (e) => {
            console.error('Unhandled promise rejection:', e.reason);
        });
    }

    setupAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const animatedElements = document.querySelectorAll('.value-card, .animal-card, .event-card, .type-card, .contact-card, .step, .journey-info-card');
        animatedElements.forEach(el => {
            el.classList.add('fade-in');
            observer.observe(el);
        });

        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const heroImages = document.querySelectorAll('.hero-bg');
            heroImages.forEach(img => {
                img.style.transform = `translateY(${scrolled * 0.5}px)`;
            });
        });

        const cards = document.querySelectorAll('.value-card, .animal-card, .event-card, .type-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px) scale(1.02)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    }
}

// API Helper class with error handling and loading states
class ApiClient {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
    }

    /**
     * Show loading state for an element
     */
    showLoading(elementId, message = 'Načítání...') {
        const element = document.getElementById(elementId);
        if (element) {
            element.dataset.originalContent = element.innerHTML;
            element.innerHTML = `<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> ${message}</div>`;
            element.disabled = true;
        }
    }

    /**
     * Hide loading state
     */
    hideLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element && element.dataset.originalContent) {
            element.innerHTML = element.dataset.originalContent;
            element.disabled = false;
            delete element.dataset.originalContent;
        }
    }

    /**
     * Show error message
     */
    showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-circle"></i> ${message}</div>`;
        }
    }

    /**
     * Make API request with error handling
     */
    async request(endpoint, options = {}) {
        const url = this.baseUrl + endpoint;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    /**
     * POST request
     */
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }
}

// Form validation helper
class FormValidator {
    static email(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    static phone(phone) {
        return /^[\d\s\+\-\(\)]{9,}$/.test(phone);
    }

    static required(value) {
        return value && value.trim().length > 0;
    }

    static minLength(value, min) {
        return value && value.length >= min;
    }

    /**
     * Validate form and show inline errors
     */
    static validateForm(formId, rules) {
        const form = document.getElementById(formId);
        if (!form) return true;

        let isValid = true;
        const errors = {};

        for (const [fieldName, fieldRules] of Object.entries(rules)) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;

            const value = field.value;
            let error = null;

            if (fieldRules.required && !this.required(value)) {
                error = fieldRules.required;
            } else if (fieldRules.email && !this.email(value)) {
                error = fieldRules.email;
            } else if (fieldRules.phone && !this.phone(value)) {
                error = fieldRules.phone;
            } else if (fieldRules.minLength && !this.minLength(value, fieldRules.minLength)) {
                error = fieldRules.minLength;
            }

            if (error) {
                isValid = false;
                errors[fieldName] = error;
                field.classList.add('error');
                field.setAttribute('aria-invalid', 'true');
            } else {
                field.classList.remove('error');
                field.removeAttribute('aria-invalid');
            }
        }

        // Display errors
        for (const [fieldName, errorMessage] of Object.entries(errors)) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            let errorEl = field.parentElement.querySelector('.field-error');

            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'field-error';
                field.parentElement.appendChild(errorEl);
            }

            errorEl.textContent = errorMessage;
        }

        // Clear errors for valid fields
        const validFields = form.querySelectorAll('[name]:not(.error)');
        validFields.forEach(field => {
            const errorEl = field.parentElement.querySelector('.field-error');
            if (errorEl) errorEl.remove();
        });

        return isValid;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const websiteManager = new WebsiteManager();

    // Lazy load images with IntersectionObserver
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
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

        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }

    // Add dynamic styles
    const style = document.createElement('style');
    style.textContent = `
        .hamburger.active .bar:nth-child(2) { opacity: 0; }
        .hamburger.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .hamburger.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        .fade-in { opacity: 0; transform: translateY(30px); transition: all 0.6s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        .loading-spinner { color: #2d5a3d; padding: 20px; text-align: center; }
        .error-message { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .field-error { color: #dc3545; font-size: 0.875rem; margin-top: 4px; }
        input.error, textarea.error { border-color: #dc3545 !important; }
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #2d5a3d;
            color: white;
            padding: 8px 16px;
            z-index: 10000;
            transition: top 0.3s;
        }
        .skip-link:focus {
            top: 0;
        }
    `;
    document.head.appendChild(style);

    // Initialize year dynamically in footer
    document.querySelectorAll('.current-year').forEach(el => {
        el.textContent = new Date().getFullYear();
    });
});

// Service Worker registration with version control
const SW_VERSION = '1.0.0';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js', { scope: '/' })
            .then(registration => {
                console.log('SW registered:', registration.scope);

                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('New content available, refresh to update.');
                        }
                    });
                });
            })
            .catch(registrationError => {
                console.log('SW registration failed:', registrationError);
            });
    });
}

// Export for use in other scripts
window.WebsiteManager = WebsiteManager;
window.ApiClient = ApiClient;
window.FormValidator = FormValidator;