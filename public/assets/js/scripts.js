// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    // Année courante dans le footer
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }

    // Menu mobile
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            
            // Animation des barres du menu burger
            const spans = this.querySelectorAll('span');
            if (mobileMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }

    // Header sticky avec animation au scroll
    const header = document.getElementById('main-header');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scroll vers le bas
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scroll vers le haut
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });

    // Validation du formulaire de recherche
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        const searchBtn = document.getElementById('search-btn');
        
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const depart = document.getElementById('depart').value;
            const arrivee = document.getElementById('arrivee').value;
            const date = document.getElementById('date').value;
            
            if (!depart || !arrivee || !date) {
                alert('Veuillez remplir tous les champs du formulaire.');
                return;
            }
            
            // Simulation d'envoi de formulaire (à remplacer par une redirection réelle)
            window.location.href = `covoiturages?departure=${encodeURIComponent(depart)}&arrival=${encodeURIComponent(arrivee)}&date=${encodeURIComponent(date)}`;
        });
    }

    // Validation du formulaire de connexion
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Validation email
            const email = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            
            if (!email.value) {
                emailError.textContent = 'Veuillez entrer votre adresse email.';
                emailError.style.display = 'block';
                isValid = false;
            } else if (!isValidEmail(email.value)) {
                emailError.textContent = 'Veuillez entrer une adresse email valide.';
                emailError.style.display = 'block';
                isValid = false;
            } else {
                emailError.style.display = 'none';
            }
            
            // Validation mot de passe
            const password = document.getElementById('password');
            const passwordError = document.getElementById('password-error');
            
            if (!password.value) {
                passwordError.textContent = 'Veuillez entrer votre mot de passe.';
                passwordError.style.display = 'block';
                isValid = false;
            } else {
                passwordError.style.display = 'none';
            }
            
            if (isValid) {
                // Simulation de connexion réussie
                alert('Connexion réussie !');
                // Redirection vers la page d'accueil (à décommenter en production)
                // window.location.href = 'index.html';
            }
        });
    }

    // Validation du formulaire d'inscription
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        const password = document.getElementById('password');
        const strengthMeter = document.querySelector('.strength-meter span');
        const strengthText = document.querySelector('.strength-text');
        
        // Évaluation de la force du mot de passe en temps réel
        if (password) {
            password.addEventListener('input', function() {
                const strength = evaluatePasswordStrength(this.value);
                
                // Mise à jour de la barre de progression
                strengthMeter.style.width = `${strength.score * 25}%`;
                strengthMeter.style.backgroundColor = strength.color;
                strengthText.textContent = `Force du mot de passe: ${strength.label}`;
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Validation email
            const email = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            
            if (!email.value) {
                emailError.textContent = 'Veuillez entrer votre adresse email.';
                emailError.style.display = 'block';
                isValid = false;
            } else if (!isValidEmail(email.value)) {
                emailError.textContent = 'Veuillez entrer une adresse email valide.';
                emailError.style.display = 'block';
                isValid = false;
            } else {
                emailError.style.display = 'none';
            }
            
            // Validation pseudo
            const pseudo = document.getElementById('pseudo');
            const pseudoError = document.getElementById('pseudo-error');
            
            if (!pseudo.value) {
                pseudoError.textContent = 'Veuillez entrer un pseudo.';
                pseudoError.style.display = 'block';
                isValid = false;
            } else if (pseudo.value.length < 3) {
                pseudoError.textContent = 'Votre pseudo doit contenir au moins 3 caractères.';
                pseudoError.style.display = 'block';
                isValid = false;
            } else {
                pseudoError.style.display = 'none';
            }
            
            // Validation mot de passe
            const passwordError = document.getElementById('password-error');
            
            if (!password.value) {
                passwordError.textContent = 'Veuillez entrer un mot de passe.';
                passwordError.style.display = 'block';
                isValid = false;
            } else if (password.value.length < 8) {
                passwordError.textContent = 'Votre mot de passe doit contenir au moins 8 caractères.';
                passwordError.style.display = 'block';
                isValid = false;
            } else {
                passwordError.style.display = 'none';
            }
            
            // Validation confirmation mot de passe
            const confirmPassword = document.getElementById('confirm-password');
            const confirmPasswordError = document.getElementById('confirm-password-error');
            
            if (!confirmPassword.value) {
                confirmPasswordError.textContent = 'Veuillez confirmer votre mot de passe.';
                confirmPasswordError.style.display = 'block';
                isValid = false;
            } else if (confirmPassword.value !== password.value) {
                confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
                confirmPasswordError.style.display = 'block';
                isValid = false;
            } else {
                confirmPasswordError.style.display = 'none';
            }
            
            // Validation conditions générales
            const terms = document.getElementById('terms');
            const termsError = document.getElementById('terms-error');
            
            if (!terms.checked) {
                termsError.textContent = 'Vous devez accepter les conditions générales.';
                termsError.style.display = 'block';
                isValid = false;
            } else {
                termsError.style.display = 'none';
            }
            
            if (isValid) {
                // Simulation d'inscription réussie
                alert('Inscription réussie !');
                // Redirection vers la page de connexion (à décommenter en production)
                // window.location.href = 'connexion.html';
            }
        });
    }

    // Filtres interactifs pour les résultats de covoiturage
    const priceRange = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    const durationRange = document.getElementById('duration-range');
    const durationValue = document.getElementById('duration-value');
    const starFilter = document.querySelectorAll('.star-filter i');
    const ecoFriendly = document.getElementById('eco-friendly');
    const applyFilters = document.getElementById('apply-filters');
    const rideCards = document.querySelectorAll('.ride-card');
    
    // Mise à jour des valeurs des filtres
    if (priceRange && priceValue) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = `${this.value}€`;
        });
    }
    
    if (durationRange && durationValue) {
        durationRange.addEventListener('input', function() {
            durationValue.textContent = `${this.value}h`;
        });
    }
    
    // Système d'étoiles interactif
    if (starFilter.length > 0) {
        starFilter.forEach(star => {
            star.addEventListener('mouseover', function() {
                const value = this.getAttribute('data-value');
                
                starFilter.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
            
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                
                starFilter.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                        s.classList.add('selected');
                    } else {
                        s.classList.remove('active');
                        s.classList.remove('selected');
                    }
                });
            });
        });
        
        document.querySelector('.star-filter').addEventListener('mouseout', function() {
            starFilter.forEach(s => {
                if (!s.classList.contains('selected')) {
                    s.classList.remove('active');
                }
            });
        });
    }
    
    // Application des filtres
    if (applyFilters) {
        applyFilters.addEventListener('click', function() {
            // Récupération des valeurs des filtres
            const maxPrice = priceRange ? parseInt(priceRange.value) : 100;
            const maxDuration = durationRange ? parseInt(durationRange.value) : 10;
            const minRating = document.querySelector('.star-filter i.selected') ? 
                parseInt(document.querySelector('.star-filter i.selected').getAttribute('data-value')) : 0;
            const isEcoFriendly = ecoFriendly ? ecoFriendly.checked : false;
            
            // Filtrage des résultats (simulation côté client)
            rideCards.forEach(card => {
                const price = parseInt(card.querySelector('.price').textContent);
                
                // Extraction de la durée (format: "4h30")
                const durationText = card.querySelector('.info-item:nth-child(2) span').textContent;
                const duration = parseInt(durationText.split('h')[0]);
                
                // Extraction de la note (format: "(4.5)")
                const ratingText = card.querySelector('.rating span').textContent;
                const rating = parseFloat(ratingText.replace('(', '').replace(')', ''));
                
                // Vérification si le trajet est écologique
                const isEco = card.querySelector('.eco-friendly') !== null;
                
                // Application des filtres
                if (price <= maxPrice && 
                    duration <= maxDuration && 
                    rating >= minRating && 
                    (!isEcoFriendly || isEco)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Animation d'apparition des éléments au scroll
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.benefit-card, .destination-card, .ride-card, .detail-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Initialisation des animations
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);

    // Fonctions utilitaires
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function evaluatePasswordStrength(password) {
        // Critères de force du mot de passe
        const hasLowerCase = /[a-z]/.test(password);
        const hasUpperCase = /[A-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const isLongEnough = password.length >= 8;
        
        // Calcul du score
        let score = 0;
        if (hasLowerCase) score++;
        if (hasUpperCase) score++;
        if (hasNumber) score++;
        if (hasSpecialChar) score++;
        if (isLongEnough) score++;
        
        // Détermination du niveau et de la couleur
        let label, color;
        
        switch (score) {
            case 0:
            case 1:
                label = 'Très faible';
                color = '#e74c3c';
                break;
            case 2:
                label = 'Faible';
                color = '#e67e22';
                break;
            case 3:
                label = 'Moyen';
                color = '#f1c40f';
                break;
            case 4:
                label = 'Fort';
                color = '#2ecc71';
                break;
            case 5:
                label = 'Très fort';
                color = '#27ae60';
                break;
        }
        
        return { score, label, color };
    }
});


// Profile Tab Navigation
const initProfileTabs = () => {
    const navItems = document.querySelectorAll('.profile-nav .nav-item');
    const tabContents = document.querySelectorAll('.tab-content');

    if (navItems.length && tabContents.length) {
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                // Remove active class from all nav items and tabs
                navItems.forEach(nav => nav.classList.remove('active'));
                tabContents.forEach(tab => tab.classList.remove('active'));

                // Add active class to clicked nav item
                item.classList.add('active');

                // Show corresponding tab content
                const tabId = item.getAttribute('data-tab');
                const tabContent = document.getElementById(tabId);
                if (tabContent) {
                    tabContent.classList.add('active');
                }
            });
        });
    }
};

// Initialize profile tabs when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initProfileTabs();
    // ... existing DOMContentLoaded code ...
});