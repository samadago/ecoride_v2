<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposer un trajet - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/autocomplete.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header avec navigation -->
    <!-- <header id="main-header">
        <div class="container">
            <div class="logo">
                <img src="/assets/images/logo_eco.png" alt="Logo EcoRide">
                <h1>EcoRide</h1>
            </div>
            <nav>
                <ul class="desktop-menu">
                    <li><a href="/">Accueil</a></li>
                    <li><a href="/covoiturages">Covoiturages</a></li>
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
                <li><a href="/covoiturages">Covoiturages</a></li>
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
    </header> -->

    <!-- Formulaire de création de trajet -->
    <section class="create-ride-section">
        <div class="container">
            <h2 class="section-title">Proposer un trajet</h2>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="form-card">
                <form action="/proposer-trajet" method="post" class="create-ride-form">
                    <div class="form-group">
                        <label for="departure_location">Lieu de départ</label>
                        <div class="input-icon">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <input type="text" id="departure_location" name="departure_location" placeholder="Ville, adresse..." value="<?= htmlspecialchars($_POST['departure_location'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="arrival_location">Lieu d'arrivée</label>
                        <div class="input-icon">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <input type="text" id="arrival_location" name="arrival_location" placeholder="Ville, adresse..." value="<?= htmlspecialchars($_POST['arrival_location'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="departure_time">Date et heure de départ</label>
                        <div class="input-icon">
                            <i class="far fa-calendar"></i>
                        </div>
                        <input type="datetime-local" id="departure_time" name="departure_time" value="<?= htmlspecialchars($_POST['departure_time'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="estimated_arrival_time">Date et heure d'arrivée estimée</label>
                        <div class="input-icon">
                            <i class="far fa-clock"></i>
                        </div>
                        <input type="datetime-local" id="estimated_arrival_time" name="estimated_arrival_time" value="<?= htmlspecialchars($_POST['estimated_arrival_time'] ?? '') ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="price">Prix par personne (€)</label>
                            <div class="input-icon">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <input type="number" id="price" name="price" min="0" step="0.01" placeholder="15.00" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group half">
                            <label for="seats_available">Places disponibles</label>
                            <div class="input-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <input type="number" id="seats_available" name="seats_available" min="1" max="8" value="<?= htmlspecialchars($_POST['seats_available'] ?? '3') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vehicle_id">Sélectionnez votre véhicule</label>
                        <div class="input-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <select id="vehicle_id" name="vehicle_id" required>
                            <option value="">Choisissez un véhicule</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= $vehicle['id'] ?>" <?= (isset($_POST['vehicle_id']) && $_POST['vehicle_id'] == $vehicle['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="eco_friendly" name="eco_friendly" <?= isset($_POST['eco_friendly']) ? 'checked' : '' ?>>
                        <label for="eco_friendly">Véhicule écologique (électrique, hybride, etc.)</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Publier le trajet</button>
                </form>
            </div>
        </div>
    </section>


    <script src="/assets/js/scripts.js"></script>
    <script src="/assets/js/city-autocomplete.js"></script>
</body>
</html>