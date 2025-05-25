<div class="content-section">
    <div class="content-header">
        <h1>Ajouter un véhicule</h1>
        <a href="/admin/vehicles" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
    
    <form class="admin-form" method="POST" action="/admin/vehicles/store">
        <div class="form-group">
            <label for="user_id">Propriétaire *</label>
            <select id="user_id" name="user_id" class="form-control" required>
                <option value="">Sélectionner un propriétaire</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo isset($_SESSION['form_data']['user_id']) && $_SESSION['form_data']['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($_SESSION['errors']['user_id'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['user_id']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="brand">Marque *</label>
            <input type="text" id="brand" name="brand" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['brand']) ? htmlspecialchars($_SESSION['form_data']['brand']) : ''; ?>" required>
            <?php if (isset($_SESSION['errors']['brand'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['brand']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="model">Modèle *</label>
            <input type="text" id="model" name="model" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['model']) ? htmlspecialchars($_SESSION['form_data']['model']) : ''; ?>" required>
            <?php if (isset($_SESSION['errors']['model'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['model']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="year">Année *</label>
            <input type="number" id="year" name="year" class="form-control" min="1900" max="<?php echo date('Y') + 1; ?>" 
                   value="<?php echo isset($_SESSION['form_data']['year']) ? htmlspecialchars($_SESSION['form_data']['year']) : date('Y'); ?>" required>
            <?php if (isset($_SESSION['errors']['year'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['year']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="color">Couleur</label>
            <input type="text" id="color" name="color" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['color']) ? htmlspecialchars($_SESSION['form_data']['color']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="license_plate">Plaque d'immatriculation *</label>
            <input type="text" id="license_plate" name="license_plate" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['license_plate']) ? htmlspecialchars($_SESSION['form_data']['license_plate']) : ''; ?>" required>
            <?php if (isset($_SESSION['errors']['license_plate'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['license_plate']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="seats">Nombre de places *</label>
            <input type="number" id="seats" name="seats" class="form-control" min="1" max="9" 
                   value="<?php echo isset($_SESSION['form_data']['seats']) ? htmlspecialchars($_SESSION['form_data']['seats']) : 4; ?>" required>
            <?php if (isset($_SESSION['errors']['seats'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['seats']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="checkbox-container">
                <input type="checkbox" id="eco_friendly" name="eco_friendly" 
                       <?php echo isset($_SESSION['form_data']['eco_friendly']) && $_SESSION['form_data']['eco_friendly'] ? 'checked' : ''; ?>>
                <span class="checkmark"></span>
                Véhicule écologique
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-add">
                <i class="fas fa-save"></i> Enregistrer
            </button>
        </div>
    </form>
</div>

<?php
// Clear session data
unset($_SESSION['errors']);
unset($_SESSION['form_data']);
?> 