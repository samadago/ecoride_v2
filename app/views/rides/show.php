<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du covoiturage - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>


    <!-- Détail du covoiturage -->
    <section class="ride-detail">
        <div class="container">
            <div class="back-link">
                <a href="/covoiturages"><i class="fas fa-arrow-left"></i> Retour aux résultats</a>
            </div>
            
            <div class="detail-card">
                <div class="detail-header">
                    <div class="route-summary">
                        <h2><?= htmlspecialchars($ride['departure_location']) ?> → <?= htmlspecialchars($ride['arrival_location']) ?></h2>
                        <div class="route-date">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= date('d F Y', strtotime($ride['departure_time'])) ?></span>
                        </div>
                    </div>
                    <div class="price-tag">
                        <span><?= number_format($ride['price'], 2) ?>€</span>
                        <span class="per-person">par personne</span>
                    </div>
                </div>
                
                <div class="detail-content">
                    <div class="detail-left">
                        <div class="route-details">
                            <div class="route-point departure">
                                <div class="time"><?= date('H:i', strtotime($ride['departure_time'])) ?></div>
                                <div class="point-marker">
                                    <i class="fas fa-circle"></i>
                                    <div class="line"></div>
                                </div>
                                <div class="location">
                                    <h3>Départ</h3>
                                    <p><?= htmlspecialchars($ride['departure_location']) ?></p>
                                </div>
                            </div>
                            <div class="route-point arrival">
                                <div class="time"><!-- Arrival time if available --></div>
                                <div class="point-marker">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="location">
                                    <h3>Arrivée</h3>
                                    <p><?= htmlspecialchars($ride['arrival_location']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ride-options">
                            <div class="option">
                                <i class="fas fa-users"></i>
                                <span><?= (int)$ride['seats_available'] ?> place(s) disponible(s)</span>
                            </div>
                            <div class="option">
                                <i class="fas fa-car"></i>
                                <span><?= htmlspecialchars($ride['vehicle_type']) ?></span>
                            </div>
                            <?php if ($ride['eco_friendly']): ?>
                            <div class="option eco">
                                <i class="fas fa-leaf"></i>
                                <span>Véhicule écologique</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-right">
                        <div class="driver-card">
                            <h3>Conducteur</h3>
                            <div class="driver-info">
                                <div class="driver-avatar">
                                    <?php if (!empty($ride['profile_image'])): ?>
                                        <img src="<?= htmlspecialchars($ride['profile_image']) ?>" alt="Photo de profil" class="profile-image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" style="font-size: 80px;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="driver-details">
                                    <p class="driver-name"><?= htmlspecialchars($ride['driver_name']) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (\App\Helpers\Auth::isLoggedIn() && \App\Helpers\Auth::user()['id'] != $ride['user_id']): ?>
                        <div class="booking-section">
                            <h3>Réserver</h3>
                            <form class="booking-form" action="/reserver-trajet" method="post">
                                <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                                <div class="form-group">
                                    <label for="seats_booked">Nombre de places</label>
                                    <select id="seats_booked" name="seats_booked" required>
                                        <?php for ($i = 1; $i <= min(4, $ride['seats_available']); $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="message">Message (optionnel)</label>
                                    <textarea id="message" name="message" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Réserver</button>
                            </form>
                        </div>
                        <?php elseif (!\App\Helpers\Auth::isLoggedIn()): ?>
                        <div class="login-prompt">
                            <p>Connectez-vous pour réserver ce trajet</p>
                            <a href="/connexion" class="btn btn-secondary">Se connecter</a>
                        </div>
                        <?php endif; ?>
                    </div>
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

<?php if (isset($_SESSION['booking_success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['booking_success']) ?>
        <?php unset($_SESSION['booking_success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['booking_errors'])): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($_SESSION['booking_errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php unset($_SESSION['booking_errors']); ?>
    </div>
<?php endif; ?>

    </section>

    <script src="/assets/js/scripts.js"></script>

</body>
</html>
