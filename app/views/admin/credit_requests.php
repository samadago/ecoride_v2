<div class="content-section">
    <div class="content-header">
        <h1>Gestion des demandes de crédit</h1>
        <p>Approuvez ou rejetez les demandes de crédit des utilisateurs</p>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
</div>

<div class="content-section">
    <div class="content-header">
        <h2>Demandes en attente</h2>
    </div>
    
    <?php if (empty($pendingRequests)): ?>
        <p class="no-data">Aucune demande en attente.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Montant</th>
                        <th>Raison</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRequests as $request): ?>
                        <tr>
                            <td><?= $request['id'] ?></td>
                            <td>
                                <div class="user-info">
                                    <?= htmlspecialchars($request['user_first_name'] . ' ' . $request['user_last_name']) ?>
                                </div>
                            </td>
                            <td><?= number_format($request['amount'], 2) ?> €</td>
                            <td><?= empty($request['reason']) ? '-' : htmlspecialchars($request['reason']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                            <td class="action-cell">
                                <form action="/admin/credit-requests/process" method="post" class="inline-form">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-edit" title="Approuver">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form action="/admin/credit-requests/process" method="post" class="inline-form">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-delete" title="Rejeter">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="content-section">
    <div class="content-header">
        <h2>Historique des demandes</h2>
    </div>
    
    <?php if (empty($processedRequests)): ?>
        <p class="no-data">Aucune demande traitée.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Traité par</th>
                        <th>Date de demande</th>
                        <th>Date de traitement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processedRequests as $request): ?>
                        <tr>
                            <td><?= $request['id'] ?></td>
                            <td>
                                <div class="user-info">
                                    <?= htmlspecialchars($request['user_first_name'] . ' ' . $request['user_last_name']) ?>
                                </div>
                            </td>
                            <td><?= number_format($request['amount'], 2) ?> €</td>
                            <td>
                                <?php if ($request['status'] === 'approved'): ?>
                                    <span class="status-badge completed">Approuvée</span>
                                <?php else: ?>
                                    <span class="status-badge cancelled">Rejetée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $request['admin_first_name'] ? htmlspecialchars($request['admin_first_name'] . ' ' . $request['admin_last_name']) : '-' ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($request['created_at'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($request['updated_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="content-section">
    <div class="content-header">
        <h2>Ajouter des crédits manuellement</h2>
    </div>
    
    <form action="/admin/credit-add" method="post" class="admin-form">
        <div class="form-group">
            <label for="user_id">Utilisateur</label>
            <select id="user_id" name="user_id" class="form-control" required>
                <option value="">Sélectionnez un utilisateur</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>">
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="amount">Montant (€)</label>
            <input type="number" id="amount" name="amount" class="form-control" min="1" step="1" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="2" required></textarea>
        </div>
        
        <button type="submit" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter des crédits
        </button>
    </form>
</div> 