## Documentation de l'API - SAE Biblio

Cette documentation décrit les principales routes de l'API REST du backend Symfony du projet SAE Biblio.

- **Base URL (développement)** : `http://localhost:8000`
- **Préfixe API principal** : `"/api"`
- **Format** : toutes les réponses sont au format JSON.
- **Authentification** :
  - L'authentification est gérée par le firewall `json_login` de Symfony.
  - Les détails précis (JWT, cookie de session, header `Authorization`, etc.) dépendent de la configuration de sécurité.
  - Les routes marquées **Auth requise : Oui** nécessitent qu'un utilisateur soit connecté.

---

## 1. Authentification & Utilisateur

### 1.1 `POST /api/login`

Tableau récapitulatif :


| Champ            | Valeur                            |
| ---------------- | --------------------------------- |
| **Résumé**       | Authentification d'un utilisateur |
| **URL**          | `/api/login`                      |
| **Méthode**      | `POST`                            |
| **Auth requise** | Non                               |


#### Corps de requête (JSON)

Les noms exacts des champs dépendent de la configuration du firewall `json_login`. Typiquement :

```json
{
  "email": "user@example.com",
  "password": "monMotDePasse"
}
```

#### Réponses

- `200 OK` : authentification réussie (token, cookie ou autre mécanisme retourné selon la config de sécurité).
- `401 Unauthorized` : échec d'authentification.

Exemple de payload d'erreur (implémentation actuelle) :

```json
{
  "message": "Missing credentials"
}
```

---

### 1.2 `GET /api/user/me`

Tableau récapitulatif :


| Champ            | Valeur                                              |
| ---------------- | --------------------------------------------------- |
| **Résumé**       | Récupérer les informations de l'utilisateur courant |
| **URL**          | `/api/user/me`                                      |
| **Méthode**      | `GET`                                               |
| **Auth requise** | Oui                                                 |


#### Paramètres

Aucun paramètre de chemin ou de requête.

#### Réponses

- `200 OK` : utilisateur authentifié, informations retournées.
- `401 Unauthorized` : aucun utilisateur connecté.

Exemple de réponse `200` :

```json
{
  "id": 1,
  "email": "user@example.com",
  "member": {
    "id": 10,
    "last_name": "Dupont",
    "first_name": "Alice"
  },
  "roles": ["ROLE_USER"]
}
```

Exemple de réponse `401` :

```json
{
  "message": "Not authenticated"
}
```

---

## 2. Livres (`BookController`)

Toutes les routes suivantes sont préfixées par `"/api"` au niveau de la classe.

### 2.1 `GET /api/books`

Tableau récapitulatif :


| Champ            | Valeur                   |
| ---------------- | ------------------------ |
| **Résumé**       | Liste paginée des livres |
| **URL**          | `/api/books`             |
| **Méthode**      | `GET`                    |
| **Auth requise** | Non                      |


#### Paramètres de requête


| Nom     | Type   | Obligatoire | Défaut | Description                                 |
| ------- | ------ | ----------- | ------ | ------------------------------------------- |
| `page`  | entier | Non         | `1`    | Numéro de page (>= 1)                       |
| `limit` | entier | Non         | `20`   | Nombre d'éléments par page (entre 1 et 100) |


#### Réponses

- `200 OK` : liste paginée des livres.

Structure de réponse (groupes de sérialisation `book:list`) :

```json
{
  "data": [
    {
      "...": "champs du livre"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 120,
    "totalPages": 6
  }
}
```

---

### 2.2 `GET /api/books/{id}`

Tableau récapitulatif :


| Champ            | Valeur                        |
| ---------------- | ----------------------------- |
| **Résumé**       | Récupérer un livre par son id |
| **URL**          | `/api/books/{id}`             |
| **Méthode**      | `GET`                         |
| **Auth requise** | Non                           |


#### Paramètres de chemin


| Nom  | Type   | Obligatoire | Description          |
| ---- | ------ | ----------- | -------------------- |
| `id` | entier | Oui         | Identifiant du livre |


#### Réponses

- `200 OK` : livre trouvé (groupes `book:read`).
- `404 Not Found` : si l'id ne correspond à aucun livre.

Exemple de réponse `200` (simplifié) :

```json
{
  "id": 1,
  "title": "Titre du livre",
  "...": "autres champs"
}
```

---

### 2.3 `GET /api/books/search`

Tableau récapitulatif :


