<?php
// Set page variables
$currentPage = 'reviews';
$pageTitle = 'Évaluer un utilisateur - EcoRide';

// Start output buffering
ob_start();
?>

<div class="review-create-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="review-card">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-star"></i>
                            Évaluer <?= $data['ratingType'] === 'driver' ? 'le conducteur' : 'le passager' ?>
                        </h2>
                    </div>
                    
                    <div class="card-body">
                        <!-- Ride Summary -->
                        <div class="ride-summary">
                            <h4>Détails du trajet</h4>
                            <div class="ride-info">
                                <div class="route">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($data['ride']['departure_location']) ?></span>
                                    <i class="fas fa-arrow-right"></i>
                                    <span><?= htmlspecialchars($data['ride']['arrival_location']) ?></span>
                                </div>
                                <div class="date">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= date('d/m/Y H:i', strtotime($data['ride']['departure_time'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User to Rate -->
                        <div class="user-to-rate">
                            <h4>Évaluer</h4>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php if (!empty($data['userToRate']['profile_image'])): ?>
                                        <img src="/<?= htmlspecialchars($data['userToRate']['profile_image']) ?>" alt="Photo de profil">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="user-details">
                                    <h5><?= htmlspecialchars($data['userToRate']['first_name'] . ' ' . $data['userToRate']['last_name']) ?></h5>
                                    <p><?= $data['ratingType'] === 'driver' ? 'Conducteur' : 'Passager' ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Review Form -->
                        <form action="/avis/store" method="POST" class="review-form">
                            <input type="hidden" name="booking_id" value="<?= $data['booking']['id'] ?>">
                            <input type="hidden" name="to_user_id" value="<?= $data['userToRate']['id'] ?>">
                            
                            <!-- Star Rating -->
                            <div class="form-group rating-group">
                                <label>Note (obligatoire)</label>
                                <div class="star-rating-input">
                                    <input type="hidden" name="rating" id="rating-value" value="0">
                                    <div class="stars">
                                        <i class="fas fa-star" data-rating="1"></i>
                                        <i class="fas fa-star" data-rating="2"></i>
                                        <i class="fas fa-star" data-rating="3"></i>
                                        <i class="fas fa-star" data-rating="4"></i>
                                        <i class="fas fa-star" data-rating="5"></i>
                                    </div>
                                    <span class="rating-text">Cliquez sur les étoiles pour noter</span>
                                </div>
                            </div>
                            
                            <!-- Comment -->
                            <div class="form-group">
                                <label for="comment">Commentaire (optionnel)</label>
                                <textarea name="comment" id="comment" rows="4" class="form-control" 
                                    placeholder="Partagez votre expérience avec cet utilisateur (votre commentaire sera vérifié par nos modérateurs avant publication)"></textarea>
                                <small class="form-text text-muted">
                                    Soyez respectueux et constructif dans vos commentaires. Les messages inappropriés ne seront pas publiés.
                                </small>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" id="submit-review" disabled>
                                    <i class="fas fa-check"></i>
                                    Soumettre l'évaluation
                                </button>
                                <a href="/profil" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.review-create-container {
    padding: 2rem 0;
    min-height: 70vh;
}

.review-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #4ECE5D, #44bd32);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.card-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.card-header i {
    margin-right: 0.5rem;
}

.card-body {
    padding: 2rem;
}

.ride-summary, .user-to-rate {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.ride-summary h4, .user-to-rate h4 {
    margin-bottom: 1rem;
    color: #333;
    font-size: 1.1rem;
}

.ride-info .route {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.ride-info .date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    font-size: 60px;
    color: #6c757d;
}

.user-details h5 {
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
}

.user-details p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.rating-group {
    text-align: center;
    margin-bottom: 2rem;
}

.rating-group label {
    display: block;
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: 1.1rem;
}

.star-rating-input .stars {
    display: inline-flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.star-rating-input .stars i {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
}

.star-rating-input .stars i:hover,
.star-rating-input .stars i.active {
    color: #ffc107;
}

.rating-text {
    display: block;
    color: #666;
    font-size: 0.9rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4ECE5D;
    box-shadow: 0 0 0 2px rgba(78, 206, 93, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #4ECE5D;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #44bd32;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.form-text {
    margin-top: 0.5rem;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .review-create-container {
        padding: 1rem 0;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating-input .stars i');
    const ratingValue = document.getElementById('rating-value');
    const ratingText = document.querySelector('.rating-text');
    const submitButton = document.getElementById('submit-review');
    
    let currentRating = 0;
    
    const ratingTexts = {
        1: '1 étoile - Très décevant',
        2: '2 étoiles - Décevant', 
        3: '3 étoiles - Correct',
        4: '4 étoiles - Bien',
        5: '5 étoiles - Excellent'
    };
    
    stars.forEach((star, index) => {
        star.addEventListener('mouseover', function() {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });
        
        star.addEventListener('click', function() {
            currentRating = parseInt(this.dataset.rating);
            ratingValue.value = currentRating;
            highlightStars(currentRating);
            updateRatingText(currentRating);
            enableSubmitButton();
        });
    });
    
    document.querySelector('.star-rating-input').addEventListener('mouseleave', function() {
        highlightStars(currentRating);
    });
    
    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    function updateRatingText(rating) {
        if (rating > 0) {
            ratingText.textContent = ratingTexts[rating];
            ratingText.style.color = '#4ECE5D';
        } else {
            ratingText.textContent = 'Cliquez sur les étoiles pour noter';
            ratingText.style.color = '#666';
        }
    }
    
    function enableSubmitButton() {
        if (currentRating > 0) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }
});
</script>

<?php
$content = ob_get_clean();
require_once BASE_PATH . '/app/views/layouts/main.php';
?> 