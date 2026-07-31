# Structure de la base de données — Top Maracana API

## Vue d'ensemble

Le système gère des tournois de football de type Maracana. Un organisateur crée des compétitions, les managers inscrivent leurs équipes avec une sélection de joueurs, et l'organisateur valide ou refuse les inscriptions avant le lancement automatique des groupes et matchs.

---

## Tables principales

### `users`
Comptes utilisateurs. Deux rôles possibles : `organizer` et `team_manager`.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| name | string | Nom complet |
| email | string unique | Email de connexion |
| password | string | Hash du mot de passe |
| role | enum | `organizer` ou `team_manager` |
| email_verified_at | timestamp nullable | Date de vérification email |
| email_verification_code | string nullable | Code OTP de vérification |
| email_verification_expires_at | timestamp nullable | Expiration du code OTP |
| password_reset_code | string nullable | Code OTP de réinitialisation |
| password_reset_expires_at | timestamp nullable | Expiration du code reset |
| created_at / updated_at | timestamp | |

---

### `competitions`
Représente un tournoi Maracana créé par un organisateur.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| organizer_id | uuid FK | Référence vers `users.id` |
| name | string | Nom du tournoi |
| location | string | Lieu du tournoi |
| start_date | date | Date de début |
| end_date | date | Date de fin |
| max_teams | integer | Nombre maximum d'équipes |
| players_per_team | integer | Nombre de joueurs requis par sélection |
| registration_fee | decimal nullable | Frais d'inscription |
| prize_description | text nullable | Description du prix |
| age_min | integer nullable | Âge minimum des joueurs |
| age_max | integer nullable | Âge maximum des joueurs |
| poster_image | string nullable | Chemin du fichier affiche |
| matches_per_day | integer | Matchs planifiés par jour |
| status | enum | `draft` / `registration_open` / `full` / `ongoing` / `finished` |
| is_verified | boolean | Compétition vérifiée par admin |
| winner_id | uuid FK nullable | Référence vers `teams.id` |
| top_scorer_id | uuid FK nullable | Référence vers `players.id` |
| best_player_id | uuid FK nullable | Référence vers `players.id` |
| best_goalkeeper_id | uuid FK nullable | Référence vers `players.id` |
| created_at / updated_at | timestamp | |

**Cycle de vie du statut :**
```
draft → registration_open → full → ongoing → finished
```

---

### `competition_days`
Jours de la semaine disponibles pour les matchs d'une compétition.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| competition_id | uuid FK | Référence vers `competitions.id` |
| day_of_week | integer | 0 = Dimanche, 1 = Lundi, …, 6 = Samedi |

---

### `competition_time_slots`
Créneaux horaires disponibles pour les matchs.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| competition_id | uuid FK | Référence vers `competitions.id` |
| start_time | time | Heure de début |
| end_time | time | Heure de fin |

---

### `teams`
Représente l'équipe permanente d'un manager. Indépendante de toute compétition — une équipe peut s'inscrire à plusieurs tournois.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| manager_id | uuid FK | Référence vers `users.id` |
| name | string | Nom de l'équipe |
| logo | string nullable | Chemin du fichier logo |
| created_at / updated_at | timestamp | |

---

### `players`
Effectif général d'une équipe. Tous les joueurs enregistrés, indépendamment des compétitions.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| team_id | uuid FK | Référence vers `teams.id` |
| full_name | string | Nom complet |
| birth_date | date | Date de naissance (YYYY-MM-DD) |
| is_goalkeeper | boolean | Est gardien de but |
| national_id_number | string nullable unique | Numéro CNIB |
| national_id_photo | string nullable | Chemin photo CNIB |
| created_at / updated_at | timestamp | |

---

### `registrations`
Représente l'inscription d'une équipe à une compétition.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| competition_id | uuid FK | Référence vers `competitions.id` |
| team_id | uuid FK | Référence vers `teams.id` |
| status | enum | `pending` / `approved` / `rejected` |
| payment_status | string nullable | Statut du paiement |
| receipt_file | string nullable | Justificatif de paiement |
| created_at / updated_at | timestamp | |

**Cycle de vie :**
```
pending → approved (organisateur valide)
        → rejected (organisateur refuse)
```

---

