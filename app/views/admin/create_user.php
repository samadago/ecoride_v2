<div class="content-section">
    <div class="content-header">
        <h1>Ajouter un utilisateur</h1>
        <a href="/admin/users" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
    
    <form class="admin-form" method="POST" action="/admin/users/store">
        <div class="form-group">
            <label for="first_name">Prénom *</label>
            <input type="text" id="first_name" name="first_name" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['first_name']) ? htmlspecialchars($_SESSION['form_data']['first_name']) : ''; ?>" required>
            <?php if (isset($_SESSION['errors']['first_name'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['first_name']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="last_name">Nom *</label>
            <input type="text" id="last_name" name="last_name" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['last_name']) ? htmlspecialchars($_SESSION['form_data']['last_name']) : ''; ?>" required>
            <?php if (isset($_SESSION['errors']['last_name'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['last_name']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>" required>
            <?php if (isset($_SESSION['errors']['email'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['email']; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe *</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <?php if (isset($_SESSION['errors']['password'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['password']; ?></div>
            <?php endif; ?>
            <small>Minimum 8 caractères</small>
        </div>
        
        <div class="form-group">
            <label class="checkbox-container">
                <input type="checkbox" id="is_admin" name="is_admin" 
                       <?php echo isset($_SESSION['form_data']['is_admin']) && $_SESSION['form_data']['is_admin'] ? 'checked' : ''; ?>>
                <span class="checkmark"></span>
                Administrateur
            </label>
            <?php if (isset($_SESSION['errors']['is_admin'])): ?>
                <div class="error-message"><?php echo $_SESSION['errors']['is_admin']; ?></div>
            <?php endif; ?>
            <small>Seuls les domaines d'email contenant "ecoride" (ex: user@ecoride.com, admin@ecoride.space) peuvent être définis comme administrateurs.</small>
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