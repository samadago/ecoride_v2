<section class="booking-section">
    <div class="container">
        <div class="booking-card">
            <h2>Réserver un trajet</h2>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $field => $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="ride-details">
                <h3>Détails du trajet</h3>
                <div class="ride-info">
                    <p><strong>De:</strong> <?= htmlspecialchars($ride['departure_city']) ?></p>
                    <p><strong>À:</strong> <?= htmlspecialchars($ride['arrival_city']) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($ride['departure_date']))) ?></p>
                    <p><strong>Heure:</strong> <?= htmlspecialchars(date('H:i', strtotime($ride['departure_time']))) ?></p>
                    <p><strong>Prix:</strong> <?= htmlspecialchars($ride['price']) ?> €</p>
                    <p><strong>Places disponibles:</strong> <?= htmlspecialchars($ride['remaining_seats']) ?></p>
                </div>
            </div>
            
            <form action="/reserver-trajet?ride_id=<?= htmlspecialchars($rideId) ?>" method="post" class="booking-form">
                <div class="form-group">
                    <label for="seats_booked">Nombre de places à réserver</label>
                    <select name="seats_booked" id="seats_booked" class="form-control" required>
                        <?php for($i = 1; $i <= min(4, $ride['remaining_seats']); $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <p class="booking-notice">En réservant ce trajet, vous acceptez les conditions générales d'utilisation.</p>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Réserver ce trajet</button>
                    <a href="/covoiturages" class="btn btn-secondary">Retour aux trajets</a>
                </div>
            </form>
        </div>
    </div>
</section>