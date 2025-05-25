<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EcoRide - Administration'; ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header id="admin-header">
        <div class="container">
            <div class="logo">
                <img src="/assets/images/logo_eco.png" alt="Logo EcoRide">
                <a href="/admin">
                    <h1>EcoRide Admin</h1>
                </a>
            </div>
            <div class="user-info">
                <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                    <span>
                        <i class="fas fa-user-shield"></i> 
                        <?php 
                            $user = \App\Helpers\Auth::user();
                            echo $user['first_name'] . ' ' . $user['last_name'];
                        ?>
                    </span>
                    <a href="/deconnexion" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Admin Content -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav>
                <ul>
                    <li>
                        <a href="/admin" <?php echo ($currentPage ?? '') === 'admin' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="/admin/users" <?php echo ($currentPage ?? '') === 'admin-users' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <li>
                        <a href="/admin/rides" <?php echo ($currentPage ?? '') === 'admin-rides' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-route"></i> Trajets
                        </a>
                    </li>
                    <li>
                        <a href="/admin/vehicles" <?php echo ($currentPage ?? '') === 'admin-vehicles' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-car"></i> Véhicules
                        </a>
                    </li>
                    <li>
                        <a href="/admin/credit-requests" <?php echo ($currentPage ?? '') === 'admin-credit-requests' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-coins"></i> Demandes de crédit
                        </a>
                    </li>
                    <li>
                        <a href="/" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Voir le site
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </main>
    </div>

    <script src="/assets/js/admin.js"></script>
</body>
</html> 