### `registration_players` ← NOUVELLE TABLE
Table pivot entre une inscription et les joueurs **sélectionnés** pour cette compétition. Permet à une équipe d'avoir un effectif général plus large que le nombre requis par tournoi.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire (généré par MySQL UUID()) |
| registration_id | uuid FK | Référence vers `registrations.id` |
| player_id | uuid FK | Référence vers `players.id` |
| created_at / updated_at | timestamp | |

**Contrainte unique :** `(registration_id, player_id)`

---

### `groups`
Groupes générés automatiquement quand une compétition est pleine.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| competition_id | uuid FK | Référence vers `competitions.id` |
| name | string | Nom du groupe (ex: "Groupe A") |

---

### `group_team`
Pivot entre groupes et équipes, avec statistiques de classement.

| Colonne | Type | Description |
|---|---|---|
| group_id | uuid FK | Référence vers `groups.id` |
| team_id | uuid FK | Référence vers `teams.id` |
| played | integer | Matchs joués |
| win | integer | Victoires |
| draws | integer | Matchs nuls |
| losses | integer | Défaites |
| goals_for | integer | Buts marqués |
| goals_against | integer | Buts encaissés |
| goal_difference | integer | Différence de buts |
| points | integer | Points au classement |

---

### `game_matches`
Matchs planifiés ou joués.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| competition_id | uuid FK | Référence vers `competitions.id` |
| group_id | uuid FK nullable | Référence vers `groups.id` (null si knockout) |
| home_team_id | uuid FK | Équipe domicile |
| away_team_id | uuid FK | Équipe extérieur |
| round_type | enum | `group` / `semi` / `final` |
| week_number | integer | Numéro de la semaine |
| day_of_week | integer | Jour du match (0–6) |
| match_time | time | Heure du match |
| status | enum | `scheduled` / `played` |

---

### `match_results`
Résultat d'un match.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| match_id | uuid FK unique | Référence vers `game_matches.id` |
| home_score | integer | Score domicile |
| away_score | integer | Score extérieur |
| home_penalty_score | integer nullable | Tirs au but domicile |
| away_penalty_score | integer nullable | Tirs au but extérieur |
| status | enum | `played` |

---

### `match_goals`
Buts marqués dans un match.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| match_id | uuid FK | Référence vers `game_matches.id` |
| player_id | uuid FK | Joueur buteur |
| team_id | uuid FK | Équipe du buteur |
| minute | integer nullable | Minute du but |

---

### `match_cards`
Cartons distribués dans un match.

| Colonne | Type | Description |
|---|---|---|
| id | uuid | Clé primaire |
| match_id | uuid FK | Référence vers `game_matches.id` |
| player_id | uuid FK | Joueur sanctionné |
| team_id | uuid FK | Équipe du joueur |
| card_type | enum | `yellow` / `red` |
| minute | integer nullable | Minute du carton |

---

## Relations

```
User (organizer) (1) ──── (N) Competition
User (manager)   (1) ──── (N) Team

Team             (1) ──── (N) Player           ← effectif général
Team             (1) ──── (N) Registration      ← inscriptions à des compétitions

Competition      (1) ──── (N) Registration
Registration     (N) ──── (N) Player            ← via registration_players
                                                   (sélection pour ce tournoi)

Competition      (1) ──── (N) Group
Group            (N) ──── (N) Team              ← via group_team (avec stats)
Competition      (1) ──── (N) GameMatch
GameMatch        (1) ──── (1) MatchResult
GameMatch        (1) ──── (N) MatchGoal
GameMatch        (1) ──── (N) MatchCard
```

---

## Changement majeur — Juillet 2026

### Avant
- Une équipe était directement liée à une compétition via `competition_id` sur la table `teams`
- Lors de l'inscription, le backend validait que `team.players.count == competition.players_per_team`
- Une équipe ne pouvait participer qu'à une seule compétition

### Après
- `competition_id` retiré de `teams` — une équipe est une entité permanente et indépendante
- L'équipe a un effectif général (`players`) sans limite de nombre
- Lors de l'inscription (`POST /competitions/{id}/register`), le manager envoie `player_ids` : la sélection exacte pour ce tournoi
- Le backend valide que `player_ids.count == competition.players_per_team`, que tous les joueurs appartiennent à l'équipe, et que les contraintes d'âge et de gardien sont respectées sur la sélection
- Une même équipe peut s'inscrire à plusieurs compétitions avec des compositions différentes
- La table `registration_players` stocke la sélection par inscription
