# 🏆 Top Maracana API
API REST pour la gestion de tournois de football, développée avec Laravel 12 et JWT Authentication.

## 📋 Description
Top Maracana est une application de gestion de compétitions de football. Elle permet à des organisateurs de créer et gérer des tournois, à des chefs d'équipe d'inscrire leurs équipes, et génère automatiquement les groupes (poules) et le programme des matchs une fois toutes les équipes inscrites.

## ⚙️ Stack Technique
TechnologieVersion
PHP8.2
Laravel12
JWT Authtymon/jwt-auth
Swaggerdarkaonline/l5-swagger
Base de donnéesMySQL

## 🚀 Fonctionnalités
### 👤 Gestion des utilisateurs

Inscription / Connexion avec JWT
Vérification d'email
Réinitialisation de mot de passe
Rôles : Admin, Organisateur, Chef d'équipe

### 🏆 Gestion des compétitions

Création / Modification / Suppression
Configuration : nombre d'équipes (8, 16, 32), joueurs par équipe, frais d'inscription, tranche d'âge
Jours et créneaux horaires disponibles
Nombre de matchs par jour (1, 2 ou 3)
Statuts : registration_open → full → ongoing → finished

### 👥 Gestion des équipes

Création d'équipe liée à une compétition spécifique
Vérification automatique de la tranche d'âge des joueurs
Vérification du nombre de joueurs requis
Upload de logo

### 📝 Inscriptions automatiques

Inscription automatiquement approuvée à la création de l'équipe
Vérification des conditions d'inscription
Déclenchement automatique de la génération des poules quand max_teams est atteint

### 🧩 Génération automatique des poules

Déclenchée via un Event (CompetitionFull) dès que toutes les équipes sont inscrites
Groupes de 4 équipes générés aléatoirement (Groupe A, Groupe B, ...)
Algorithme Round Robin : chaque équipe joue contre toutes les autres du groupe

### 📅 Programme des matchs

Génération automatique avec contraintes :

✅ Une équipe ne joue pas deux fois le même jour
✅ Une équipe ne joue pas deux jours consécutifs


Organisation par semaine (Semaine 1, Semaine 2, ...)
Attribution automatique des créneaux horaires

### ⚽ Gestion des matchs

Saisie des buts par joueur (réservé à l'organisateur)
Saisie des cartons jaunes et rouges avec la minute
Clôture du match avec calcul automatique :

Score final
Points (Victoire = 3pts, Nul = 1pt, Défaite = 0pt)
Mise à jour du classement du groupe



### 📊 Classement des groupes

Mise à jour en temps réel après chaque match clôturé
Critères : Points, Victoires, Nuls, Défaites, Buts marqués, Buts encaissés, Différence de buts

### 🔧 Installation
bash# Cloner le projet
git clone https://github.com/DimitriOuedraogo/Top_Maracana_api.git
cd top-maracana-api

#### Installer les dépendances
composer install

#### Configurer l'environnement
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

#### Configurer la base de données dans .env
DB_DATABASE=top_maracana
DB_USERNAME=root
DB_PASSWORD=

#### Lancer les migrations
php artisan migrate

#### Lancer le serveur
php artisan serve
