# Guide des corrections — Septembre 2026

Lot de **7 corrections**, appliquées et vérifiées directement sur la base de
**Reyeh10/App-Atelier** (branche `main`, commit `0b6273d` — « Add production
Vite build »).

## 0. État git — rien n'est poussé

| | |
|---|---|
| Dépôt de travail local | `app-atelier-corrections-reyeh10/` (worktree séparé du projet principal) |
| Branche | `corrections-2026-09` |
| Basée sur | `Reyeh10/App-Atelier@main` (`0b6273d`) |
| Commit | *« Corrections septembre 2026 (VIN, date, permission devis, prix pièces, profil) »* |
| Poussé vers un remote ? | **Non.** Aucun `git push` n'a été fait, ni vers `Reyeh10/App-Atelier`, ni ailleurs. |

Cette branche attend le feu vert **et** l'URL du dépôt de destination
(annoncée comme différente de `Reyeh10/App-Atelier`) pour être poussée —
elle est déjà prête, aucune reconstruction ne sera nécessaire à ce moment-là.

### Cause de l'incompatibilité rencontrée précédemment

`fathiahmedosman59-ux/App-Atelier` (base du premier paquet de corrections) et
`Reyeh10/App-Atelier` ont divergé à partir d'un ancêtre commun (`9f96044`).
Vérification faite : le code métier (les 12 fichiers modifiés) est resté
**strictement identique** des deux côtés — seuls des fichiers de build front
(`.gitignore`, `package-lock.json`, `public/build/*`) différaient. Le vrai
problème n'était donc pas un conflit de code, mais très probablement : les
fichiers « modifiés » collés sans les fichiers « nouveaux » (notamment
`ProfilController.php`, absent du dépôt cible → erreur fatale « classe
introuvable »), et/ou l'absence de `php artisan migrate`. Ce nouveau paquet
élimine toute ambiguïté de base en étant construit et vérifié directement
contre `Reyeh10/App-Atelier`.

## Contenu du dossier `rapport/Corrections-2026-09/`

| Élément | Ce qu'il contient |
|---------|-------------------|
| `fichiers_nouveaux/` | Les 3 nouveaux fichiers — **CODE COMPLET** |
| `fichiers_modifies_reference/` | Les 12 fichiers modifiés — **CODE COMPLET de la version corrigée** |
| `fichiers_modifies_DIFF.txt` | Uniquement les lignes changées (`Reyeh10/App-Atelier@main` → `corrections-2026-09`) |
| `GUIDE-corrections.md` | Le présent document |
| `LISEZMOI.txt` | Résumé court + mode d'emploi |

---

## 1. Vue d'ensemble

| N° | Sujet | Type |
|----|-------|------|
| 1 | VIN obligatoire et strictement 17 caractères alphanumériques à la création d'un véhicule | Validation + UI |
| 2 | Date de mise en circulation obligatoire à la création d'un véhicule | Validation + UI |
| 3 | Bouton « Créer un devis » invisible dans l'OR malgré la permission `gerer_devis` | Bug permissions |
| 4 | Prix unitaire des pièces : ne plus le saisir au garage (fourni par le magasin) | Workflow devis |
| 5 | Nouvel espace « Mon profil » : mot de passe + préférence de langue | Nouvelle fonctionnalité |
| 6 | Cloche de notifications : pastille coupée + flèches de barre de défilement parasites | Bug affichage |
| 7 | Page de connexion : chevauchement d'informations quand la fenêtre est courte | Bug responsive |

---

