<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header avec navigation -->
    <header id="main-header">
        <div class="container">
            <div class="logo">
                <img src="/assets/images/logo_eco.png" alt="Logo EcoRide">
                <h1>EcoRide</h1>
            </div>
            <nav>
                <ul class="desktop-menu">
                    <li><a href="/">Accueil</a></li>
                    <li><a href="/covoiturages" class="active">Covoiturages</a></li>
                    <li><a href="/contact">Contact</a></li>
                </ul>
                <div class="user-menu">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                            <a href="/profil">Mon profil</a>
                            <a href="/deconnexion">Déconnexion</a>
                        <?php else: ?>
                            <a href="/connexion">Connexion</a>
                            <a href="/inscription">Inscription</a>
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
                <li><a href="/">Accueil</a></li>
                <li><a href="/covoiturages" class="active">Covoiturages</a></li>
                <li><a href="/contact">Contact</a></li>
                <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                    <li><a href="/profil">Mon profil</a></li>
                    <li><a href="/deconnexion">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="/connexion">Connexion</a></li>
                    <li><a href="/inscription">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- Barre de recherche compacte -->
    <section class="compact-search">
        <div class="container">
            <form class="search-form-compact" action="/covoiturages" method="get">
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <input type="text" name="departure" id="depart-compact" placeholder="Départ" value="<?= htmlspecialchars($departure) ?>" required>
                </div>
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-map-marker"></i>
                    </div>
                    <input type="text" name="arrival" id="arrivee-compact" placeholder="Arrivée" value="<?= htmlspecialchars($arrival) ?>" required>
                </div>
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <input type="date" name="date" id="date-compact" value="<?= htmlspecialchars($date) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-compact">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
        </div>
    </section>

    <!-- Résultats de recherche -->
    <section class="results-section">
        <div class="container">
            <div class="search-summary">
                <h2>Résultats de recherche</h2>
                <p>Trajets de <strong><?= htmlspecialchars($departure) ?></strong> à <strong><?= htmlspecialchars($arrival) ?></strong> le <strong><?= date('d/m/Y', strtotime($date)) ?></strong></p>
            </div>
            
            <div class="results-container">
                <!-- Filtres -->
                <div class="filters">
                    <h3>Filtres</h3>
                    <div class="filter-group">
                        <label for="price-range">Prix</label>
                        <input type="range" id="price-range" min="0" max="100" value="50">
                        <div class="range-values">
                            <span>0€</span>
                            <span id="price-value">50€</span>
                            <span>100€</span>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="eco-friendly">Véhicule écologique</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="eco-friendly">
                            <label for="eco-friendly"></label>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des résultats -->
                <div class="results-list">
                    <?php if (empty($rides)): ?>
                        <div class="no-results">
                            <p>Aucun trajet disponible pour cette recherche.</p>
                            <a href="/proposer-trajet" class="btn btn-secondary">Proposer ce trajet</a>
                        </div>
                    <?php else: ?>
                        <div class="results-header">
                            <h3><?= count($rides) ?> trajet(s) trouvé(s)</h3>
                            <div class="sort-options">
                                <label for="sort-by">Trier par:</label>
                                <select id="sort-by">
                                    <option value="price">Prix</option>
                                    <option value="date">Date</option>
                                    <option value="duration">Durée</option>
                                </select>
                            </div>
                        </div>
                        
                        <?php foreach ($rides as $ride): ?>
                            <div class="ride-card">
                                <div class="ride-info">
                                    <div class="ride-route">
                                        <div class="ride-time">
                                            <?= date('H:i', strtotime($ride['departure_time'])) ?>
                                        </div>
                                        <div class="ride-locations">
                                            <div class="departure">
                                                <i class="fas fa-circle"></i>
                                                <span><?= htmlspecialchars($ride['departure_location']) ?></span>
                                            </div>
                                            <div class="arrival">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?= htmlspecialchars($ride['arrival_location']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ride-details">
                                        <div class="driver">
                                            <i class="fas fa-user"></i>
                                            <div class="rating">
                                                <i class="fas fa-star"></i>
                                                <span><?= number_format($ride['driver_rating'], 1) ?></span>
                                                <span class="rating-count">(<?= $ride['rating_count'] ?> ratings)</span>
                                            </div>
                                            <span><?= htmlspecialchars($ride['driver_name']) ?></span>
                                        </div>
                                        <div class="seats">
                                            <i class="fas fa-users"></i>
                                            <span><?= (int)$ride['seats_available'] ?> place(s)</span>
                                        </div>
                                        <?php if ($ride['eco_friendly']): ?>
                                            <div class="eco-badge">
                                                <i class="fas fa-leaf"></i>
                                                <span>Éco</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ride-price-action">
                                    <div class="price"><?= number_format($ride['price'], 2) ?>€</div>
                                    <a href="/detail-covoiturage?id=<?= $ride['id'] ?>" class="btn btn-secondary">Voir</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <!-- <footer>
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
            </div>
        </div>
    </footer> -->

    <script src="/assets/js/scripts.js"></script>
</body>
</html>