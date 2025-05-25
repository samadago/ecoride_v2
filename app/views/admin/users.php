<div class="content-section">
    <div class="content-header">
        <h1>Gestion des utilisateurs</h1>
        <a href="/admin/users/create" class="btn-add">
            <i class="fas fa-user-plus"></i> Ajouter un utilisateur
        </a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Crédit</th>
                <th>Statut</th>
                <th>Date d'inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Aucun utilisateur trouvé</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="/<?php echo $user['profile_image']; ?>" alt="Photo de profil" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user-circle" style="font-size: 40px; color: #ccc;"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo number_format($user['credit'], 2); ?> €</td>
                        <td>
                            <?php if ($user['is_admin']): ?>
                                <span class="status-badge" style="background-color: #d4edda; color: #155724;">Administrateur</span>
                            <?php else: ?>
                                <span class="status-badge" style="background-color: #cce5ff; color: #004085;">Utilisateur</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                        <td class="action-cell">
                            <a href="/admin/credit-requests" class="btn-secondary" title="Gérer les crédits">
                                <i class="fas fa-coins"></i>
                            </a>
                            <button type="button" class="btn-secondary" onclick="confirmToggleAdmin(<?php echo $user['id']; ?>, <?php echo $user['is_admin'] ? 'true' : 'false'; ?>)">
                                <?php echo $user['is_admin'] ? '<i class="fas fa-user"></i>' : '<i class="fas fa-user-shield"></i>'; ?>
                            </button>
                            
                            <?php if ($user['id'] != 1): /* Prevent deletion of the first admin */ ?>
                                <a href="/admin/users/delete/<?php echo $user['id']; ?>" class="btn-delete delete-btn">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div> 