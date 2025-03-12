# EcoRide - Covoiturage Écologique

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

![EcoRide Banner](public/assets/images/logo_eco.png)

> **Note**: Cette version représente une restructuration complète utilisant l'architecture MVC. Pour accéder à la version précédente du projet, consultez [l'ancien dépôt](https://github.com/samadago/EcoRide).

Une application web de covoiturage écologique pour connecter passagers et conducteurs engagés.

## 📖 Description

EcoRide est une plateforme de covoiturage qui permet :
- 🔍 Aux passagers de trouver des trajets écologiques
- 🚗 Aux conducteurs de proposer leurs trajets
- ♻️ Une réduction de l'empreinte carbone des déplacements

## 🛠 Technologies

**Frontend:**  
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat&logo=bootstrap&logoColor=white)

**Backend:**  
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)

**Base de données:**  
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)

**Outils:**  
![Git](https://img.shields.io/badge/Git-F05032?style=flat&logo=git&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat&logo=docker&logoColor=white)

## 🚀 Installation

### Prérequis
- Docker et Docker Compose installés
- Git pour le contrôle de version

### Étapes d'installation
1. Cloner le dépôt :
```bash
git clone https://github.com/samadago/ecoride_v2.git
cd new_ecoride
```

2. Copier le fichier d'environnement et le configurer :
```bash
cp .env.example .env
```
Mettez à jour les variables d'environnement dans `.env` avec votre configuration.

3. Démarrer les conteneurs Docker :
```bash
docker compose up -d --build
```

4. Installer les dépendances PHP :
```bash
docker-compose exec web composer install
```

5. L'application sera disponible sur : http://localhost

## 🚀 Déploiement sur Hostinger avec Docker Compose

### Prérequis
- Compte Hostinger 
- VPS 
- Docker et Docker Compose installés
- Client SSH (pour la connexion au serveur)

### Étapes de déploiement

1. **Connexion au serveur Hostinger**
    ```bash
    ssh root@ip_vps_hostinger
    ```

2. **Build de l'image docker en local & lancement du compose**
    ```bash
    docker compose up -d --build
    ```
    > La version de l'image doit s'incrémenter a chaque montée de version de l'application.


### Post-déploiement
- Configurer le DNS dans le panel Hostinger
- Activer SSL via DADDY
- Tester l'application : https://ecoride.space

## 📖 Utilisation

Rôles disponibles :
- Visiteur : Consulter les trajets
- Passager : Réserver des trajets
- Conducteur : Publier/gérer des trajets 
- Admin : Gestion complète

## 🤝 Contribution

Les contributions sont les bienvenues !
Procédure :

1. Forker le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commiter les changements (`git commit -m 'Add some AmazingFeature'`)
4. Pusher (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request