## 2. Fichiers à AJOUTER (3) — dossier `fichiers_nouveaux/`

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/ProfilController.php` | Espace profil : afficher la page, changer son mot de passe, enregistrer la langue |
| `database/migrations/2026_09_07_202606_add_locale_to_users_table.php` | Colonne `users.locale` (`fr` par défaut, valeurs `fr` / `en` / `ar`) |
| `resources/views/profil/edit.blade.php` | Page « Mon profil » (compte, mot de passe, langue) |

---

## 3. Fichiers à MODIFIER (12) — dossier `fichiers_modifies_reference/`

| Fichier | Corrections concernées |
|---------|------------------------|
| `app/Http/Controllers/VehiculeController.php` | 1, 2 |
| `app/Http/Controllers/DevisController.php` | 4 |
| `app/Models/User.php` | 5 |
| `routes/web.php` | 5 |
| `resources/views/vehicules/_form.blade.php` | 1, 2 |
| `resources/views/dossiers-reception/create.blade.php` | 1, 2 |
| `resources/views/ordres-reparations/create.blade.php` | 1, 2 |
| `resources/views/ordres-reparations/show.blade.php` | 3 |
| `resources/views/devis/create.blade.php` | 4 |
| `resources/views/devis/edit.blade.php` | 4 |
| `resources/views/layouts/app.blade.php` | 5, 6 |
| `resources/views/auth/login.blade.php` | 7 |

---

## 4. Détail par correction

### Correction 1 — VIN obligatoire (17 caractères alphanumériques exactement)

**Demande.** Le VIN doit être obligatoire dès qu'on crée un véhicule (à la
réception comme dans le menu Véhicules) et doit faire exactement 17 caractères
alphanumériques, ni plus ni moins.

**Solution.**

- Règle appliquée partout : `required`, `size:17`, `regex:/^[A-Z0-9]{17}$/`,
  `unique`. Le VIN est normalisé (majuscules, espaces retirés) avant validation.
- Messages d'erreur français ajoutés (obligatoire / exactement 17 caractères /
  lettres et chiffres uniquement).

**Fichiers modifiés.**

- `app/Http/Controllers/VehiculeController.php`
  - `store()` — menu Véhicules → création
  - `update()` — menu Véhicules → modification (règle d'unicité ignorant le véhicule courant)
  - `storeRapide()` — création rapide utilisée par la réception **et** le formulaire OR (`POST /api/vehicules/rapide`)
- `resources/views/vehicules/_form.blade.php` — champ marqué `*`, `required`, `minlength/maxlength=17`, `pattern`, message d'aide.
- `resources/views/dossiers-reception/create.blade.php` — modale « nouveau véhicule » : champ obligatoire, `maxlength=17`, contrôle JS bloquant si le VIN n'a pas 17 caractères alphanumériques.
- `resources/views/ordres-reparations/create.blade.php` — même modale, mêmes contrôles.

**À noter.** La colonne `vehicules.vin` reste `nullable` en base (les véhicules
déjà enregistrés sans VIN ne sont pas cassés), mais **toute modification** d'une
ancienne fiche exigera désormais un VIN valide avant enregistrement.

---

### Correction 2 — Date de mise en circulation obligatoire

**Demande.** Même logique que le VIN : obligatoire à la création d'un véhicule.

**Solution.**

- Règle appliquée partout : `required`, `date`, `before_or_equal:today`
  (une date de mise en circulation ne peut pas être dans le futur).
- Messages français ajoutés (obligatoire / date invalide / pas dans le futur).
- La modale de création rapide (réception + OR) **ne comportait pas** ce champ :
  il a été **ajouté** (juste après « Année »).

**Fichiers modifiés.**

- `app/Http/Controllers/VehiculeController.php` — `store()`, `update()`,
  `storeRapide()` (nouvelle clé `date_mise_circulation` dans la validation).
- `resources/views/vehicules/_form.blade.php` — champ marqué `*`, `required`,
  `max` = aujourd'hui.
- `resources/views/dossiers-reception/create.blade.php` — nouveau champ
  « Date de mise en circulation * » dans la modale, envoyé dans la requête,
  blocage JS si vide, remis à zéro après enregistrement.
- `resources/views/ordres-reparations/create.blade.php` — idem.

**À noter.** Comme pour le VIN : modifier une ancienne fiche sans date de mise
en circulation obligera à en saisir une.

---

### Correction 3 — Bouton « Créer un devis » manquant dans l'OR

**Problème.** Un utilisateur possédant la permission `gerer_devis` (accordée
individuellement) ne voyait ni « + Créer un devis » ni « + Devis complémentaire »
sur la fiche OR. Les boutons étaient conditionnés par
`auth()->user()->canManageWorkshop()`, qui teste **uniquement le rôle**
(`admin` ou `chef_garage`) et ignore la permission. Le `DevisController`, lui,
autorise bien l'action avec `hasPermission('gerer_devis')` — d'où l'incohérence.

**Solution.** Remplacement de `canManageWorkshop()` par
`hasPermission('gerer_devis')` sur les deux boutons.

**Fichier modifié.**

- `resources/views/ordres-reparations/show.blade.php` (2 lignes, bloc « Devis »).

**Impact.** `hasPermission()` renvoie toujours `true` pour l'admin, et
`chef_garage` a `gerer_devis` par défaut → personne ne perd l'accès ;
la fonctionnalité s'ouvre simplement aux comptes à qui la permission a été
donnée explicitement. Les autres usages de `canManageWorkshop()` dans la fiche
OR (affectation technicien, contrôle qualité, lavage…) sont **inchangés**.

---

### Correction 4 — Prix unitaire des pièces non saisi au garage

**Demande.** Là où on crée un devis, ne plus demander le prix unitaire des
pièces : le magasin (stcd-magasin) renvoie le prix en réponse au bon de commande.

**Solution.**

- **Back-end** (`DevisController` : `store`, `storePourDossier`, `update`) :
  la règle `lignes.*.prix_unitaire` passe de `required` à `nullable` ;
  le message `prix_unitaire.required` est supprimé ; les boucles de création
  des lignes utilisent `(float) ($ligne['prix_unitaire'] ?? 0)`.
  Les lignes « pièce » sont enregistrées à **0 FDJ** en attendant la réponse
  du magasin ; le prix réel est ensuite écrit automatiquement par
  `LigneBonCommande::propagerVersDevis()` (mécanisme existant, inchangé).
- **Front-end** (`devis/create.blade.php` et `devis/edit.blade.php`) :
  pour une ligne de type **Pièce**, le champ « P.U. HT » devient `readonly`,
  grisé, avec l'infobulle « Prix fourni automatiquement par le magasin ».
  Il reste éditable pour Main d'œuvre / Forfait / Autre.
  La fonction JS `typeChanged()` verrouille / déverrouille le champ quand on
  change le type d'une ligne. En modification, un prix déjà renvoyé par le
  magasin reste visible et est conservé à l'enregistrement.
  Sous-titre du tableau mis à jour.

**Fichiers modifiés.**

- `app/Http/Controllers/DevisController.php`
- `resources/views/devis/create.blade.php`
- `resources/views/devis/edit.blade.php`

**À noter.** Si le magasin ne répond pas (hors ligne), la pièce reste à 0 FDJ
sur le devis jusqu'à la prochaine synchronisation (ré-enregistrer le devis
relance l'envoi). L'écran de **création de facture** laisse encore modifier les
prix (ajustement final au moment de facturer) — non modifié, la demande visait
le devis.

---

### Correction 5 — Espace « Mon profil » (mot de passe + langue)

**Demande.** L'utilisateur connecté n'avait aucun moyen de changer son propre
mot de passe. Créer un espace profil pour ça, avec en plus une option de langue
du système (Français par défaut, Anglais, Arabe).

**Solution.**

- **Accès** : le bloc nom + avatar en bas de la barre latérale devient un lien
  vers `/profil` (surligné quand on y est).
- **Mot de passe** : self-service, exige le mot de passe actuel (règle
  `current_password`), minimum 8 caractères, confirmation, et un nouveau mot
  de passe différent de l'ancien. Le changement est tracé dans le journal
  d'activité (`modifier_mot_de_passe`). Distinct de
  `UtilisateurController::resetPassword` (réservé à l'admin).
- **Langue** : sélecteur FR / EN / AR, stocké dans `users.locale`.
  **Décision retenue : « Profil seulement, langue plus tard ».** Le choix est
  **enregistré mais sans effet visible** pour l'instant (l'application est
  aujourd'hui 100 % en français écrit en dur). Un bandeau « Bientôt disponible »
  l'indique sur la page. La traduction complète FR/EN/AR + sens droite-à-gauche
  pour l'arabe est un chantier séparé, à faire page par page.

**Fichiers ajoutés.**

- `app/Http/Controllers/ProfilController.php`
- `database/migrations/2026_09_07_202606_add_locale_to_users_table.php`
- `resources/views/profil/edit.blade.php`

**Fichiers modifiés.**

- `app/Models/User.php` — `locale` ajouté au `$fillable`.
- `routes/web.php` — 3 routes dans le groupe `auth` (aucune permission requise) :
  - `GET  /profil`               → `profil.edit`
  - `PATCH /profil/mot-de-passe` → `profil.password`
  - `PATCH /profil/langue`       → `profil.langue`
- `resources/views/layouts/app.blade.php` — bloc utilisateur de la sidebar
  transformé en lien vers le profil.

**Pour activer la langue plus tard.** Middleware faisant
`App::setLocale(auth()->user()->locale)`, fichiers `lang/{fr,en,ar}.json`,
`dir="rtl"` + `lang` sur `<html>` quand `ar`, externalisation des chaînes des
vues.

---

### Correction 6 — Cloche de notifications (affichage cassé)

**Problème.** Le conteneur des actions d'en-tête (à droite) portait
`overflow-x-auto`. En CSS, `overflow-x: auto` force aussi `overflow-y: auto` :
la pastille rouge du compteur (positionnée légèrement en dehors du bouton) était
**coupée**, et une **barre de défilement avec ses flèches** (▲▼ ◀▶) apparaissait
à côté de la cloche dès que les boutons d'en-tête dépassaient la largeur
disponible.

**Solution.** Sur ce conteneur :
`overflow-x-auto` → `flex-wrap justify-end`. Les boutons d'en-tête passent
désormais à la ligne sur petit écran au lieu de générer une barre de
défilement, et la pastille s'affiche entièrement.

**Fichier modifié.**

- `resources/views/layouts/app.blade.php` (ligne du conteneur des actions d'en-tête).

---

### Correction 7 — Page de connexion : chevauchement en écran court

**Problème.** Dans le panneau gauche (branding), le contenu était centré
verticalement (`justify-center`) et le pied de page « © … STCD Motors » était en
`position: absolute` (`bottom-6`), le tout dans un conteneur `overflow-hidden`.
Fenêtre trop courte (ou zoom) → le contenu centré dépassait la hauteur et le
pied de page absolu venait **se superposer** à la liste des fonctionnalités.

**Solution** (`resources/views/auth/login.blade.php`) :

| Élément | Avant | Après |
|---------|-------|-------|
| Panneau gauche | `overflow-hidden … justify-center` | `overflow-y-auto overflow-x-hidden … py-12` (défilable si trop court) |
| Bloc de contenu | centré par le parent | ajout de `my-auto` (reste centré quand il y a la place) |
| Pied de page | `absolute bottom-6 text-slate-600` | `relative flex-shrink-0 mt-8 text-slate-500 text-center` — **en flux normal**, ne peut plus recouvrir le contenu |

Fenêtre haute → rendu identique ; fenêtre courte → le panneau défile proprement,
plus aucun chevauchement. Le formulaire de droite était déjà correct.
Les pages « mot de passe oublié / réinitialisation » utilisent un autre gabarit
sans ce défaut — laissées telles quelles.

---

## 5. Déploiement

À exécuter à la racine du projet cible, dans l'ordre :

```bash
# 1. Mettre en place le code
#    -> copier les fichiers de fichiers_nouveaux/ ET fichiers_modifies_reference/
#       dans le projet (même arborescence), OU récupérer la branche
#       corrections-2026-09 une fois poussée sur le dépôt de destination.

