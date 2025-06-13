<?php
// Set page variables
$currentPage = 'reviews';
$pageTitle = 'Avis pour ' . htmlspecialchars($data['user']['first_name'] . ' ' . $data['user']['last_name']) . ' - EcoRide';

// Start output buffering
ob_start();
?>

<div class="user-reviews-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- User Header -->
                <div class="user-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php if (!empty($data['user']['profile_image'])): ?>
                                <img src="/<?= htmlspecialchars($data['user']['profile_image']) ?>" alt="Photo de profil">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <h1><?= htmlspecialchars($data['user']['first_name'] . ' ' . $data['user']['last_name']) ?></h1>
                            <p>Membre depuis <?= date('F Y', strtotime($data['user']['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <!-- Rating Summary -->
                    <div class="rating-summary">
                        <div class="rating-score">
                            <span class="score"><?= number_format($data['ratingSummary']['average_rating'], 1) ?></span>
                            <div class="stars">
                                <?php
                                $rating = $data['ratingSummary']['average_rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star filled"></i>';
                                    } elseif ($i - 0.5 <= $rating) {
                                        echo '<i class="fas fa-star-half-alt filled"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <p><?= $data['ratingSummary']['rating_count'] ?> avis</p>
                        </div>
                    </div>
                </div>
                
                <!-- Reviews List -->
                <div class="reviews-section">
                    <h2>Avis et commentaires</h2>
                    
                    <?php if (!empty($data['reviews'])): ?>
                        <div class="reviews-list">
                            <?php foreach ($data['reviews'] as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <h4><?= htmlspecialchars($review['from_user_first_name'] . ' ' . $review['from_user_last_name']) ?></h4>
                                            <div class="review-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : 'empty' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="review-date">
                                            <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($review['comment']) && $review['comment_approved'] == 1): ?>
                                        <div class="review-comment">
                                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                        </div>
                                    <?php elseif (!empty($review['comment']) && $review['comment_approved'] == 0): ?>
                                        <div class="review-comment pending">
                                            <i class="fas fa-clock"></i>
                                            <em>Commentaire en cours de modération</em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-reviews">
                            <i class="fas fa-star"></i>
                            <h3>Aucun avis pour le moment</h3>
                            <p>Cet utilisateur n'a pas encore reçu d'avis.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Back Button -->
                <div class="actions">
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-reviews-container {
    padding: 2rem 0;
    min-height: 70vh;
}

.user-header {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-avatar {
    width: 80px;
    height: 80px;
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
    font-size: 80px;
    color: #6c757d;
}

.user-details h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
    color: #333;
}

.user-details p {
    margin: 0;
    color: #666;
    font-size: 1rem;
}

.rating-summary {
    text-align: center;
}

.rating-score .score {
    font-size: 3rem;
    font-weight: bold;
    color: #4ECE5D;
    display: block;
    line-height: 1;
}

.rating-score .stars {
    margin: 0.5rem 0;
}

.rating-score .stars i {
    font-size: 1.2rem;
    margin: 0 0.1rem;
}

.rating-score .stars i.filled {
    color: #ffc107;
}

.rating-score .stars i:not(.filled) {
    color: #ddd;
}

.rating-score p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.reviews-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.reviews-section h2 {
    margin-bottom: 2rem;
    color: #333;
    font-size: 1.5rem;
}

.review-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
}

.review-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.reviewer-info h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    color: #333;
}

.review-rating i {
    font-size: 1rem;
    margin-right: 0.1rem;
}

.review-rating i.filled {
    color: #ffc107;
}

.review-rating i.empty {
    color: #ddd;
}

.review-date {
    color: #666;
    font-size: 0.9rem;
}

.review-comment {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    line-height: 1.6;
    color: #333;
}

.review-comment.pending {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    font-style: italic;
    text-align: center;
}

.review-comment.pending i {
    margin-right: 0.5rem;
    color: #f39c12;
}

.no-reviews {
    text-align: center;
    padding: 3rem 2rem;
    color: #666;
}

.no-reviews i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.no-reviews h3 {
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.no-reviews p {
    margin: 0;
    font-size: 1rem;
}

.actions {
    text-align: center;
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

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .user-header {
        flex-direction: column;
        gap: 2rem;
        text-align: center;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
    
    .reviews-section {
        padding: 1rem;
    }
    
    .review-header {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once BASE_PATH . '/app/views/layouts/main.php';
?> 