| Champ            | Valeur                      |
| ---------------- | --------------------------- |
| **Résumé**       | Recherche avancée de livres |
| **URL**          | `/api/books/search`         |
| **Méthode**      | `GET`                       |
| **Auth requise** | Non                         |


#### Paramètres de requête


| Nom        | Type   | Obligatoire | Description                             |
| ---------- | ------ | ----------- | --------------------------------------- |
| `title`    | string | Non         | Filtrer par titre (recherche textuelle) |
| `author`   | entier | Non         | ID d'auteur                             |
| `category` | entier | Non         | ID de catégorie                         |
| `language` | string | Non         | Code ou nom de langue                   |
| `yearFrom` | entier | Non         | Année de publication minimale           |
| `yearTo`   | entier | Non         | Année de publication maximale           |
| `page`     | entier | Non         | Numéro de page (>= 1, défaut 1)         |
| `limit`    | entier | Non         | Taille de page (1–100, défaut 20)       |


#### Réponses

- `200 OK` : liste paginée des livres correspondant aux critères.

Structure de réponse :

```json
{
  "data": [
    {
      "...": "champs du livre"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 3,
    "totalPages": 1
  }
}
```

---

## 3. Auteurs (`AuthorController`)

Préfixe de classe : `"/api"`.

### 3.1 `GET /api/authors`

Tableau récapitulatif :


| Champ            | Valeur                    |
| ---------------- | ------------------------- |
| **Résumé**       | Liste de tous les auteurs |
| **URL**          | `/api/authors`            |
| **Méthode**      | `GET`                     |
| **Auth requise** | Non                       |


#### Réponses

- `200 OK` : tableau d'auteurs (groupes `author:list`).

Exemple :

```json
[
  {
    "id": 1,
    "lastName": "Hugo",
    "firstName": "Victor"
  }
]
```

---

### 3.2 `GET /api/authors/{id}`

Tableau récapitulatif :


| Champ            | Valeur                         |
| ---------------- | ------------------------------ |
| **Résumé**       | Récupérer un auteur par son id |
| **URL**          | `/api/authors/{id}`            |
| **Méthode**      | `GET`                          |
| **Auth requise** | Non                            |


#### Paramètres de chemin


| Nom  | Type   | Obligatoire | Description        |
| ---- | ------ | ----------- | ------------------ |
| `id` | entier | Oui         | Identifiant auteur |


#### Réponses

- `200 OK` : auteur trouvé (groupes `author:read`).
- `404 Not Found` : auteur introuvable.

---

## 4. Catégories (`CategoryController`)

Préfixe de classe : `"/api"`.

### 4.1 `GET /api/categories`

Tableau récapitulatif :


| Champ            | Valeur                         |
| ---------------- | ------------------------------ |
| **Résumé**       | Liste de toutes les catégories |
| **URL**          | `/api/categories`              |
| **Méthode**      | `GET`                          |
| **Auth requise** | Non                            |


#### Réponses

- `200 OK` : tableau de catégories (groupes `category:read`).

Exemple :

```json
[
  {
    "id": 1,
    "name": "Roman"
  }
]
```

---

### 4.2 `GET /api/categories/{id}`

Tableau récapitulatif :


| Champ            | Valeur                             |
| ---------------- | ---------------------------------- |
| **Résumé**       | Récupérer une catégorie par son id |
| **URL**          | `/api/categories/{id}`             |
| **Méthode**      | `GET`                              |
| **Auth requise** | Non                                |


#### Paramètres de chemin


| Nom  | Type   | Obligatoire | Description           |
| ---- | ------ | ----------- | --------------------- |
| `id` | entier | Oui         | Identifiant catégorie |


#### Réponses

- `200 OK` : catégorie trouvée (groupes `category:read`).
- `404 Not Found` : catégorie introuvable.

---

## 5. Espace adhérent / membre (`MemberController`)

Préfixe de classe : `"/api/me"`.

> Toutes les routes de cette section sont protégées par `#[IsGranted('ROLE_USER')]` et requièrent un utilisateur authentifié.

### 5.1 `GET /api/me/profile`

Tableau récapitulatif :


| Champ            | Valeur                             |
| ---------------- | ---------------------------------- |
| **Résumé**       | Profil adhérent du membre connecté |
| **URL**          | `/api/me/profile`                  |
| **Méthode**      | `GET`                              |
| **Auth requise** | Oui (`ROLE_USER`)                  |


#### Réponses

- `200 OK` : profil adhérent (groupes `member:read`).
- `404 Not Found` : si aucun profil adhérent n'est lié à l'utilisateur.

