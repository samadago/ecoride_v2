<?php if (!isset($layout) || $layout): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer vos crédits - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php endif; ?>

<section class="profile-section">
    <div class="container">
        <h2 class="section-title">Gérer vos crédits</h2>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Photo de profil" class="profile-image">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                    <ul class="profile-nav">
                        <li><a href="/profil"><i class="fas fa-user"></i> Mon profil</a></li>
                        <li><a href="/profil/mes-reservations"><i class="fas fa-ticket-alt"></i> Mes réservations</a></li>
                        <li><a href="/profil/mes-trajets"><i class="fas fa-route"></i> Mes trajets proposés</a></li>
                        <li class="active"><a href="/profil/credits"><i class="fas fa-coins"></i> Mes crédits</a></li>
                        <li><a href="/deconnexion"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="profile-content">
                    <!-- Credit Balance Card -->
                    <div class="credit-balance-card">
                        <div class="credit-amount">
                            <h3>Solde actuel</h3>
                            <div class="amount"><?= number_format($user['credit'] ?? 0, 2) ?> €</div>
                        </div>
                    </div>
                    
                    <!-- Recent Transactions -->
                    <div class="transactions-card">
                        <h3>Transactions récentes</h3>
                        <?php if (empty($transactions)): ?>
                            <p class="no-data">Aucune transaction récente.</p>
                        <?php else: ?>
                            <div class="transactions-list">
                                <?php foreach ($transactions as $transaction): ?>
                                    <div class="transaction-item <?= $transaction['amount'] >= 0 ? 'credit' : 'debit' ?>">
                                        <div class="transaction-icon">
                                            <?php if ($transaction['type'] === 'booking'): ?>
                                                <i class="fas fa-ticket-alt"></i>
                                            <?php elseif ($transaction['type'] === 'cancellation'): ?>
                                                <i class="fas fa-ban"></i>
                                            <?php elseif ($transaction['type'] === 'ride_earnings'): ?>
                                                <i class="fas fa-car"></i>
                                            <?php elseif ($transaction['type'] === 'credit_request'): ?>
                                                <i class="fas fa-plus-circle"></i>
                                            <?php else: ?>
                                                <i class="fas fa-exchange-alt"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="transaction-details">
                                            <div class="transaction-description"><?= htmlspecialchars($transaction['description']) ?></div>
                                            <div class="transaction-date"><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></div>
                                        </div>
                                        <div class="transaction-amount">
                                            <?= $transaction['amount'] >= 0 ? '+' : '' ?><?= number_format($transaction['amount'], 2) ?> €
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Credit Request Form -->
                    <div class="credit-request-card">
                        <h3>Demander des crédits</h3>
                        
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
                        
                        <form action="/profil/demande-credit" method="post" class="credit-request-form">
                            <div class="form-group">
                                <label for="amount">Montant à ajouter (€)</label>
                                <input type="number" id="amount" name="amount" min="10" step="5" placeholder="50" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="reason">Raison (optionnel)</label>
                                <textarea id="reason" name="reason" rows="3" placeholder="Expliquez pourquoi vous avez besoin de ces crédits..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Demander des crédits</button>
                        </form>
                        
                        <div class="credit-request-note">
                            <p><i class="fas fa-info-circle"></i> Votre demande sera examinée par un administrateur. Les crédits seront ajoutés à votre compte après approbation.</p>
                        </div>
                    </div>
                    
                    <!-- Credit Request History -->
                    <?php if (!empty($creditRequests)): ?>
                        <div class="credit-requests-card">
                            <h3>Historique des demandes</h3>
                            <div class="credit-requests-list">
                                <?php foreach ($creditRequests as $request): ?>
                                    <div class="credit-request-item status-<?= $request['status'] ?>">
                                        <div class="request-info">
                                            <div class="request-amount"><?= number_format($request['amount'], 2) ?> €</div>
                                            <div class="request-date"><?= date('d/m/Y', strtotime($request['created_at'])) ?></div>
                                        </div>
                                        <div class="request-status">
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <span class="badge pending">En attente</span>
                                            <?php elseif ($request['status'] === 'approved'): ?>
                                                <span class="badge approved">Approuvée</span>
                                            <?php else: ?>
                                                <span class="badge rejected">Rejetée</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!isset($layout) || $layout): ?>
<script src="/assets/js/scripts.js"></script>
</body>
</html>
<?php endif; ?> 