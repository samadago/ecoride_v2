<div class="admin-section">
    <div class="admin-header">
        <h1><i class="fas fa-comment"></i> Modération des commentaires</h1>
        <p>Gérez les commentaires soumis par les utilisateurs (les notes sont automatiquement approuvées)</p>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?= count($pendingReviews) ?></h3>
                <p>Commentaires en attente</p>
            </div>
        </div>
    </div>

    <!-- Pending Reviews -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-clock"></i> Commentaires en attente de modération</h2>
        </div>
        
        <div class="card-body">
            <?php if (!empty($pendingReviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($pendingReviews as $review): ?>
                        <div class="review-moderation-item">
                            <div class="review-header">
                                <div class="review-info">
                                    <div class="users-info">
                                        <span class="from-user">
                                            <i class="fas fa-user"></i>
                                            <strong><?= htmlspecialchars($review['from_user_first_name'] . ' ' . $review['from_user_last_name']) ?></strong>
                                        </span>
                                        <i class="fas fa-arrow-right"></i>
                                        <span class="to-user">
                                            <i class="fas fa-user"></i>
                                            <?= htmlspecialchars($review['to_user_first_name'] . ' ' . $review['to_user_last_name']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="ride-info">
                                        <i class="fas fa-route"></i>
                                        <span><?= htmlspecialchars($review['departure_location']) ?> → <?= htmlspecialchars($review['arrival_location']) ?></span>
                                    </div>
                                    
                                    <div class="review-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                                    </div>
                                </div>
                                
                                <div class="review-rating">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : 'empty' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text"><?= $review['rating'] ?>/5</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($review['comment'])): ?>
                                <div class="review-comment">
                                    <h4>Commentaire :</h4>
                                    <div class="comment-text">
                                        <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="moderation-actions">
                                <form method="POST" action="/admin/reviews/moderate" class="moderation-form">
                                    <input type="hidden" name="rating_id" value="<?= $review['id'] ?>">
                                    
                                    <div class="form-group">
                                        <label for="notes_<?= $review['id'] ?>">Notes administrateur (optionnel) :</label>
                                        <textarea name="notes" id="notes_<?= $review['id'] ?>" class="form-control" 
                                                  placeholder="Raison de l'approbation/rejet..."></textarea>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button type="submit" name="action" value="approve" class="btn btn-success">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                        <a href="/admin/reviews/delete/<?= $review['id'] ?>" 
                                           onclick="return confirm('Supprimer définitivement ?')"
                                           class="btn btn-secondary">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-comment"></i>
                    <h3>Aucun commentaire en attente</h3>
                    <p>Tous les commentaires ont été modérés</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.review-moderation-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    transition: all 0.2s ease;
}

.review-moderation-item:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.review-info {
    flex: 1;
}

.users-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.from-user {
    color: #28a745;
    font-weight: 600;
}

.to-user {
    color: #007bff;
}

.users-info i {
    margin-right: 0.3rem;
}

.ride-info, .review-date {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.ride-info i, .review-date i {
    margin-right: 0.5rem;
    width: 15px;
}

.review-rating {
    text-align: center;
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.stars {
    margin-bottom: 0.5rem;
}

.stars i {
    font-size: 1.2rem;
    margin: 0 0.1rem;
}

.stars i.filled {
    color: #ffc107;
}

.stars i.empty {
    color: #dee2e6;
}

.rating-text {
    font-weight: 600;
    color: #333;
}

.review-comment {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

.review-comment h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1rem;
}

.comment-text {
    color: #555;
    line-height: 1.6;
    font-style: italic;
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 4px;
}

.moderation-actions {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

.moderation-form .form-group {
    margin-bottom: 1rem;
}

.moderation-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #333;
}

.moderation-form .form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.9rem;
    resize: vertical;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.no-data {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}

.no-data i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.no-data h3 {
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
}

.no-data p {
    margin: 0;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .review-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .users-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style> 