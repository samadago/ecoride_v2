<?php
// The layout variables and content are handled by the ContactController
// This file only contains the main content section
?>

    <!-- Section Contact -->
    <section class="contact-section">
        <div class="container">
            <h2 class="section-title">Contactez-nous</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <p>Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Informations de contact</h3>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <p>contact@ecoride.site</p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <p>+33 1 23 45 67 89</p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>123 Avenue de la République<br>75011 Paris, France</p>
                    </div>
                    <div class="social-contact">
                        <h4>Suivez-nous</h4>
                        <div class="social-icons">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-square"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-container">
                    <h3>Envoyez-nous un message</h3>
                    <form action="/contact" method="post" class="contact-form">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
