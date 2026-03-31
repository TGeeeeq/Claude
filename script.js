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
                    // ZDE JE TA DŮLEŽITÁ ZMĚNA: innerHTML místo textContent
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

        // Check if user has already made a choice
        const cookieChoice = localStorage.getItem('cookieConsent');
        
        if (!cookieChoice) {
            // Show banner with a slight delay
            setTimeout(() => {
                banner.classList.remove('hidden');
            }, 1000);
        }

        const handleChoice = (choice) => {
            localStorage.setItem('cookieConsent', choice);
            banner.classList.add('hidden');
            
            // Here you can trigger other functionalities based on choice
            if (choice === 'accepted') {
                console.log('Cookies accepted');
                // Initialize tracking scripts if any
            }
        };

        acceptBtn.addEventListener('click', () => handleChoice('accepted'));
        rejectBtn.addEventListener('click', () => handleChoice('rejected'));
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const websiteManager = new WebsiteManager();
    
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
    
    const style = document.createElement('style');
    style.textContent = `
        .hamburger.active .bar:nth-child(2) { opacity: 0; }
        .hamburger.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .hamburger.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
        .fade-in { opacity: 0; transform: translateY(30px); transition: all 0.6s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
    `;
    document.head.appendChild(style);
});

// Service Worker for offline functionality
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}