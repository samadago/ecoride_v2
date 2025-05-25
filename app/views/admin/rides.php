<div class="content-section">
    <div class="content-header">
        <h1>Gestion des trajets</h1>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Conducteur</th>
                <th>Véhicule</th>
                <th>Trajet</th>
                <th>Date</th>
                <th>Places dispo.</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rides)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Aucun trajet trouvé</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rides as $ride): ?>
                    <tr>
                        <td><?php echo $ride['id']; ?></td>
                        <td><?php echo $ride['driver_first_name'] . ' ' . $ride['driver_last_name']; ?></td>
                        <td><?php echo $ride['vehicle_brand'] . ' ' . $ride['vehicle_model']; ?></td>
                        <td>
                            <div><strong>Départ:</strong> <?php echo $ride['departure_location']; ?></div>
                            <div><strong>Arrivée:</strong> <?php echo $ride['arrival_location']; ?></div>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($ride['departure_time'])); ?>
                        </td>
                        <td><?php echo $ride['available_seats']; ?></td>
                        <td><?php echo number_format($ride['price'], 2); ?> €</td>
                        <td>
                            <form action="/admin/rides/status/<?php echo $ride['id']; ?>" method="post">
                                <select name="status" class="status-select" style="border-left: 4px solid 
                                    <?php 
                                    switch($ride['status']) {
                                        case 'pending': echo '#f39c12'; break;
                                        case 'ongoing': echo '#3498db'; break;
                                        case 'completed': echo '#2ecc71'; break;
                                        case 'cancelled': echo '#e74c3c'; break;
                                    }
                                    ?>;">
                                    <option value="pending" <?php echo $ride['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="ongoing" <?php echo $ride['status'] === 'ongoing' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="completed" <?php echo $ride['status'] === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="cancelled" <?php echo $ride['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </form>
                        </td>
                        <td class="action-cell">
                            <a href="/admin/rides/delete/<?php echo $ride['id']; ?>" class="btn-delete delete-btn">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div> 