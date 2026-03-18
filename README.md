## SAE Biblio – Guide d’installation et de lancement

**Groupe 3B** : Yassine Badaoui, Ugo Pia, Melvin Bouyssou

Ce dépôt contient :

- un **back-end Symfony / API Platform** dans `biblio`
- un **front-end Angular** dans `front-biblio`
- une **base de données MariaDB** (via `docker-compose.yml`)

Pour aller plus loin, la documentation du projet est disponible dans `docs/` (spécification et endpoints dans `docs/API.md`, cahier de recette dans `docs/CAHIER_RECETTE.md`, documentation technique dans `docs/doc_technique_sae_biblio.pdf`, et scripts/dump SQL dans `docs/dump_sae_db.sql`).

---

## 1. Prérequis

- **Git**
- **PHP ≥ 8.2** avec Composer
- **Node.js ≥ 20** et **npm**
- **Docker**

---

## 2. Configuration de la base de données (MariaDB)

La base est prévue pour tourner dans un conteneur **MariaDB** décrit dans `docker-compose.yml`.

### 2.1. Variables d’environnement Docker

À la racine du projet, copiez le fichier d’exemple :

```bash
cp .env.example .env
```

Puis adaptez si besoin les valeurs dans `.env`

### 2.2. Lancer la base de données

Depuis la **racine** du dépôt :

```bash
docker compose up -d
```

Cela démarre un conteneur MariaDB nommé `sae-mariadb` accessible sur `127.0.0.1:MYSQL_PORT` (par défaut `3306`).

---

## 3. Back-end Symfony (dossier `biblio`)

### 3.1. Configuration de l’environnement Symfony

Placez-vous dans le dossier `biblio` :

```bash
cd biblio
```

Copiez le fichier d’exemple :

```bash
cp .env.exemple .env
```

Adaptez si besoin les valeurs pour correspondre à la configuration Docker.

### 3.2. Installation des dépendances PHP

Depuis `biblio` :

```bash
composer install
```

### 3.3. Création / mise à jour du schéma de base

Assurez-vous que le conteneur MariaDB est démarré (`docker compose up -d` à la racine), puis exécutez :

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```

### 3.4. Chargement des fixtures

Les fixtures créent :

- des **auteurs**, **catégories**, **livres**
- **10 adhérents + utilisateurs** avec rôle `ROLE_USER`
- un **administrateur** avec rôle `ROLE_RESPONSABLE`
- des **emprunts** et **réservations**

Commandes à lancer depuis `biblio` :

```bash
php bin/console doctrine:fixtures:load -n
```

### 3.5. Lancement du serveur Symfony

Toujours dans `biblio` :

```bash
symfony server:start
```

L’API sera accessible en HTTP (et non HTTPS) sur :

- **Base API** : `http://127.0.0.1:8000/`
- **Entrée API Platform** : `http://127.0.0.1:8000/api`

---

## 4. Front-end Angular (dossier `front-biblio`)

### 4.1. Installation des dépendances

Placez-vous dans le dossier `front-biblio` :

```bash
cd front-biblio
npm install
```

### 4.2. Configuration de l’URL de l’API

Le fichier `src/app/environments/environment.ts` contient l’URL de l’API :

- `apiUrl: 'https://localhost:8000/api'`

Adaptez la valeur en fonction de la façon dont vous servez l’API (HTTP/HTTPS, port, host, etc.).

### 4.3. Lancement du serveur Angular

Depuis `front-biblio` :

```bash
npm start
```

ou directement :

```bash
ng serve
```

L’application sera accessible sur :

- **Front Angular** : `http://localhost:4200/`

Elle utilisera l’API exposée sur `http://localhost:8000/api` (ou l’URL configurée dans `environment.ts`).

---

## 5. Identifiants de connexion (fixtures)

### 5.1. Administrateur (Responsable)

- **Email** : `admin@admin.fr`
- **Mot de passe** : `admin`
- **Rôle** : `ROLE_RESPONSABLE`

### 5.2. Adhérents (Membres)

Les 10 membres sont générés avec des emails aléatoires via Faker. Le mot de passe est toujours `user`.

- Email : **aléatoire** (du type `prenom.nom@example.com`, non prédictible)
- Mot de passe commun : `user`
- Rôle : `ROLE_USER`

Pour tester un compte adhérent concret :

1. Chargez les fixtures.
2. Consultez la table `user` dans la base `sae_db` (via Adminer `https://127.0.0.1:8000/adminer/` par exemple ).
3. Relevez un email d’utilisateur avec rôle `ROLE_USER`.
4. Connectez-vous sur le front avec cet email et le mot de passe `user`.

---

## 6. Récapitulatif des commandes

### 6.1. Initialisation complète (première fois)

Depuis la **racine** :

```bash
cp .env.example .env
docker compose up -d

cd biblio
cp .env.exemple .env
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n

cd ../front-biblio
npm install
```

### 6.2. Lancement des services à chaque développement

Depuis la **racine** :

```bash
docker compose up -d        # base de données
```

Dans un premier terminal (back, depuis `biblio`) :

```bash
symfony server:start
```

Dans un second terminal (front, depuis `front-biblio`) :

```bash
npm start
```

Accès :

- **Front** : `http://localhost:4200/`
- **API back** : `http://localhost:8000/api`

