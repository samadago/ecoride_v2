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
                                            </div>
                                        </div>
                                        <a href="/detail-covoiturage?id=<?= $ride['id'] ?>" class="btn btn-primary">Voir détails</a>
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
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-rides">Vous n'avez pas encore effectué de réservation.</p>
                        <?php endif; ?>
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
    <script src="/assets/js/scripts.js"></script>
</body>
</html>
