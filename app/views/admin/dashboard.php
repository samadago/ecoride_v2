<div class="dashboard-header">
    <h1>Tableau de bord</h1>
    <p>Bienvenue dans l'interface d'administration d'EcoRide.</p>
</div>

<!-- KPI Cards -->
<div class="kpi-cards">
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="kpi-title">Total des utilisateurs</div>
        <div class="kpi-value"><?php echo $stats['totalUsers']; ?></div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-route"></i>
        </div>
        <div class="kpi-title">Total des trajets</div>
        <div class="kpi-value"><?php echo $stats['totalRides']; ?></div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-car"></i>
        </div>
        <div class="kpi-title">Total des véhicules</div>
        <div class="kpi-value"><?php echo $stats['totalVehicles']; ?></div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-icon">
            <i class="fas fa-bookmark"></i>
        </div>
        <div class="kpi-title">Total des réservations</div>
        <div class="kpi-value"><?php echo $stats['totalBookings']; ?></div>
    </div>
</div>

<!-- Status Cards -->
<div class="status-cards">
    <div class="status-card pending">
        <div class="status-title">Trajets en attente</div>
        <div class="status-value"><?php echo $stats['pendingRides']; ?></div>
    </div>
    
    <div class="status-card ongoing">
        <div class="status-title">Trajets en cours</div>
        <div class="status-value"><?php echo $stats['ongoingRides']; ?></div>
    </div>
    
    <div class="status-card completed">
        <div class="status-title">Trajets terminés</div>
        <div class="status-value"><?php echo $stats['completedRides']; ?></div>
    </div>
    
    <div class="status-card cancelled">
        <div class="status-title">Trajets annulés</div>
        <div class="status-value"><?php echo $stats['cancelledRides']; ?></div>
    </div>
</div>

<!-- Charts -->
<div class="chart-container">
    <h2>Répartition des trajets par statut</h2>
    <div style="height: 300px;">
        <canvas id="rideStatusChart" 
                data-pending="<?php echo $stats['pendingRides']; ?>"
                data-ongoing="<?php echo $stats['ongoingRides']; ?>"
                data-completed="<?php echo $stats['completedRides']; ?>"
                data-cancelled="<?php echo $stats['cancelledRides']; ?>">
        </canvas>
    </div>
</div>

<!-- Quick Actions -->
<div class="content-section">
    <div class="content-header">
        <h2>Actions rapides</h2>
    </div>
    
    <div class="quick-actions">
        <a href="/admin/users" class="btn-add">
            <i class="fas fa-users"></i> Gérer les utilisateurs
        </a>
        
        <a href="/admin/rides" class="btn-add" style="margin-left: 10px;">
            <i class="fas fa-route"></i> Gérer les trajets
        </a>
        
        <a href="/admin/vehicles" class="btn-add" style="margin-left: 10px;">
            <i class="fas fa-car"></i> Gérer les véhicules
        </a>
    </div>
</div> 