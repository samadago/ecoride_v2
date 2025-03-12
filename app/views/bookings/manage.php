<section class="bookings-section">
    <div class="container">
        <div class="bookings-card">
            <h2>Gérer les réservations</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($bookings)): ?>
                <p class="no-bookings">Aucune réservation en attente.</p>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-item">
                            <div class="booking-info">
                                <h3>Réservation #<?= htmlspecialchars($booking['id']) ?></h3>
                                <p><strong>Passager:</strong> <?= htmlspecialchars($booking['passenger_name']) ?></p>
                                <p><strong>Trajet:</strong> <?= htmlspecialchars($booking['departure_city']) ?> → <?= htmlspecialchars($booking['arrival_city']) ?></p>
                                <p><strong>Date:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($booking['departure_date']))) ?></p>
                                <p><strong>Statut:</strong> <span class="status-<?= htmlspecialchars($booking['status']) ?>"><?= htmlspecialchars(ucfirst($booking['status'])) ?></span></p>
                            </div>
                            
                            <?php if ($booking['status'] === 'pending'): ?>
                                <div class="booking-actions">
                                    <form action="/gerer-reservations" method="post" class="inline-form">
                                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-success">Accepter</button>
                                    </form>
                                    
                                    <form action="/gerer-reservations" method="post" class="inline-form">
                                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                                        <input type="hidden" name="action" value="decline">
                                        <button type="submit" class="btn btn-danger">Refuser</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>