Exemple de réponse `404` :

```json
{
  "error": "Profil adhérent introuvable."
}
```

---

### 5.2 `GET /api/me/loans`

Tableau récapitulatif :


| Champ            | Valeur                             |
| ---------------- | ---------------------------------- |
| **Résumé**       | Liste des prêts du membre connecté |
| **URL**          | `/api/me/loans`                    |
| **Méthode**      | `GET`                              |
| **Auth requise** | Oui (`ROLE_USER`)                  |


#### Réponses

- `200 OK` : liste des prêts (groupes `loan:read`).
- `404 Not Found` : si le profil adhérent est introuvable.

Exemple de réponse `404` :

```json
{
  "error": "Profil adhérent introuvable."
}
```

---

### 5.3 `GET /api/me/reservations`

Tableau récapitulatif :


| Champ            | Valeur                                    |
| ---------------- | ----------------------------------------- |
| **Résumé**       | Liste des réservations du membre connecté |
| **URL**          | `/api/me/reservations`                    |
| **Méthode**      | `GET`                                     |
| **Auth requise** | Oui (`ROLE_USER`)                         |


#### Réponses

- `200 OK` : liste des réservations (groupes `reservation:read`).
- `404 Not Found` : profil adhérent introuvable.

---

### 5.4 `POST /api/me/reservations`

Tableau récapitulatif :


| Champ            | Valeur                              |
| ---------------- | ----------------------------------- |
| **Résumé**       | Créer une réservation pour un livre |
| **URL**          | `/api/me/reservations`              |
| **Méthode**      | `POST`                              |
| **Auth requise** | Oui (`ROLE_USER`)                   |


#### Corps de requête (JSON)

```json
{
  "bookId": 123
}
```


| Champ    | Type   | Obligatoire | Description                     |
| -------- | ------ | ----------- | ------------------------------- |
| `bookId` | entier | Oui         | Identifiant du livre à réserver |


#### Réponses

- `201 Created` : réservation créée avec succès (groupes `reservation:read`).
- `400 Bad Request` : champ `bookId` manquant.
- `404 Not Found` : livre introuvable ou profil adhérent introuvable.
- `409 Conflict` : le livre est déjà réservé.

Exemples de réponses d'erreur :

```json
{
  "error": "Le champ bookId est requis."
}
```

```json
{
  "error": "Livre introuvable."
}
```

```json
{
  "error": "Ce livre est déjà réservé."
}
```

---

### 5.5 `DELETE /api/me/reservations/{id}`

Tableau récapitulatif :


| Champ            | Valeur                                     |
| ---------------- | ------------------------------------------ |
| **Résumé**       | Annuler une réservation du membre connecté |
| **URL**          | `/api/me/reservations/{id}`                |
| **Méthode**      | `DELETE`                                   |
| **Auth requise** | Oui (`ROLE_USER`)                          |


#### Paramètres de chemin


| Nom  | Type   | Obligatoire | Description                   |
| ---- | ------ | ----------- | ----------------------------- |
| `id` | entier | Oui         | Identifiant de la réservation |


#### Réponses

- `204 No Content` : réservation annulée avec succès.
- `404 Not Found` : réservation ou profil adhérent introuvable.
- `403 Forbidden` : la réservation n'appartient pas au membre connecté.

Exemples de réponses d'erreur :

```json
{
  "error": "Réservation introuvable."
}
```

```json
{
  "error": "Vous ne pouvez annuler que vos propres réservations."
}
```

---

## 6. Autres endpoints (API Platform)

Le projet utilise API Platform, configuré via le fichier `biblio/config/routes/api_platform.yaml` avec le préfixe `"/api"`.  
Les entités annotées avec `ApiResource` exposent automatiquement des endpoints CRUD supplémentaires (liste, détail, création, mise à jour, suppression).

Pour obtenir la liste exhaustive de ces routes :

- Utiliser la commande Symfony :

```bash
bin/console debug:router
```

- Ou, si l'interface API Platform est activée (Swagger / ReDoc), consulter la documentation auto-générée.

---

## 7. Exemples de requêtes

### 7.1 Connexion

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"monMotDePasse"}'
```

### 7.2 Liste des livres

```bash
curl http://localhost:8000/api/books?page=1&limit=20
```

### 7.3 Créer une réservation (utilisateur authentifié)

```bash
curl -X POST http://localhost:8000/api/me/reservations \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{"bookId":123}'
```

