<div class="content-section">
    <div class="content-header">
        <h1>Gestion des véhicules</h1>
        <a href="/admin/vehicles/create" class="btn-add">
            <i class="fas fa-car-alt"></i> Ajouter un véhicule
        </a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Propriétaire</th>
                <th>Véhicule</th>
                <th>Année</th>
                <th>Couleur</th>
                <th>Immatriculation</th>
                <th>Places</th>
                <th>Écologique</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">Aucun véhicule trouvé</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo $vehicle['id']; ?></td>
                        <td><?php echo $vehicle['owner_first_name'] . ' ' . $vehicle['owner_last_name']; ?></td>
                        <td><?php echo $vehicle['brand'] . ' ' . $vehicle['model']; ?></td>
                        <td><?php echo $vehicle['year']; ?></td>
                        <td><?php echo $vehicle['color'] ?? '-'; ?></td>
                        <td><?php echo $vehicle['license_plate']; ?></td>
                        <td><?php echo $vehicle['seats']; ?></td>
                        <td>
                            <button type="button" class="btn-secondary" onclick="confirmToggleEco(<?php echo $vehicle['id']; ?>, <?php echo $vehicle['eco_friendly'] ? 'true' : 'false'; ?>)">
                                <?php if ($vehicle['eco_friendly']): ?>
                                    <i class="fas fa-leaf" style="color: #2ecc71;"></i> Oui
                                <?php else: ?>
                                    <i class="fas fa-times" style="color: #e74c3c;"></i> Non
                                <?php endif; ?>
                            </button>
                        </td>
                        <td class="action-cell">
                            <a href="/admin/vehicles/delete/<?php echo $vehicle['id']; ?>" class="btn-delete delete-btn">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div> 