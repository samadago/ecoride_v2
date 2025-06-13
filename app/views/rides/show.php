<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du covoiturage - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/ride-status.css">
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
            
            <div class="detail-card <?= 'status-' . ($ride['status'] ?? 'pending') ?>">
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
                
                <?php
                // Define status labels first for use throughout the page
                $statusLabels = [
                    'pending' => 'En attente',
                    'ongoing' => 'En cours',
                    'completed' => 'Terminé',
                    'cancelled' => 'Annulé'
                ];
                
                // Only show status section if user is logged in and has a relationship with this ride
                $isLoggedIn = \App\Helpers\Auth::isLoggedIn();
                $currentUser = $isLoggedIn ? \App\Helpers\Auth::user() : null;
                $isDriver = $isLoggedIn && $currentUser['id'] == $ride['user_id'];
                $isAdmin = $isLoggedIn && isset($currentUser['is_admin']) && $currentUser['is_admin'];
                
                // Check if user is a passenger
                $isPassenger = false;
                if ($isLoggedIn) {
                    require_once BASE_PATH . '/app/models/Booking.php';
                    $bookingModel = new \App\Models\Booking();
                    $isPassenger = $bookingModel->checkExistingBooking($ride['id'], $currentUser['id']);
                }
                
                // Show status section if user has a relationship with this ride
                $canManageStatus = $isDriver || $isPassenger || $isAdmin;
                ?>
                
                <?php if ($canManageStatus): ?>
                <div class="ride-status-section">
                    <h3>Statut du trajet</h3>
                    <div class="status-display">
                        <span class="status-label">Statut actuel:</span>
                        <span id="ride-status" class="ride-status status-<?= $ride['status'] ?? 'pending' ?>">
                            <?= $statusLabels[$ride['status'] ?? 'pending'] ?>
                        </span>
                    </div>
                    
                    <?php if ($isDriver || $isAdmin): ?>
                    <div class="status-actions">
                        <button class="status-btn btn-pending" data-status="pending" <?= ($ride['status'] == 'pending') ? 'disabled' : '' ?>>Marquer comme en attente</button>
                        <button class="status-btn btn-ongoing" data-status="ongoing" <?= ($ride['status'] == 'ongoing') ? 'disabled' : '' ?>>Démarrer le trajet</button>
                        <button class="status-btn btn-completed" data-status="completed" <?= ($ride['status'] == 'completed') ? 'disabled' : '' ?>>Terminer le trajet</button>
                        <button class="status-btn btn-cancelled" data-status="cancelled" <?= ($ride['status'] == 'cancelled') ? 'disabled' : '' ?>>Annuler le trajet</button>
                    </div>
                    <?php endif; ?>
                    
                    <input type="hidden" id="ride-id" value="<?= $ride['id'] ?>">
                </div>
                <?php endif; ?>
                
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
                                <div class="time"><?= date('H:i', strtotime($ride['estimated_arrival_time'] ?? $ride['departure_time'])) ?></div>
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
                                <span><?= (int)$ride['remaining_seats'] ?> place(s) restante(s)</span>
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
                        
                        <?php if (\App\Helpers\Auth::isLoggedIn() && \App\Helpers\Auth::user()['id'] != $ride['user_id'] && ($ride['status'] == 'pending') && $ride['remaining_seats'] > 0): ?>
                        <div class="booking-section">
                            <h3>Réserver</h3>
                            <form class="booking-form" action="/reserver-trajet" method="post">
                                <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                                <div class="form-group">
                                    <label for="seats_booked">Nombre de places</label>
                                    <select id="seats_booked" name="seats_booked" required>
                                        <?php for ($i = 1; $i <= min(4, $ride['remaining_seats']); $i++): ?>
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
                        <?php elseif (\App\Helpers\Auth::isLoggedIn() && \App\Helpers\Auth::user()['id'] != $ride['user_id'] && ($ride['status'] == 'pending') && $ride['remaining_seats'] <= 0): ?>
                        <div class="booking-closed">
                            <p>Ce trajet est complet, aucune place disponible.</p>
                        </div>
                        <?php elseif (!\App\Helpers\Auth::isLoggedIn()): ?>
                        <div class="login-prompt">
                            <p>Connectez-vous pour réserver ce trajet</p>
                            <a href="/connexion" class="btn btn-secondary">Se connecter</a>
                        </div>
                        <?php elseif ($ride['status'] != 'pending'): ?>
                        <div class="booking-closed">
                            <p>Les réservations sont fermées pour ce trajet.</p>
                            <p>Statut: 
                                <span class="ride-status status-<?= $ride['status'] ?>">
                                    <?= $statusLabels[$ride['status']] ?>
                                </span>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="/assets/js/scripts.js"></script>
    <script src="/assets/js/ride-status.js"></script>
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
