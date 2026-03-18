## Documentation de l'API - SAE Biblio

Cette documentation décrit les principales routes de l'API REST du backend Symfony

- **Base URL (développement)** : `http://localhost:8000`
- **Préfixe API principal** : `"/api"`
- **Format** : toutes les réponses sont au format JSON.
- **Rôles** :
  - `ROLE_USER` : adhérent (espace membre `/api/me/...`)
  - `ROLE_BIBLIOTHECAIRE` : espace bibliothécaire (`/api/librarian/...`)
  - `ROLE_RESPONSABLE` : espace responsable / admin (`/api/admin/...`)

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
  "email": "user@ex.fr",
  "password": "user"
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
  "email": "user@ex.fr",
  "member": {
    "id": 10,
    "last_name": "Bouyssou",
    "first_name": "Melvin"
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

### 2.4 `GET /api/books/{id}/reservation-status`

Tableau récapitulatif :


| Champ            | Valeur                                            |
| ---------------- | ------------------------------------------------- |
| **Résumé**       | Savoir si un livre est réservable / disponible    |
| **URL**          | `/api/books/{id}/reservation-status`              |
| **Méthode**      | `GET`                                             |
| **Auth requise** | Non (mais le résultat est plus détaillé connecté) |


#### Paramètres de chemin


| Nom  | Type   | Obligatoire | Description          |
| ---- | ------ | ----------- | -------------------- |
| `id` | entier | Oui         | Identifiant du livre |


#### Réponses

- `200 OK` : JSON avec la clé `reservable` et, **si authentifié**, un éventuel `reason`.

Cas possibles (exemples) :

```json
{ "reservable": true }
```

```json
{ "reservable": false }
```

```json
{ "reservable": false, "reason": "reserved" }
```

```json
{ "reservable": false, "reason": "loaned" }
```

Notes :

- En accès public (non authentifié), l’API renvoie seulement `{"reservable": false}` sans préciser la raison.
- La route purge au préalable les réservations expirées.

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

- `200 OK` : profil adhérent (groupes `member:read`) **ou** (cas particulier) infos minimalistes si l’utilisateur n’a pas de profil adhérent.

Cas particulier : un utilisateur authentifié **sans profil adhérent** (ex. admin/bibliothécaire) reçoit une réponse `200` avec `isMember: false` :

```json
{
  "id": null,
  "email": "admin@admin.fr",
  "roles": ["ROLE_RESPONSABLE"],
  "isMember": false
}
```

---

### 5.2 `PUT /api/me/profile`

Tableau récapitulatif :


| Champ            | Valeur                           |
| ---------------- | -------------------------------- |
| **Résumé**       | Mettre à jour le profil adhérent |
| **URL**          | `/api/me/profile`                |
| **Méthode**      | `PUT`                            |
| **Auth requise** | Oui (`ROLE_USER`)                |


#### Corps de requête (JSON)

Champs modifiables :


| Champ         | Type   | Obligatoire | Description |
| ------------- | ------ | ----------- | ----------- |
| `phoneNumber` | string | null        | Non         |
| `address`     | string | null        | Non         |


Exemple :

```json
{
  "phoneNumber": "0601020304",
  "address": "1 rue de la Paix, 75000 Paris"
}
```

#### Réponses

- `200 OK` : profil mis à jour (groupes `member:read`).
- `400 Bad Request` : corps JSON invalide.
- `404 Not Found` : profil adhérent introuvable.

Exemples d’erreur :

```json
{ "error": "Corps JSON invalide." }
```

```json
{ "error": "Profil adhérent introuvable." }
```

---

### 5.3 `GET /api/me/loans`

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

### 5.4 `GET /api/me/reservations`

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

### 5.5 `POST /api/me/reservations`

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
- `403 Forbidden` : compte suspendu.
- `409 Conflict` : le livre est déjà réservé **ou** le membre a atteint la limite de 3 réservations **ou** le livre est actuellement emprunté.

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

```json
{
  "error": "Vous avez atteint la limite de 3 réservations simultanées."
}
```

```json
{
  "error": "Ce livre est actuellement emprunte et ne peut pas etre reserve."
}
```

```json
{
  "error": "Votre compte est suspendu. Impossible de réserver."
}
```

---

### 5.6 `DELETE /api/me/reservations/{id}`

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

## 6. Espace bibliothécaire (`LibrarianController`)

Préfixe de classe : `"/api/librarian"`.

> Toutes les routes de cette section sont protégées par `#[IsGranted('ROLE_BIBLIOTHECAIRE')]`.

### 6.1 `GET /api/librarian/members/search`


| Champ            | Valeur                             |
| ---------------- | ---------------------------------- |
| **Résumé**       | Recherche d’adhérents (nom/prénom) |
| **URL**          | `/api/librarian/members/search`    |
| **Méthode**      | `GET`                              |
| **Auth requise** | Oui (`ROLE_BIBLIOTHECAIRE`)        |


#### Paramètres de requête


| Nom | Type   | Obligatoire | Description                           |
| --- | ------ | ----------- | ------------------------------------- |
| `q` | string | Non         | Terme de recherche (min 2 caractères) |


#### Réponses

- `200 OK` : liste (potentiellement vide). Si `q` < 2 caractères, renvoie `[]`.

Exemple :

```json
[
  {
    "id": 12,
    "firstName": "Melvin",
    "lastName": "Bouyssou",
    "email": "melvin@gmail.com",
    "suspended": false
  }
]
```

---

### 6.2 `GET /api/librarian/members/{id}`


| Champ            | Valeur                                |
| ---------------- | ------------------------------------- |
| **Résumé**       | Détails adhérent + prêts/réservations |
| **URL**          | `/api/librarian/members/{id}`         |
| **Méthode**      | `GET`                                 |
| **Auth requise** | Oui (`ROLE_BIBLIOTHECAIRE`)           |


#### Réponses

- `200 OK` : objet avec `member`, `activeLoans`, `reservations`.
- `404 Not Found` : adhérent introuvable.

---

### 6.3 `POST /api/librarian/loans`


| Champ            | Valeur                      |
| ---------------- | --------------------------- |
| **Résumé**       | Enregistrer un emprunt      |
| **URL**          | `/api/librarian/loans`      |
| **Méthode**      | `POST`                      |
| **Auth requise** | Oui (`ROLE_BIBLIOTHECAIRE`) |


#### Corps de requête (JSON)

```json
{
  "memberId": 10,
  "bookId": 123,
  "force": false
}
```

Notes :

- `force` est optionnel. Si l’API retourne `requireConfirmation: true`, relancer la requête avec `force: true`.

#### Réponses

- `201 Created` : `{ "success": true, "loan": { ... } }`
- `400 Bad Request` : champs requis manquants.
- `404 Not Found` : adhérent ou livre introuvable.
- `422 Unprocessable Entity` : emprunt non autorisé (`success: false`, `error`).
- `409 Conflict` : avertissement nécessitant confirmation (`success: false`, `warning`, `requireConfirmation: true`).

---

### 6.4 `PUT /api/librarian/loans/{id}/return`


| Champ            | Valeur                             |
| ---------------- | ---------------------------------- |
| **Résumé**       | Enregistrer le retour d’un emprunt |
| **URL**          | `/api/librarian/loans/{id}/return` |
| **Méthode**      | `PUT`                              |
| **Auth requise** | Oui (`ROLE_BIBLIOTHECAIRE`)        |


#### Réponses

- `200 OK` : détails de l’emprunt avec `returnDate`.
- `404 Not Found` : emprunt introuvable.
- `422 Unprocessable Entity` : déjà retourné.

---

### 6.5 `GET /api/librarian/active-loans`


| Champ            | Valeur                            |
| ---------------- | --------------------------------- |
| **Résumé**       | Liste de tous les emprunts actifs |
| **URL**          | `/api/librarian/active-loans`     |
| **Méthode**      | `GET`                             |
| **Auth requise** | Oui (`ROLE_BIBLIOTHECAIRE`)       |


#### Réponses

- `200 OK` : tableau d’emprunts actifs avec `isOverdue` et `daysOverdue`.

---

### 6.6 `GET /api/librarian/overdue-loans`


| Champ            | Valeur                         |
| ---------------- | ------------------------------ |
| **Résumé**       | Liste des emprunts en retard   |
| **URL**          | `/api/librarian/overdue-loans` |
| **Méthode**      | `GET`                          |
| **Auth requise** | Oui (`ROLE_BIBLIOTHECAIRE`)    |


#### Réponses

- `200 OK` : tableau d’emprunts en retard avec `daysOverdue`.

---

## 7. Espace responsable / admin

### 7.1 Gestion des adhérents (`AdminMemberController`)

Préfixe de classe : `"/api/admin/members"`.

> Toutes les routes de cette section sont protégées par `#[IsGranted('ROLE_RESPONSABLE')]`.

#### 7.1.1 `GET /api/admin/members`


| Champ            | Valeur                      |
| ---------------- | --------------------------- |
| **Résumé**       | Liste paginée des adhérents |
| **URL**          | `/api/admin/members`        |
| **Méthode**      | `GET`                       |
| **Auth requise** | Oui (`ROLE_RESPONSABLE`)    |


Paramètres de requête :


| Nom      | Type   | Obligatoire | Description                                                              |
| -------- | ------ | ----------- | ------------------------------------------------------------------------ |
| `search` | string | Non         | Recherche (nom/prénom/email selon implémentation repository)             |
| `status` | string | Non         | Filtre statut (ex. `active`/`suspended` selon implémentation repository) |
| `page`   | entier | Non         | Page (défaut 1)                                                          |
| `limit`  | entier | Non         | Taille page (défaut 20, max 100)                                         |


Réponse `200` :

```json
{
  "data": [
    {
      "id": 12,
      "firstName": "Melvin",
      "lastName": "Bouyssou",
      "email": "melvin@gmail.com",
      "membershipDate": "2026-01-10",
      "phoneNumber": "0601020304",
      "suspended": false
    }
  ],
  "meta": { "page": 1, "limit": 20, "total": 1, "totalPages": 1 }
}
```

---

#### 7.1.2 `GET /api/admin/members/{id}`


| Champ            | Valeur                        |
| ---------------- | ----------------------------- |
| **Résumé**       | Détails adhérent + historique |
| **URL**          | `/api/admin/members/{id}`     |
| **Méthode**      | `GET`                         |
| **Auth requise** | Oui (`ROLE_RESPONSABLE`)      |


Réponses :

- `200 OK` : objet avec `member`, `activeLoans`, `loanHistory`, `reservations`.
- `404 Not Found` : adhérent introuvable.

---

#### 7.1.3 `PUT /api/admin/members/{id}/suspend`


| Champ            | Valeur                            |
| ---------------- | --------------------------------- |
| **Résumé**       | Suspendre / réactiver un adhérent |
| **URL**          | `/api/admin/members/{id}/suspend` |
| **Méthode**      | `PUT`                             |
| **Auth requise** | Oui (`ROLE_RESPONSABLE`)          |


Réponses :

- `200 OK` : `{ "id": <id>, "suspended": true/false, "message": "..." }`
- `404 Not Found` : adhérent introuvable.

---

#### 7.1.4 `DELETE /api/admin/members/{memberId}/reservations/{resId}`


| Champ            | Valeur                                               |
| ---------------- | ---------------------------------------------------- |
| **Résumé**       | Annuler une réservation d’un adhérent                |
| **URL**          | `/api/admin/members/{memberId}/reservations/{resId}` |
| **Méthode**      | `DELETE`                                             |
| **Auth requise** | Oui (`ROLE_RESPONSABLE`)                             |


Réponses :

- `204 No Content` : réservation supprimée.
- `404 Not Found` : réservation introuvable (ou ne correspond pas à l’adhérent).

---

### 7.2 Statistiques (`AdminStatsController`)

Préfixe de classe : `"/api/admin/stats"`.

> Toutes les routes de cette section sont protégées par `#[IsGranted('ROLE_RESPONSABLE')]`.

#### 7.2.1 `GET /api/admin/stats/overview`

Réponse `200` :

```json
{
  "totalBooks": 120,
  "activeMembers": 80,
  "currentLoans": 15,
  "overdueCount": 2
}
```

---

#### 7.2.2 `GET /api/admin/stats/loans-by-month`

Paramètres de requête :


| Nom      | Type   | Obligatoire | Description                |
| -------- | ------ | ----------- | -------------------------- |
| `months` | entier | Non         | Nombre de mois (défaut 12) |


Réponse `200` : structure retournée directement par `LoanRepository::getMonthlyLoanStats($months)`.

---

#### 7.2.3 `GET /api/admin/stats/loans-by-category`

Réponse `200` : structure retournée directement par `LoanRepository::getLoansByCategory()`.

---

#### 7.2.4 `GET /api/admin/stats/overdue`

Réponse `200` : liste d’emprunts en retard (format détaillé : `book`, `member`, `loanDate`, `dueDate`, `daysOverdue`).

---

## 8. Exemples de requêtes

### 8.1 Connexion

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@admin.com","password":"admin"}'
```

### 8.2 Liste des livres

```bash
curl http://localhost:8000/api/books?page=1&limit=20
```

### 8.3 Créer une réservation (utilisateur authentifié)

```bash
curl -X POST http://localhost:8000/api/me/reservations \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN>" \
  -d '{"bookId":10}'
```

