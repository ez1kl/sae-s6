| # | Scenario | Prerequis | Actions | Resultat attendu | Role |
|---|---|---|---|---|---|
| 1 | Catalogue public (consultation) | Front et API demarres | Internaute ouvre /catalogue puis change de page | La liste des livres se charge et la pagination fonctionne | Internaute |
| 2 | Recherche publique multi-criteres | Livres et auteurs en BDD | Internaute renseigne titre + auteur + langue puis lance la recherche | Les resultats correspondent aux filtres saisis | Internaute |
| 3 | Fiche detail d un livre | Un livre existant en BDD | Internaute ouvre /livres/{id} depuis le catalogue | Auteur, categories et disponibilite sont affiches | Internaute |
| 4 | Connexion adherent | Adherent existant en BDD | Saisir email + mdp, cliquer Connexion | Redirection vers Mon espace, session active | Adherent |
| 5 | Reservation adherent | Adherent connecte + livre reservable | Depuis la fiche livre, cliquer Reserver | Reservation creee et message de succes affiche | Adherent |
| 6 | Annulation reservation + profil | Adherent connecte avec reservation | Annuler une reservation puis modifier telephone/adresse dans Mon profil | Reservation supprimee et profil mis a jour | Adherent |
| 7 | Enregistrer un emprunt | Bibliothecaire connecte + membre + livre disponible | Ouvrir /librarian/loan, selectionner membre/livre, valider | Emprunt enregistre avec date de retour prevue | Bibliothecaire |
| 8 | Enregistrer un retour | Bibliothecaire connecte + emprunt actif | Ouvrir /librarian/return, selectionner emprunt, valider | Emprunt passe en rendu et livre disponible | Bibliothecaire |
| 9 | Liste des emprunteurs | Bibliothecaire connecte | Ouvrir /librarian et consulter les prets a jour/en retard | Les emprunteurs sont visibles avec distinction des retards | Bibliothecaire |
| 10 | Dashboard responsable (stats) | Responsable connecte | Ouvrir /admin | Les compteurs globaux sont affiches (livres, membres, emprunts, reservations) | Responsable |
| 11 | CRUD responsable | Responsable connecte | Creer, modifier puis supprimer un livre (ou categorie) via EasyAdmin | Les operations CRUD sont prises en compte sans erreur | Responsable |
| 12 | Gestion des acces | Responsable connecte + utilisateur existant | Modifier le role d un utilisateur | Le nouveau role est enregistre et applique | Responsable |
| 13 | API publique | API disponible | Appeler GET /api/books?page=1&limit=5 sans token | Reponse 200 avec data et meta de pagination | API client |
| 14 | API protegee profil adherent | Token JWT valide d un adherent | POST /api/login puis GET /api/me/profile avec Authorization Bearer | Reponse 200 avec donnees du profil adherent | API client |
