# Wikito

Un mini wiki / glossaire en PHP, **sans base de données** : toutes les entrées sont stockées dans un fichier JSON. Conçu pour un hébergement mutualisé (FTP/SFTP uniquement), en HTML/CSS/JS vanilla.

- Lecture mobile-first (safe-areas iOS), recherche instantanée côté client.
- Ajout de termes via une page cachée (`add.php`) protégée par Basic Auth.
- Discret par défaut : `robots.txt`, balises meta et en-têtes `X-Robots-Tag` contre l'indexation (moteurs de recherche et robots d'IA). Aucun sitemap.

## Structure

```
index.php          liste des termes + recherche
add.php            formulaire d'ajout (non lié depuis le site, protégé par Basic Auth)
api/insert.php     écriture dans le JSON (flock, tri alphabétique, anti-doublon)
data/              glossary.json (créé automatiquement), glossary.example.json
scripts/           déploiement et récupération du JSON de production
automation/        ajout de termes en ligne de commande (définition générée par Claude)
```

## Installation locale

```bash
php -S localhost:8000
```

Au premier lancement, copier l'exemple pour avoir des données :

```bash
cp data/glossary.example.json data/glossary.json
```

`data/glossary.json` n'est pas versionné : la version de production fait foi.

## Déploiement (SFTP via lftp)

1. Copier la configuration et la compléter :
   ```bash
   cp scripts/.env.example scripts/.env
   ```
   Variables : `FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`, `FTP_REMOTE_DIR` et `HTPASSWD_PATH` (chemin **absolu** du `.htpasswd` sur le serveur).
2. Créer le fichier `.htpasswd` (hors dépôt) et l'envoyer une fois à l'emplacement `HTPASSWD_PATH` :
   ```bash
   htpasswd -c .htpasswd mon_utilisateur
   ```
3. Déployer :
   ```bash
   cd scripts
   chmod +x deploy.sh
   ./deploy.sh
   ```

Le script génère le `.htaccess` avec `HTPASSWD_PATH` puis l'envoie. Il n'écrase jamais `.htpasswd` ni `data/glossary.json` sur le serveur.

Récupérer le JSON de production pour tester en local :

```bash
./pull-prod-json.sh
```

## API

```bash
curl -X POST https://wikito.example.com/api/insert.php \
  -u "utilisateur:mot_de_passe" \
  -H "Content-Type: application/json" \
  -d '{"title": "Docker", "description": "Plateforme permettant d'\''exécuter des applications dans des conteneurs."}'
```

Réponses : `{"success": true}` ou `{"success": false, "message": "..."}` (400 champs manquants, 409 doublon, 405 mauvaise méthode).

## Automatisation

Voir [automation/SETUP.md](automation/SETUP.md) pour ajouter des termes depuis le terminal, avec une définition générée par l'API Claude.

## Limites

- La protection anti-indexation décourage les robots respectueux des règles, elle ne garantit pas un blocage total.
- Les pages de lecture sont publiques : ne pas y stocker de contenu confidentiel.

## Licence

[MIT](LICENSE)
