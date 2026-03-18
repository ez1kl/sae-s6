| # | Scenario | Prerequis | Actions | Resultat attendu | Role |
|---|---|---|---|---|---|
| 1 | Catalogue public (consultation) | Front et API demarres | Internaute ouvre /catalogue puis change de page | La liste des livres se charge et la pagination fonctionne | Internaute |
| 2 | Recherche publique multi-criteres | Livres et auteurs en BDD | Internaute renseigne titre + auteur + langue puis lance la recherche | Les resultats correspondent aux filtres saisis | Internaute |
| 3 | Fiche detail d un livre | Un livre existant en BDD | Internaute ouvre /livres/{id} depuis le catalogue | Auteur, categories et disponibilite sont affiches | Internaute |
| 4 | Connexion adherent | Adherent existant en BDD | Saisir email + mdp, cliquer Connexion | Redirection vers Mon espace, session active | Adherent |
| 5 | Reservation adherent | Adherent connecte + livre reservable | Depuis la fiche livre, cliquer Reserver | Reservation creee et message de succes affiche | Adherent |
| 6 | Limite reservations atteinte | Limite max de reservations parametree + adherent connecte ayant deja atteint le max | Depuis une fiche livre reservable, cliquer Reserver | Reservation refusee, message explicite indique la limite atteinte, aucune nouvelle reservation creee | Adherent |
| 7 | Annulation reservation | Adherent connecte avec reservation | Ouvrir Mon espace > Mes reservations puis annuler une reservation | Reservation supprimee et message de succes affiche | Adherent |
| 8 | Mise a jour du profil adherent | Adherent connecte | Ouvrir Mon espace > Mon profil, modifier telephone/adresse, enregistrer | Le profil est mis a jour et les nouvelles valeurs s affichent | Adherent |
| 9 | Acces panel biblio | Utilisateur avec role biblio connecte | Ouvrir le panel biblio (menu /librarian) | Le dashboard biblio s affiche avec les acces (membres, emprunts, retours, reservations) | Biblio |
| 10 | Liste des membres (panel biblio) | Biblio connecte + membres en BDD | Depuis le panel biblio, ouvrir la page Liste des membres | La liste des membres s affiche (recherche/pagination si dispo) | Biblio |
| 11 | Detail d un membre (profil + reservations) | Biblio connecte + membre ayant au moins 1 reservation | Depuis la liste des membres, ouvrir la fiche d un membre puis consulter l onglet/section Reservations | Les informations du membre s affichent et ses reservations sont listees avec leur statut | Biblio |
| 12 | Detail d un membre (profil + emprunts) | Biblio connecte + membre ayant au moins 1 emprunt | Depuis la fiche membre, consulter l onglet/section Emprunts | Les emprunts actifs et/ou passes du membre sont visibles (dates, statut) | Biblio |
| 13 | Enregistrer un emprunt | Biblio connecte + membre + livre disponible | Ouvrir /librarian/loan, selectionner membre/livre, valider | Emprunt enregistre avec date de retour prevue | Biblio |
| 14 | Limite emprunts atteinte | Limite max d emprunts parametree + biblio connecte + membre ayant deja atteint le max | Ouvrir /librarian/loan, selectionner le membre puis un livre disponible, valider | Emprunt refuse, message explicite indique la limite atteinte, aucun emprunt cree et le livre reste disponible | Biblio |
| 15 | Enregistrer un retour | Biblio connecte + emprunt actif | Ouvrir /librarian/return, selectionner emprunt, valider | Emprunt passe en rendu et livre disponible | Biblio |
| 16 | Liste des emprunteurs | Biblio connecte | Ouvrir /librarian et consulter les prets a jour/en retard | Les emprunteurs sont visibles avec distinction des retards | Biblio |
| 17 | Dashboard responsable (stats) | Responsable connecte | Ouvrir /admin | Les compteurs globaux sont affiches (livres, membres, emprunts, reservations) | Responsable |
| 18 | CRUD responsable | Responsable connecte | Creer, modifier puis supprimer un livre (ou categorie) via EasyAdmin | Les operations CRUD sont prises en compte sans erreur | Responsable |
| 19 | Gestion des acces | Responsable connecte + utilisateur existant | Modifier le role d un utilisateur | Le nouveau role est enregistre et applique | Responsable |
| 20 | API publique | API disponible | Appeler GET /api/books?page=1&limit=5 sans token | Reponse 200 avec data et meta de pagination | API client |
| 21 | API protegee profil adherent | Token JWT valide d un adherent | POST /api/login puis GET /api/me/profile avec Authorization Bearer | Reponse 200 avec donnees du profil adherent | API client |

Pour 20 et 21 voila les commandes curl utiles :
### 20 :
```
curl -k "https://localhost:8000/api/books?page=1&limit=5"
```
### 21 :
```
curl -k -X POST "https://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@admin.fr","password":"admin"}'
```
```
curl -k "https://localhost:8000/api/me/profile" \
  -H "Authorization: Bearer JWT" \
  -H "Accept: application/json"
```