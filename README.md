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

### Configuration du fichier .env

Créez un fichier `.env` à la racine du projet avec les variables suivantes :

```bash
# Configuration de la base de données
DB_HOST=localhost    # En dev: localhost ou 127.0.0.1, en prod: ecoride_db
DB_NAME=ecoride
DB_USER=ecoride
DB_PASS=votre_mot_de_passe

# Configuration de l'application
APP_URL=http://localhost:8000    # En dev: http://localhost:8000, en prod: https://ecoride.space
APP_ENV=development    # En dev: development, en prod: production
```

### Environnement de développement

1. Cloner le dépôt :
```bash
git clone https://github.com/samadago/ecoride_v2.git
cd ecoride_v2
```

2. Copier le fichier d'environnement et le configurer :
```bash
cp .env.example .env
```
Mettez à jour les variables d'environnement dans `.env` avec votre configuration de développement (DB_HOST=localhost ou 127.0.0.1).

3. Démarrer uniquement le conteneur de base de données :
```bash
docker compose up -d db
```

4. Installer les dépendances PHP :
```bash
composer install
```

5. Créer le dossier d'uploads avec les permissions appropriées :
```bash
mkdir -p public/assets/uploads
chmod -R 777 public/assets/uploads
```

6. Lancer le serveur de développement PHP :
```bash
php -S localhost:8000 -t public
```

7. L'application sera disponible sur : http://localhost:8000

### Environnement de production

1. Cloner le dépôt sur votre serveur :
```bash
git clone https://github.com/samadago/ecoride_v2.git
cd ecoride_v2
```

2. Copier le fichier d'environnement et le configurer :
```bash
cp .env.example .env
```
Mettez à jour les variables d'environnement dans `.env` avec votre configuration de production (DB_HOST=ecoride_db).

3. Démarrer les conteneurs Docker :
```bash
docker compose up -d --build
```

4. Créer le dossier d'uploads avec les permissions appropriées :
```bash
mkdir -p public/assets/uploads
chmod -R 777 public/assets/uploads
```

5. L'application sera disponible sur l'URL configurée dans votre serveur.

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

2. **Cloner le dépôt et configurer l'environnement**
    ```bash
    git clone https://github.com/samadago/ecoride_v2.git
    cd ecoride_v2
    cp .env.example .env
    # Modifier le fichier .env avec les paramètres de production
    # DB_HOST=ecoride_db
    # APP_ENV=production
    # APP_URL=https://ecoride.space
    ```

3. **Build de l'image docker et lancement du compose**
    ```bash
    docker compose up -d --build
    ```
    > La version de l'image doit s'incrémenter à chaque montée de version de l'application.

4. **Configurer les permissions du dossier d'uploads**
    ```bash
    mkdir -p public/assets/uploads
    chmod -R 777 public/assets/uploads
    ```

### Post-déploiement
- Configurer le DNS dans le panel Hostinger
- Activer SSL via CADDY
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