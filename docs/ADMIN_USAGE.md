# Guide d'utilisation – CRM B2B Maroc

## Accès

* Le module est strictement **admin-only** (capacité `manage_options`).
* Naviguez vers **CRM B2B Maroc** dans le menu WordPress.

## Liste des leads

* Recherche instantanée par société, email, téléphone ou ville.
* Filtres avancés : statut, ville, secteur, niveau d'intérêt.
* Modification rapide du statut ou de l'intérêt directement dans la table.

## Fiche lead

* Consultez la fiche détaillée pour éditer toutes les informations.
* Ajoutez des commentaires et des prochaines actions.
* Définissez les dates de contact et de relance.

## Envoi d'e-mails

* Depuis la fiche lead, rédigez un email via l'éditeur enrichi.
* L'envoi se fait avec `wp_mail()` (compatible SMTP futur).
* Chaque envoi est journalisé dans l'historique des interactions.

## Historique des interactions

* Ajoutez des interactions (appel, email, réunion, etc.).
* Consultez la timeline pour suivre la relation commerciale.

## Conformité légale

* Utilisez uniquement des données professionnelles publiques.
* Documentez la source et la méthode de collecte.
* Assurez-vous de respecter le RGPD B2B et la loi marocaine 09-08.



## Packaging WordPress (dossier fixe)
- Générez le zip via `bash scripts/build-plugin-zip.sh`.
- Le script force une archive avec la racine `saoupack-crm/` et le fichier principal `saoupack-crm/saoupack-crm.php`.
- Cette structure évite les installations parallèles du plugin (WordPress remplace correctement la version existante).

## Politique fichiers stables (texte-only)
- Le dépôt est maintenu sans fichiers binaires (`.png`, `.jpg`, `.zip`, `.pdf`) pour éviter les erreurs GitHub de diff binaire.
- Utilisez de préférence des assets texte (ex: SVG inline ou fichier `.svg`).
- Vérification rapide : `bash scripts/verify-text-only-repo.sh origin/main HEAD` (ou sans arguments en local).
- Si un objet binaire apparait encore dans l'historique de la branche, réécrivez l'historique puis poussez avec `git push --force-with-lease`.
