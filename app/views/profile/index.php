<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    // Set current page for navigation highlighting
    $currentPage = 'profile';
    $pageTitle = 'Mon Profil - EcoRide';
    
    // Start output buffering
    ob_start();
    ?>
        
    <!-- Profile Section with Left Navigation -->
    <section class="profile-section">
        <div class="container profile-container">
            <div class="profile-nav">
                <ul>
                    <li class="nav-item active" data-tab="personal-info">
                        <i class="fas fa-user"></i> Informations personnelles
                    </li>
                    <li class="nav-item" data-tab="my-rides">
                        <i class="fas fa-car"></i> Mes trajets
                    </li>
                    <li class="nav-item" data-tab="booked-rides">
                        <i class="fas fa-ticket-alt"></i> Mes réservations
                    </li>
                    <li class="nav-item" data-tab="credit-management">
                        <i class="fas fa-coins"></i> Mon crédit
                    </li>
                </ul>
            </div>
    
            <div class="profile-content">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
    
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
    
                <!-- Personal Information Tab -->
                <div class="tab-content active" id="personal-info">
                    <h2 class="section-title">Informations personnelles</h2>
                    <div class="profile-card">
                        <form action="/profil/update" method="post" class="profile-form" enctype="multipart/form-data">
                            <div class="profile-image-section">
                                <?php if (!empty($profile['profile_image'])): ?>
                                    <img src="/<?= htmlspecialchars($profile['profile_image']) ?>" alt="Profile Image" class="current-profile-image">
                                <?php endif; ?>
                                <div class="form-group">
                                    <label for="profile_image" class="file-upload-label">
                                        <i class="fas fa-camera"></i> Changer la photo de profil
                                    </label>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-upload-input">
                                </div>
                            </div>
    
                            <div class="form-group">
                                <label for="first_name">Prénom</label>
                                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($profile['first_name']) ?>" required>
                            </div>
    
                            <div class="form-group">
                                <label for="last_name">Nom</label>
                                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($profile['last_name']) ?>" required>
                            </div>
    
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required>
                            </div>
    
                            <div class="form-group">
                                <label for="phone">Téléphone</label>
                                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                            </div>
    
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                            </div>
    
                            <div class="form-group">
                                <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                <input type="password" id="password" name="password">
                            </div>
    
                            <div class="form-group">
                                <label for="password_confirm">Confirmer le nouveau mot de passe</label>
                                <input type="password" id="password_confirm" name="password_confirm">
                            </div>
    
                            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                        </form>
                    </div>
                </div>
    
                <!-- My Rides Tab -->
                <div class="tab-content" id="my-rides">
                    <h2 class="section-title">Mes trajets proposés</h2>
                    <div class="profile-card">
                        <div class="action-buttons">
                            <a href="/proposer-trajet" class="btn btn-primary">Proposer un trajet</a>
                        </div>
                        <?php if (!empty($userRides)): ?>
                            <div class="rides-list">
                                <?php foreach ($userRides as $ride): ?>
                                    <div class="ride-card">
                                        <div class="ride-info">
                                            <div class="ride-route">
                                                <span class="departure"><?= htmlspecialchars($ride['departure_location']) ?></span>
                                                <i class="fas fa-arrow-right"></i>
                                                <span class="arrival"><?= htmlspecialchars($ride['arrival_location']) ?></span>
                                            </div>
                                            <div class="ride-details">
                                                <span><i class="fas fa-calendar"></i> <?= htmlspecialchars($ride['departure_time']) ?></span>
                                                <span><i class="fas fa-euro-sign"></i> <?= htmlspecialchars($ride['price']) ?></span>
                                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($ride['available_seats']) ?> places</span>
                                                <span class="ride-status <?= strtolower($ride['status'] ?? 'pending') ?>">
                                                    <i class="fas fa-circle"></i> <?= ucfirst($ride['status'] ?? 'En attente') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ride-actions">
                                            <a href="/detail-covoiturage?id=<?= $ride['id'] ?>" class="btn btn-secondary">Voir détails</a>
                                            
                                            <?php if (($ride['status'] ?? '') === 'completed'): ?>
                                                <?php
                                                // Get passengers for this completed ride to allow driver to review them
                                                $bookingModel = new \App\Models\Booking();
                                                $rideBookings = $bookingModel->getByDriverId($profile['id']);
                                                $completedBookings = array_filter($rideBookings, function($b) use ($ride) {
                                                    return $b['ride_id'] == $ride['id'] && $b['status'] === 'completed';
                                                });
                                                ?>
                                                
                                                <?php if (!empty($completedBookings)): ?>
                                                    <div class="passenger-reviews">
                                                        <small>Évaluer les passagers:</small>
                                                        <?php foreach ($completedBookings as $booking): ?>
                                                            <?php
                                                            // Check if driver has already reviewed this passenger
                                                            require_once BASE_PATH . '/app/models/Rating.php';
                                                            $ratingModel = new \App\Models\Rating();
                                                            $hasReviewed = $ratingModel->hasRated($profile['id'], $booking['passenger_id'], $booking['id']);
                                                            ?>
                                                            
                                                            <?php if (!$hasReviewed): ?>
                                                                <a href="/avis/creer?ride_id=<?= $ride['id'] ?>&booking_id=<?= $booking['id'] ?>" 
                                                                   class="btn btn-outline-warning btn-sm">
                                                                    <i class="fas fa-star"></i> <?= htmlspecialchars($booking['passenger_first_name'] ?? 'Passager') ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="reviewed-badge">
                                                                    <i class="fas fa-check"></i> <?= htmlspecialchars($booking['passenger_first_name'] ?? 'Passager') ?> évalué
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-rides">Vous n'avez pas encore proposé de trajets.</p>
                        <?php endif; ?>
                    </div>
                </div>
    
                <!-- Booked Rides Tab -->
                <div class="tab-content" id="booked-rides">
                    <h2 class="section-title">Mes réservations</h2>
                    <div class="profile-card">
                        <?php if (!empty($bookings)): ?>
                            <div class="rides-list">
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="ride-card">
                                        <div class="ride-info">
                                            <div class="ride-route">
                                                <span class="departure"><?= htmlspecialchars($booking['departure_location']) ?></span>
                                                <i class="fas fa-arrow-right"></i>
                                                <span class="arrival"><?= htmlspecialchars($booking['arrival_location']) ?></span>
                                            </div>
                                            <div class="ride-details">
                                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($booking['departure_time'])) ?></span>
                                                <span><i class="fas fa-euro-sign"></i> <?= htmlspecialchars($booking['price']) ?></span>
                                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($booking['seats_booked']) ?> place(s)</span>
                                                <span class="booking-status <?= strtolower($booking['status']) ?>">
                                                    <i class="fas fa-circle"></i> <?= htmlspecialchars($booking['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="booking-actions">
                                            <a href="/detail-covoiturage?id=<?= $booking['ride_id'] ?>" class="btn btn-secondary">Voir détails</a>
                                            <?php if ($booking['status'] === 'En attente'): ?>
                                                <form action="/annuler-reservation" method="post" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">Annuler</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($booking['status'] === 'completed'): ?>
                                                <?php
                                                // Check if passenger has already reviewed the driver
                                                require_once BASE_PATH . '/app/models/Rating.php';
                                                $ratingModel = new \App\Models\Rating();
                                                // Get driver ID from the ride
                                                $rideModel = new \App\Models\Ride();
                                                $ride = $rideModel->getById($booking['ride_id']);
                                                if ($ride) {
                                                    $hasReviewed = $ratingModel->hasRated($profile['id'], $ride['driver_id'], $booking['id']);
                                                }
                                                ?>
                                                
                                                <?php if (!$hasReviewed && isset($ride)): ?>
                                                    <a href="/avis/creer?ride_id=<?= $booking['ride_id'] ?>&booking_id=<?= $booking['id'] ?>" 
                                                       class="btn btn-warning">
                                                        <i class="fas fa-star"></i> Évaluer le conducteur
                                                    </a>
                                                <?php else: ?>
                                                    <span class="reviewed-badge">
                                                        <i class="fas fa-check"></i> Conducteur évalué
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-rides">Vous n'avez pas encore effectué de réservation.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Credit Management Tab -->
                <div class="tab-content" id="credit-management">
                    <h2 class="section-title">Gestion de mon crédit</h2>
                    <div class="profile-card">
                        <div class="credit-balance">
                            <h3>Solde actuel</h3>
                            <div class="balance-amount"><?= number_format($profile['credit'], 2) ?> €</div>
                        </div>
                        
                        <div class="credit-actions">
                            <h3>Demander du crédit</h3>
                            <form action="/profil/credit-request" method="post" class="credit-form">
                                <div class="form-group">
                                    <label for="amount">Montant (€)</label>
                                    <input type="number" id="amount" name="amount" min="10" step="1" value="20" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Demander du crédit</button>
                            </form>
                        </div>
                        
                        <div class="credit-history">
                            <h3>Historique des demandes</h3>
                            <?php if (!empty($creditRequests)): ?>
                                <table class="credit-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($creditRequests as $request): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                                                <td><?= number_format($request['amount'], 2) ?> €</td>
                                                <td>
                                                    <span class="status-badge <?= strtolower($request['status']) ?>">
                                                        <?= $request['status'] ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="no-history">Vous n'avez pas encore fait de demandes de crédit.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="credit-history">
                            <h3>Historique des transactions</h3>
                            <?php if (!empty($transactions)): ?>
                                <table class="credit-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
                                                <td><?= htmlspecialchars($transaction['description']) ?></td>
                                                <td class="<?= $transaction['amount'] > 0 ? 'positive' : 'negative' ?>">
                                                    <?= number_format($transaction['amount'], 2) ?> €
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="no-history">Vous n'avez pas encore de transactions.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php
    // Get the buffered content and clean the buffer
    $content = ob_get_clean();
    
    // Include the layout template
    require_once BASE_PATH . '/app/views/layouts/main.php';
    ?>
    
    <style>
    /* Review-specific styles */
    .reviewed-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.8rem;
        background: #d4edda;
        color: #155724;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .passenger-reviews {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #eee;
    }
    
    .passenger-reviews small {
        display: block;
        margin-bottom: 0.5rem;
        color: #666;
    }
    
    .btn-outline-warning {
        border: 1px solid #ffc107;
        color: #ffc107;
        background: transparent;
        padding: 0.2rem 0.6rem;
        font-size: 0.8rem;
        margin-right: 0.5rem;
    }
    
    .btn-outline-warning:hover {
        background: #ffc107;
        color: #212529;
    }
    
    .ride-status.completed {
        color: #28a745;
    }
    
    .ride-status.pending {
        color: #ffc107;
    }
    
    .ride-status.cancelled {
        color: #dc3545;
    }
    
    .ride-status.ongoing {
        color: #007bff;
    }
    
    .booking-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    .ride-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    </style>
    
    <script src="/assets/js/scripts.js"></script>
</body>
</html>
