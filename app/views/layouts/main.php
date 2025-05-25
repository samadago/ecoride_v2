<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EcoRide - Covoiturage écologique'; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/carousel.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header avec navigation -->
    <header id="main-header">
        <div class="container">
            <div class="logo">
                <img src="/assets/images/logo_eco.png" alt="Logo EcoRide">
                <a href="/">
                    <h1>EcoRide</h1>
                </a>
            </div>
            <nav>
                <ul class="desktop-menu">
                    <li><a href="/" <?php echo ($currentPage ?? '') === 'home' ? 'class="active"' : ''; ?>>Accueil</a></li>
                    <li><a href="/covoiturages" <?php echo ($currentPage ?? '') === 'rides' ? 'class="active"' : ''; ?>>Covoiturages</a></li>
                    <li><a href="/contact" <?php echo ($currentPage ?? '') === 'contact' ? 'class="active"' : ''; ?>>Contact</a></li>
                    <li><a href="/a-propos" <?php echo ($currentPage ?? '') === 'about' ? 'class="active"' : ''; ?>>À propos</a></li>
                </ul>
                <div class="user-menu">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                            <?php if (\App\Helpers\Auth::isAdmin()): ?>
                                <a href="/admin">Administration</a>
                            <?php endif; ?>
                            <a href="/profil">Mon profil</a>
                            <a href="/deconnexion">Déconnexion</a>
                        <?php else: ?>
                            <a href="/connexion" <?php echo ($currentPage ?? '') === 'login' ? 'class="active"' : ''; ?>>Connexion</a>
                            <a href="/inscription" <?php echo ($currentPage ?? '') === 'register' ? 'class="active"' : ''; ?>>Inscription</a>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="mobile-menu-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
        <div class="mobile-menu">
            <ul>
                <li><a href="/" <?php echo ($currentPage ?? '') === 'home' ? 'class="active"' : ''; ?>>Accueil</a></li>
                <li><a href="/covoiturages" <?php echo ($currentPage ?? '') === 'rides' ? 'class="active"' : ''; ?>>Covoiturages</a></li>
                <li><a href="/contact" <?php echo ($currentPage ?? '') === 'contact' ? 'class="active"' : ''; ?>>Contact</a></li>
                <li><a href="/a-propos" <?php echo ($currentPage ?? '') === 'about' ? 'class="active"' : ''; ?>>À propos</a></li>
                <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                    <?php if (\App\Helpers\Auth::isAdmin()): ?>
                        <li><a href="/admin">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="/profil">Mon profil</a></li>
                    <li><a href="/deconnexion">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="/connexion" <?php echo ($currentPage ?? '') === 'login' ? 'class="active"' : ''; ?>>Connexion</a></li>
                    <li><a href="/inscription" <?php echo ($currentPage ?? '') === 'register' ? 'class="active"' : ''; ?>>Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <?php echo $content ?? ''; ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="social">
                <h3>Suivez-nous</h3>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-square"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="contact-info">
                <a href="mailto:contact@ecoride.space">contact@ecoride.space</a>
            </div>
            <div class="legal">
                <a href="/mentions-legales">Mentions légales</a>
                <a href="/a-propos">À propos</a>
                <span id="current-year"><?php echo date('Y'); ?></span>
            </div>
        </div>
    </footer>

    <script src="/assets/js/scripts.js"></script>
    <script src="/assets/js/carousel.js"></script>
</body>
</html>