<?php
use App\Models\User;

$userModel = new User();
// Set current page for navigation highlighting
$currentPage = 'rides';
$pageTitle = 'EcoRide - Covoiturages';
?>

<!-- Barre de recherche compacte -->
<section class="compact-search">
    <div class="container">
        <form class="search-form-compact" action="/covoiturages" method="get">
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="autocomplete-wrapper">
                    <input type="text" name="departure" id="depart-compact" class="autocomplete-input" placeholder="Départ" value="<?= htmlspecialchars($departure ?? '') ?>">
                    <input type="hidden" name="departure_coords" id="depart-compact-coords">
                    <div class="autocomplete-dropdown" id="depart-compact-dropdown"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-map-marker"></i>
                </div>
                <div class="autocomplete-wrapper">
                    <input type="text" name="arrival" id="arrivee-compact" class="autocomplete-input" placeholder="Arrivée" value="<?= htmlspecialchars($arrival ?? '') ?>">
                    <input type="hidden" name="arrival_coords" id="arrivee-compact-coords">
                    <div class="autocomplete-dropdown" id="arrivee-compact-dropdown"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <input type="datetime-local" name="date" id="date-compact" value="<?= htmlspecialchars($date ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>
</section>

<!-- Liste des covoiturages -->
<section class="rides-list">
    <div class="container">
        <?php if (!empty($rides)): ?>
            <?php foreach ($rides as $ride): ?>
                <div class="ride-card">
                    <div class="ride-locations">
                        <div class="departure">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($ride['departure_location']) ?></span>
                        </div>
                        <div class="arrival">
                            <i class="fas fa-map-marker"></i>
                            <span><?= htmlspecialchars($ride['arrival_location']) ?></span>
                        </div>
                    </div>
                    
                    <div class="driver-info">
                        <?php
                        $profileImage = $ride['profile_image'] ? '/' . $ride['profile_image'] : '/assets/images/default-avatar.png';
                        ?>
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Photo du conducteur" class="driver-photo">
                        <div class="driver-details">
                            <h3><?= htmlspecialchars($ride['driver_name'] ?? 'Conducteur') ?></h3>
                            <div class="rating">
                                <?php
                                $userRating = $userModel->getUserRating($ride['driver_id']);
                                $rating = $userRating['average_rating'] ?? 0;
                                $ratingCount = $userRating['rating_count'] ?? 0;
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                                <span>(<?= number_format($rating, 1) ?>) - <?= $ratingCount ?> avis</span>
                            </div>
                        </div>
                    </div>

                    <div class="ride-details">
                        <div class="ride-info">
                            <div class="departure-time">
                                <i class="fas fa-clock"></i>
                                <span><?= date('H:i', strtotime($ride['departure_time'])) ?></span>
                            </div>
                            <div class="seats-info">
                                <i class="fas fa-user"></i>
                                <span><?= htmlspecialchars($ride['seats_available']) ?> places</span>
                            </div>
                        </div>
                        <div class="ride-price-action">
                            <div class="price-value"><?= htmlspecialchars($ride['price']) ?> €</div>
                            <a href="/detail-covoiturage?id=<?= $ride['id'] ?>" class="btn btn-secondary">Voir</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Aucun covoiturage disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</section>