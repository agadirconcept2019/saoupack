# Schéma SQL – CRM B2B Maroc

## Table `wp_b2b_leads`

| Colonne | Type | Description |
| --- | --- | --- |
| id | bigint | Identifiant unique du lead. |
| company_name | varchar(190) | Raison sociale. |
| sector | varchar(190) | Secteur d'activité. |
| city | varchar(190) | Ville principale. |
| contact_name | varchar(190) | Contact professionnel. |
| contact_role | varchar(190) | Fonction du contact. |
| phone | varchar(40) | Téléphone professionnel. |
| phone_mobile | varchar(40) | Téléphone portable professionnel. |
| email | varchar(190) | Email professionnel. |
| website | varchar(190) | Site web de l'entreprise. |
| social_json | longtext | Réseaux sociaux (JSON). |
| status | enum | Statut CRM (new, qualified, contacted, inactive). |
| last_contact | datetime | Date du dernier contact. |
| next_action | text | Prochaine action commerciale. |
| follow_up_date | date | Date de relance prévue. |
| interest_level | enum | Niveau d'intérêt (low, medium, high). |
| tags | text | Tags internes (liste libre). |
| notes | longtext | Commentaires internes. |
| source | varchar(190) | Source de collecte. |
| collected_at | datetime | Date de collecte. |
| collected_method | varchar(190) | Méthode de collecte (API, HTML public, etc.). |
| created_at | datetime | Date de création. |
| updated_at | datetime | Date de mise à jour. |

## Table `wp_b2b_lead_interactions`

| Colonne | Type | Description |
| --- | --- | --- |
| id | bigint | Identifiant unique de l'interaction. |
| lead_id | bigint | Référence au lead. |
| interaction_type | varchar(50) | Type d'interaction (appel, email, réunion). |
| content | longtext | Détails de l'interaction. |
| created_at | datetime | Horodatage. |
