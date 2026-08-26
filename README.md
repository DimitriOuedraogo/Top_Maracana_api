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
| PATCH | `/api/auth/device-token` | ✅ | Enregistrer le token FCM |

### Compétitions

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| GET | `/api/competitions` | — | Liste des compétitions publiques |
| GET | `/api/competitions/{id}` | — | Détails |
| GET | `/api/competitions/{id}/groups` | — | Groupes et équipes |
| GET | `/api/competitions/{id}/matches` | — | Matchs par semaine |
| GET | `/api/competitions/{id}/knockout` | — | Phase knockout |
| GET | `/api/competitions/{id}/statistics` | — | Statistiques |
| GET | `/api/competitions/{id}/teams` | — | Équipes approuvées |
| GET | `/api/competitions/my` | ✅ | Mes compétitions (organisateur) |
| POST | `/api/competitions` | ✅ | Créer |
| POST | `/api/competitions/{id}` | ✅ | Modifier |
| POST | `/api/competitions/{id}/publish` | ✅ | Publier (draft → registration_open) |
| POST | `/api/competitions/{id}/poster` | ✅ | Uploader l'affiche |
| DELETE | `/api/competitions/{id}` | ✅ | Supprimer |
| POST | `/api/competitions/{id}/generate-knockout` | ✅ | Générer le knockout |
| POST | `/api/competitions/{id}/next-round` | ✅ | Prochain tour knockout |

### Équipes

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| GET | `/api/teams` | — | Liste des équipes |
| GET | `/api/teams/{id}` | — | Détails |
| GET | `/api/teams/my` | ✅ | Mes équipes avec inscriptions |
| POST | `/api/teams` | ✅ | Créer une équipe |
| POST | `/api/teams/{id}` | ✅ | Modifier |
| DELETE | `/api/teams/{id}` | ✅ | Supprimer |

### Inscriptions

| Méthode | Endpoint | Auth | Description |
|---------|----------|------|-------------|
| POST | `/api/competitions/{id}/register` | ✅ | Inscrire une équipe avec sélection de joueurs |
| GET | `/api/competitions/{id}/registrations` | ✅ | Liste des inscriptions (organisateur) |
| PATCH | `/api/registrations/{id}/approve` | ✅ | Approuver une inscription |
| PATCH | `/api/registrations/{id}/reject` | ✅ | Refuser une inscription |

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
| Laravel Reverb | — | Serveur WebSocket (port 8080) |
| MySQL | — | Base de données |
| Vite | 7.x | Build des assets |
| Tailwind CSS | 4.x | Styles frontend |
| Axios | 1.x | Requêtes HTTP |
| PHPUnit | — | Tests unitaires |

---

## Changelog

### 2026-08-03 — Notifications push FCM (Firebase Cloud Messaging)

**`PATCH /auth/device-token` — Nouveau endpoint pour enregistrer le token FCM**
- Raison : pour envoyer des notifications push sur le téléphone de l'utilisateur, le backend a besoin de son FCM token généré par l'app Flutter au démarrage.
- Body : `{ "fcm_token": "string" }` — Auth JWT requise.
- Fichier : `app/Http/Controllers/AuthController.php`, `routes/api.php`.

**Colonne `fcm_token` ajoutée sur la table `users`**
- Fichier : `database/migrations/2026_08_03_130746_add_fcm_token_to_users_table.php`.

**`FcmService` — Service d'envoi de notifications via l'API FCM HTTP v1**
- Utilise `google/auth` (OAuth2) pour obtenir un access token depuis le fichier de credentials Firebase (`storage/app/firebase-credentials.json`).
- Raison du choix HTTP v1 direct : le package `kreait/laravel-firebase` n'est pas compatible avec PHP 8.2 (requiert PHP 8.3+).
- Les erreurs Firebase n'interrompent pas la réponse API (try/catch + log).
- Fichier : `app/Services/FcmService.php`, `config/services.php`.

**3 notifications push déclenchées automatiquement**
- Nouvelle inscription → notifie l'**organisateur** : `"[Équipe] vient de s'inscrire à [Compétition]"`
- Inscription approuvée → notifie le **manager** : `"Votre équipe est validée pour [Compétition]"`
- Inscription refusée → notifie le **manager** : `"Votre inscription à [Compétition] a été refusée"`
- Fichier : `app/Services/RegistrationService.php`.

**Variables d'environnement ajoutées**
```env
FIREBASE_CREDENTIALS=firebase-credentials.json
FIREBASE_PROJECT_ID=maracana-app-14b85
```

---

### 2026-08-03 — Correction re-inscription après refus (500 → UPDATE)

**`POST /competitions/{id}/register` — Re-inscription possible après un refus**
- Avant : une inscription refusée (`status = rejected`) restait en base. Si l'équipe tentait de se réinscrire, un `INSERT` sur la même paire `(competition_id, team_id)` violait la contrainte `UNIQUE` → erreur 500.
- Après : le backend détecte l'inscription rejetée existante et fait un `UPDATE status = 'pending'` + `sync()` des nouveaux joueurs au lieu d'un `INSERT`.
- `sync()` remplace aussi les anciens joueurs dans la table `registration_players`, évitant un doublon dans la pivot.
- Fichier : `app/Services/RegistrationService.php`.

---

### 2026-08-01 — Temps réel organisateur, upload affiche, corrections

