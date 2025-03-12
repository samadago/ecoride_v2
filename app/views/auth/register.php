<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <h2>Inscription</h2>
            <p class="auth-subtitle">déjà membre ? <a href="/connexion">Connectez-vous</a></p>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $field => $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="/inscription" method="post" class="auth-form" enctype="multipart/form-data">
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="first_name" name="first_name" placeholder="Prénom" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" id="last_name" name="last_name" placeholder="Nom" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <input type="tel" id="phone" name="phone" placeholder="Téléphone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="profile_image" class="file-upload-label">
                        <i class="fas fa-camera"></i> Photo de profil (optionnel)
                    </label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-upload-input">
                    <?php if (isset($errors['profile_image'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['profile_image']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <textarea id="bio" name="bio" placeholder="Bio (optionnel)"><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
                    <?php if (isset($errors['bio'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['bio']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                    <div class="password-strength">
                        <div class="strength-meter">
                            <span></span>
                        </div>
                        <p class="strength-text">Force du mot de passe</p>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirmer le mot de passe" required>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">J'accepte les <a href="/mentions-legales">conditions d'utilisation</a></label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-auth">S'inscrire</button>
            </form>
        </div>
    </div>
</section>