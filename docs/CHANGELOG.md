# Changelog

## 0.3.4
- Suppression complète du bouton menu `≡` (toggle sidebar) côté CRM.
- Ajout d'une règle CSS défensive pour masquer tout ancien bouton menu résiduel issu de cache/templates legacy.
- Incrément de version plugin à 0.3.4 pour forcer le rechargement des assets (cache-busting).

## 0.3.3
- Déplacement explicite du bouton menu `≡` vers la barre latérale gauche (`.b2b-crm__sidebar-menuctrl`) et suppression défensive des doublons topbar hérités/cache.
- Cache-busting des assets CRM via version plugin 0.3.3 pour forcer le chargement des styles/scripts mis à jour.

## 0.3.2
- Alignement global de l'UI sur le style NexLink (sidebar/topbar/cards/tables/forms) avec palette primaire verte.
- Scoping CSS renforcé sous `.crm-app` pour backend + portail frontend.
- Topbar enrichie (breadcrumb, notifications, user pill) et sidebar collapsible responsive.
- Onboarding settings consolidé (checks SMTP actionnables + vérification d'accessibilité de la source Google).

## 0.3.1
- Refonte UX Paramétrage avec onglets internes (Général, Modules, Référentiels, Email, Rôles & Accès, Onboarding).
- Suppression de Collecte/Sources de la page Paramétrage (séparées dans leurs pages dédiées).
- Renforcement du portail frontend (ouverture fiable en permalink standard et non-standard).
- Ajout des logs email basiques et du test email opérationnel.
- Blocage des modules désactivés pour les utilisateurs non administrateurs.