**`POST /competitions/{id}/poster` — Nouveau endpoint d'upload d'affiche**
- Avant : modifier une compétition via `POST /competitions/{id}` re-validait tous les champs obligatoires, impossible d'envoyer juste l'image.
- Après : endpoint dédié qui accepte uniquement `poster_image` en `multipart/form-data`.
- Fichier : `app/Http/Controllers/CompetitionController.php`, `app/Services/CompetitionService.php`, `app/Http/Requests/Competitions/UploadPosterRequest.php`.

**URL des affiches (`poster_url`) — Dynamique selon le host de la requête**
- Avant : `APP_URL` (défini à `http://localhost` dans `.env`) était utilisé pour construire l'URL de l'affiche, ce qui retournait des URLs inaccessibles depuis ngrok ou un autre domaine.
- Après : `request()->getSchemeAndHttpHost()` est utilisé à la place, l'URL s'adapte automatiquement à l'hôte de la requête (ngrok, production, localhost).
- Fichier : `app/Models/Competition.php` → `getPosterUrlAttribute()`.

**`NewRegistration` — Événement temps réel lors d'une inscription d'équipe**
- Raison : l'organisateur doit être notifié immédiatement quand une équipe soumet une demande d'inscription, sans avoir à rafraîchir manuellement.
- Fonctionnement : à chaque appel réussi de `POST /competitions/{id}/register`, un événement est broadcasté sur le canal privé `private-organizer.{organizer_id}` via Laravel Reverb.
- Fichier : `app/Events/NewRegistration.php` (créé), `app/Services/RegistrationService.php`.

**`channels.php` — Correction de l'autorisation du canal `private-team.{id}`**
- Avant : `$user->team` utilisait une relation `hasOne` — seul le premier manager d'une équipe était reconnu, et un manager avec plusieurs équipes était refusé sur les autres.
- Après : `$user->teams()->where('id', $teamId)->exists()` — fonctionne pour n'importe quel nombre d'équipes.
- Ajout du canal `private-organizer.{organizerId}` pour les notifications d'inscription en temps réel.
- Fichier : `routes/channels.php`.

**`User::team()` → `User::teams()` — Relation corrigée**
- Raison : un manager peut gérer plusieurs équipes. La relation `hasOne` était incorrecte et bloquait l'autorisation des canaux WebSocket.
- Changement : `hasOne(Team::class, 'manager_id')` → `hasMany(Team::class, 'manager_id')`.
- Fichier : `app/Models/User.php`.

**`GET /teams/my` — Correction de l'ordre des routes (404)**
- Avant : la route `GET /teams/{id}` était enregistrée avant `GET /teams/my`, Laravel interprétait donc `"my"` comme un UUID et retournait "Équipe introuvable".
- Après : le groupe authentifié contenant `GET /teams/my` est déclaré avant la route publique `GET /teams/{id}`.
- Fichier : `routes/api.php`.

**`GET /teams/my` — Réponse enrichie avec inscriptions et joueurs sélectionnés**
- Raison : le mobile a besoin de savoir dans quelles compétitions chaque équipe est inscrite et quels joueurs ont été sélectionnés pour chaque inscription.
- La réponse inclut maintenant : `registrations[].competition`, `registrations[].players` (sélection par compétition), `registrations[].status`.
- Fichier : `app/Services/TeamService.php` → `getMyTeams()`.

---

### 2026-07-31 — Table pivot `registration_players` et sélection de joueurs par inscription

**Nouvelle table `registration_players`**
- Raison : lors d'une inscription à une compétition, l'équipe doit sélectionner exactement `players_per_team` joueurs parmi son effectif. Cette sélection est spécifique à chaque inscription (pas identique d'une compétition à l'autre).
- La table `registration_players` stocke le lien entre une inscription et les joueurs sélectionnés.
- Fichier : `database/migrations/2026_07_31_000001_create_registration_players_table.php`.

**`POST /competitions/{id}/register` — Champ `player_ids` requis**
- Avant : l'inscription enregistrait toute l'équipe sans distinction de joueurs.
- Après : le manager doit envoyer `player_ids` (tableau d'UUIDs) pour sélectionner exactement les joueurs qui participeront.
- Validations ajoutées : nombre exact de joueurs = `players_per_team`, tous les joueurs appartiennent à l'équipe, exactement 1 gardien dans la sélection, âges dans les limites de la compétition.
- Fichier : `app/Services/RegistrationService.php`, `app/Http/Controllers/RegistrationController.php`.

**Relations ajoutées : `Registration::players()` et `Player::registrations()`**
- Fichiers : `app/Models/Registration.php`, `app/Models/Player.php`.

---

### 2026-07-20 — Découplement des équipes et des compétitions

**Suppression de `competition_id` de la table `teams`**
- Avant : une équipe était directement liée à une seule compétition lors de sa création, ce qui empêchait de réutiliser une équipe dans plusieurs compétitions.
- Après : une équipe est une entité indépendante. L'inscription à une compétition se fait séparément via `POST /competitions/{id}/register`.
- Fichier : `database/migrations/2026_07_20_000001_remove_competition_id_from_teams_table.php`.

**`POST /teams` — Simplification de la création d'équipe**
- Avant : créer une équipe nécessitait un `competition_id` et validait les âges/slots de la compétition.
- Après : on crée simplement l'équipe avec ses joueurs. Pas de lien à une compétition.
- Fichiers : `app/Http/Requests/Teams/StoreTeamRequest.php`, `app/Services/TeamService.php`, `app/Repositories/TeamRepository.php`, `app/Models/Team.php`.

**`POST /competitions/{id}/register` — Nouveau endpoint d'inscription**
- Permet à un manager d'inscrire une de ses équipes à une compétition ouverte.
- Fichier : `routes/api.php`, `app/Http/Controllers/RegistrationController.php`.
