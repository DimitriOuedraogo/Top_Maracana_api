# Top Maracana API

API REST de gestion de tournois de football, construite avec Laravel 12 et PHP 8.2.

## Fonctionnalités

- Gestion complète du cycle de vie des compétitions (inscription → groupes → matchs → knockout → terminé)
- Authentification JWT avec vérification email par OTP
- Génération automatique des groupes et du planning (Round-Robin)
- Gestion des matchs : buts, cartons, tirs au but
- Phase knockout (demi-finales, finale)
- Classements dynamiques mis à jour en temps réel
- Statistiques : meilleur buteur, meilleur joueur, meilleur gardien
- Upload de fichiers : affiches, logos, photos d'identité
- Documentation Swagger intégrée

---

## Prérequis

- PHP >= 8.2
- Composer
- MySQL
- Node.js & npm

---

## Installation

```bash
# 1. Cloner le projet
git clone https://github.com/DimitriOuedraogo/Top_Maracana_api.git
cd top_maracana_api

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=top_maracana_api
DB_USERNAME=root
DB_PASSWORD=

# 6. Générer la clé JWT
php artisan jwt:secret

# 7. Exécuter les migrations
php artisan migrate

# 8. (Optionnel) Peupler la base de données
php artisan db:seed
```

---

## Lancement

```bash
# Démarrer tous les processus en parallèle (serveur, queue, logs, assets)
npm run dev

# Ou démarrer uniquement le serveur Laravel
php artisan serve
```

L'API est disponible sur `http://localhost:8000/api`.

---

## Documentation Swagger

La documentation interactive est accessible à :

```
http://localhost:8000/api/documentation
```

Elle est générée automatiquement en environnement de développement (`L5_SWAGGER_GENERATE_ALWAYS=true`).

---

## Authentification

L'API utilise des tokens **JWT** transmis dans le header :

```
Authorization: Bearer <token>
```

### Flux d'authentification

1. `POST /api/auth/register` — Inscription
2. `POST /api/auth/verify-email` — Vérification par code OTP reçu par email
3. `POST /api/auth/login` — Connexion → retourne le token JWT
4. Utiliser le token dans toutes les requêtes protégées

### Rôles utilisateurs

| Rôle | Description |
|------|-------------|
| `admin` | Accès complet |
| `organizer` | Crée et gère les compétitions |
| `team_manager` | Crée et gère les équipes |

---

## Endpoints

### Authentification

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| POST | `/api/auth/register` | — | Inscription |
| POST | `/api/auth/login` | — | Connexion |
| POST | `/api/auth/verify-email` | — | Vérification email (OTP) |
| POST | `/api/auth/resend-verification` | — | Renvoi du code OTP |
| POST | `/api/auth/forgot-password` | — | Demande de reset |
| POST | `/api/auth/verify-reset-code` | — | Vérification OTP de reset |
| POST | `/api/auth/reset-password` | — | Réinitialisation du mot de passe |
| POST | `/api/auth/logout` | ✅ | Déconnexion |
| POST | `/api/auth/refresh` | ✅ | Rafraîchir le token |
| GET | `/api/auth/me` | ✅ | Profil connecté |

### Compétitions

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| GET | `/api/competitions` | — | Liste des compétitions |
| GET | `/api/competitions/{id}` | — | Détails |
| GET | `/api/competitions/{id}/groups` | — | Groupes et équipes |
| GET | `/api/competitions/{id}/matches` | — | Matchs par semaine |
| GET | `/api/competitions/{id}/knockout` | — | Phase knockout |
| GET | `/api/competitions/{id}/statistics` | — | Statistiques |
| GET | `/api/competitions/my` | ✅ | Mes compétitions |
| POST | `/api/competitions` | ✅ | Créer |
| POST | `/api/competitions/{id}` | ✅ | Modifier |
| DELETE | `/api/competitions/{id}` | ✅ | Supprimer |
| POST | `/api/competitions/{id}/generate-knockout` | ✅ | Générer le knockout |
| POST | `/api/competitions/{id}/next-round` | ✅ | Prochain tour knockout |

### Équipes

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| GET | `/api/teams` | — | Liste des équipes |
| GET | `/api/teams/{id}` | — | Détails |
| GET | `/api/teams/my` | ✅ | Mes équipes |
| POST | `/api/teams` | ✅ | Créer |
| POST | `/api/teams/{id}` | ✅ | Modifier |
| DELETE | `/api/teams/{id}` | ✅ | Supprimer |

### Groupes

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| GET | `/api/groups` | — | Liste des groupes |
| GET | `/api/groups/{id}` | — | Détails |
| GET | `/api/groups/{id}/matches` | — | Matchs du groupe |

### Matchs

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| GET | `/api/matches` | — | Liste des matchs |
| GET | `/api/matches/{id}` | — | Détails |
| POST | `/api/matches/{id}/goals` | ✅ | Ajouter un but |
| POST | `/api/matches/{id}/cards` | ✅ | Ajouter un carton |
| POST | `/api/matches/{id}/close` | ✅ | Clôturer le match |
| POST | `/api/matches/{id}/penalties` | ✅ | Scores des tirs au but |

---

## Cycle de vie d'une compétition

```
registration_open
       │
       ▼  (quota d'équipes atteint → Event CompetitionFull)
     full  ──► Génération automatique des groupes (4 équipes/groupe, nommés A, B, C...)
       │        Génération automatique du planning Round-Robin
       ▼
    ongoing  ──► Saisie des résultats
       │          Mise à jour des classements
       │          Génération du knockout (demi-finales → finale)
       ▼
   finished
```

### Règles de classement

| Résultat | Points |
|----------|--------|
| Victoire | 3 pts |
| Nul | 1 pt |
| Défaite | 0 pt |

### Contraintes de planification

- Une équipe ne joue pas deux fois le même jour
- Une équipe ne joue pas deux jours consécutifs

---

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/     # AuthController, CompetitionController, TeamController,
│   │                    # GroupController, MatchController, KnockoutController
│   └── Requests/        # Validation des requêtes entrantes
├── Models/              # User, Competition, Team, Player, Group,
│   │                    # GameMatch, MatchResult, MatchGoal, MatchCard,
│   │                    # Registration, CompetitionDay, CompetitionTimeSlot
├── Services/            # AuthService, CompetitionService, TeamService,
│   │                    # GroupService, MatchService, KnockoutService
├── Repositories/        # Couche d'accès aux données
├── Events/              # CompetitionFull
├── Listeners/           # Listeners d'événements
└── Mail/                # Emails de vérification et de reset

routes/
└── api.php              # Définition de toutes les routes

database/
├── migrations/          # 24 fichiers de migration
├── factories/           # Factories pour les tests
└── seeders/             # Seeders de données
```

---

## Variables d'environnement importantes

```env
APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=top_maracana_api
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=

JWT_SECRET=        # Généré par : php artisan jwt:secret

L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_BASE_PATH=/api
```

---

## Tests

```bash
php artisan test
```

---

## Stack technique

| Outil | Version | Usage |
|-------|---------|-------|
| PHP | 8.2 | Langage principal |
| Laravel | 12 | Framework |
| tymon/jwt-auth | ^2.2 | Authentification JWT |
| darkaonline/l5-swagger | ^8.6 | Documentation Swagger |
| MySQL | — | Base de données |
| Vite | 7.x | Build des assets |
| Tailwind CSS | 4.x | Styles frontend |
| Axios | 1.x | Requêtes HTTP |
| PHPUnit | — | Tests unitaires |