# 2. Migration (correction 5 — colonne users.locale)
php artisan migrate

# 3. Reconstruire les assets front (correction 5 — nouvelles nuances Tailwind
#    rouge/ambre en mode nuit sur la page profil)
npm run build
# Reyeh10/App-Atelier commite public/build/* dans git : penser à committer
# ces fichiers regénérés en plus du code source.

# 4. Vider les caches
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

**Aucune variable d'environnement** à ajouter. **Aucun jeton** à générer.
**Aucun changement** côté `stcd-magasin`.

Vérifications rapides après déploiement :

- `php artisan route:list --name=profil` doit lister 3 routes.
- Colonne `locale` présente sur la table `users` (valeur `fr` par défaut sur
  les comptes existants).
- Créer un véhicule sans VIN / avec un VIN de 16 caractères → refus avec message.
- Un compte non-chef ayant `gerer_devis` voit « + Créer un devis » sur un OR.
- Dans un devis, le prix des lignes « Pièce » est verrouillé.
- Page `/profil` accessible depuis le bloc nom en bas du menu.

---

## 6. Limites connues / suites possibles

- **Langue** : préférence stockée uniquement, interface non traduite (choix
  assumé). Chantier i18n complet à planifier séparément.
- **Prix pièces** : plus de saisie de secours au garage si le magasin est hors
  ligne (la pièce reste à 0 FDJ jusqu'à resynchronisation). Un bouton
  « déverrouiller le prix » pourrait être ajouté si besoin.
- **VIN / date de mise en circulation** : rendre ces champs obligatoires impacte
  aussi la **modification** des fiches véhicule anciennes incomplètes — c'est
  volontaire (fiabilisation des données), à communiquer aux utilisateurs.
