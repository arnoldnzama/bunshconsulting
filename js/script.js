// ===================================
// Bureau d'Études - Kinshasa
// JavaScript Principal
// ===================================

// Menu Mobile Toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animation du bouton hamburger
            const spans = menuToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        // Fermer le menu mobile quand on clique sur un lien (sauf dropdown-toggle)
        const navLinks = navMenu.querySelectorAll('a:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Ne fermer que si c'est un vrai lien (pas #)
                if (link.getAttribute('href') !== '#') {
                    navMenu.classList.remove('active');
                    const spans = menuToggle.querySelectorAll('span');
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            });
        });
    }

    // Fermer le menu mobile si on clique en dehors
    document.addEventListener('click', function(event) {
        if (navMenu && menuToggle) {
            if (!navMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                navMenu.classList.remove('active');
                const spans = menuToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        }
    });
});

// ===================================
// Filtrage des projets (page réalisations)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    if (filterButtons.length > 0 && projectCards.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Retirer la classe active de tous les boutons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');

                // Filtrer les projets
                projectCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    
                    if (filter === 'tous' || category === filter) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeIn 0.5s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
});

// ===================================
// Formulaire de contact
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const formMessage = document.getElementById('formMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Récupérer les données du formulaire
            const formData = new FormData(contactForm);

            // Désactiver le bouton de soumission
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Envoi en cours...';

            // Envoyer les données au serveur
            fetch('php/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Réactiver le bouton
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;

                // Afficher le message
                if (formMessage) {
                    formMessage.style.display = 'block';
                    
                    if (data.success) {
                        formMessage.style.backgroundColor = '#d4edda';
                        formMessage.style.color = '#155724';
                        formMessage.style.border = '1px solid #c3e6cb';
                        formMessage.textContent = data.message || 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.';
                        
                        // Réinitialiser le formulaire
                        contactForm.reset();
                        
                        // Masquer le message après 5 secondes
                        setTimeout(() => {
                            formMessage.style.display = 'none';
                        }, 5000);
                    } else {
                        formMessage.style.backgroundColor = '#f8d7da';
                        formMessage.style.color = '#721c24';
                        formMessage.style.border = '1px solid #f5c6cb';
                        formMessage.textContent = data.message || 'Une erreur est survenue. Veuillez réessayer.';
                    }
                }
            })
            .catch(error => {
                // Réactiver le bouton
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;

                // Afficher un message d'erreur
                if (formMessage) {
                    formMessage.style.display = 'block';
                    formMessage.style.backgroundColor = '#f8d7da';
                    formMessage.style.color = '#721c24';
                    formMessage.style.border = '1px solid #f5c6cb';
                    formMessage.textContent = 'Une erreur est survenue. Veuillez réessayer ou nous contacter directement par email.';
                }
                
                console.error('Erreur:', error);
            });
        });
    }
});

// ===================================
// Animation au scroll (fade in)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer tous les éléments avec la classe fade-in
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(element);
    });
});

// ===================================
// Smooth scroll pour les ancres
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Ignorer les liens vides ou juste "#"
            if (href === '#' || href === '') {
                e.preventDefault();
                return;
            }

            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// ===================================
// Header sticky avec ombre au scroll
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
            } else {
                header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            }
        });
    }
});

// ===================================
// Animation des chiffres (Stats)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    function animateNumber(element) {
        const target = element.textContent;
        const isPlus = target.includes('+');
        const isPercent = target.includes('%');
        const number = parseInt(target.replace(/\D/g, ''));
        
        let current = 0;
        const increment = number / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= number) {
                current = number;
                clearInterval(timer);
            }
            
            let displayValue = Math.floor(current);
            if (isPlus) displayValue += '+';
            if (isPercent) displayValue += '%';
            
            element.textContent = displayValue;
        }, 30);
    }
    
    if (statNumbers.length > 0) {
        const observerOptions = {
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.animated) {
                    animateNumber(entry.target);
                    entry.target.dataset.animated = 'true';
                }
            });
        }, observerOptions);
        
        statNumbers.forEach(stat => observer.observe(stat));
    }
});

// ===================================
// Validation du formulaire de newsletter
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForms = document.querySelectorAll('form[action*="newsletter"]');
    
    newsletterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (emailInput && emailInput.value) {
                // Animation de confirmation
                submitButton.textContent = '✓ Inscrit !';
                submitButton.style.backgroundColor = '#28a745';
                
                // Réinitialiser après 2 secondes
                setTimeout(() => {
                    submitButton.textContent = "S'inscrire";
                    submitButton.style.backgroundColor = '';
                    emailInput.value = '';
                }, 2000);
            }
        });
    });
});

// ===================================
// Scroll to top button
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    if (scrollToTopBtn) {
        // Afficher/masquer le bouton selon la position de scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.remove('hidden');
            } else {
                scrollToTopBtn.classList.add('hidden');
            }
        });
        
        // Fonction de retour en haut
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// ===================================
// Menu déroulant (Dropdown)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        
        if (toggle) {
            // Empêcher le comportement par défaut du lien #
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Empêcher la propagation de l'événement
                
                // Toggle au clic (mobile et desktop)
                const isActive = dropdown.classList.contains('active');
                
                // Fermer les autres dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('active');
                    }
                });
                
                // Toggle le dropdown actuel
                if (isActive) {
                    dropdown.classList.remove('active');
                } else {
                    dropdown.classList.add('active');
                }
            });
        }
    });
    
    // Fermer les dropdowns si on clique en dehors
    document.addEventListener('click', function(event) {
        // Vérifier si le clic est en dehors de tous les dropdowns
        let clickedInsideDropdown = false;
        dropdowns.forEach(dropdown => {
            if (dropdown.contains(event.target)) {
                clickedInsideDropdown = true;
            }
        });
        
        if (!clickedInsideDropdown) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});

// ===================================
// Impression console (informations développeur)
// ===================================
console.log('%c Bureau d\'Études - Kinshasa ', 'background: #0b2294; color: white; font-size: 16px; padding: 10px;');
console.log('%c Mesurer aujourd\'hui pour transformer demain ', 'color: #0b2294; font-size: 14px;');
console.log('%c Site développé avec ❤️ pour l\'évaluation d\'impact ', 'color: #666; font-size: 12px;');

