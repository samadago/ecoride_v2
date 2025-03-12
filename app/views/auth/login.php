<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <h2>Se connecter</h2>
            <p class="auth-subtitle">Pas encore membre ? <a href="/inscription">Inscrivez-vous</a></p>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $field => $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="/connexion" method="post" class="auth-form">
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="email" name="email" placeholder="Adresse email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-auth">Se connecter</button>
            </form>
        </div>
    </div>
</section>