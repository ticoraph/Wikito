# Wikito

Mini wiki / glossaire en PHP **sans base de données** : toutes les entrées sont dans `data/glossary.json`. HTML/CSS/JS vanilla, pensé pour un hébergement mutualisé (SFTP uniquement). Pas de build, pas de framework, pas de tests automatisés. Voir [README.md](README.md) pour le détail.

## Principes à respecter

- **Mobile first** : le style de base vise le mobile (1 colonne, safe-areas iOS). Le desktop est un enrichissement via `@media (min-width: …)` : grille de petites cartes dès 700px, 8 colonnes dès 1400px.
- **Aucune dépendance** : pas de npm, pas de framework, pas de CDN. Rester en PHP/CSS/JS vanilla.
- **Discrétion** : le site ne doit pas être indexé (meta robots, `X-Robots-Tag` dans `.htaccess`, `robots.txt`, pas de sitemap). Toute nouvelle page HTML/PHP doit inclure la meta `noindex,nofollow,noarchive,nosnippet,noimageindex`.
- **Contenu en français** (`lang="fr"`, messages d'erreur, commentaires de l'UI).

## Structure

- `index.php` : lit `data/glossary.json`, affiche les cartes `.term-card` et le compteur.
- `add.php` : formulaire d'ajout, non lié depuis le site, protégé par Basic Auth.
- `api/insert.php` : écrit dans le JSON (`flock` exclusif, tri alphabétique insensible à la casse, anti-doublon sur le titre → 409). Accepte `$_POST` ou un corps JSON.
- `script.js` : recherche instantanée côté client (filtre `.term-card` via `style.display`) et envoi du formulaire vers `api/insert.php`.
- `style.css` : un seul fichier, variables CSS dans `:root`. Versionné dans les pages via `?v=<?= filemtime('style.css') ?>` (pas de cache à purger).
- `scripts/` : `deploy.sh` et `pull-prod-json.sh` (lftp/SFTP), config dans `scripts/.env` (non versionné).
- `.github/workflows/deploy.yml` : déploie sur OVH à chaque push sur `main` (lance `scripts/deploy.sh` avec les secrets GitHub `FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`, `FTP_REMOTE_DIR`, `HTPASSWD_PATH`). Un push sur `main` = mise en production.
- `automation/` : `wiki_pusher.py` / `wiki.sh` génèrent une définition d'une phrase avec l'API Claude puis la POST sur `api/insert.php`. Voir `automation/SETUP.md`.

## Commandes

```bash
php -S localhost:8000                                   # serveur local (depuis la racine, pas depuis scripts/)
cp data/glossary.example.json data/glossary.json        # données de départ en local
cd scripts && ./deploy.sh                               # déployer (doit être lancé depuis scripts/)
cd scripts && ./pull-prod-json.sh                       # récupérer le JSON de prod
```

Attention : en local avec `php -S`, le Basic Auth du `.htaccess` n'est pas appliqué.

## Pièges

- `data/glossary.json` n'est **pas versionné** et **la production fait foi**. Ne jamais l'écraser en déployant : `deploy.sh` l'exclut volontairement, de même que `.htpasswd`, `README.md`, `scripts/` et `automation/`.
- `.htaccess` contient le placeholder `__HTPASSWD_PATH__` : `deploy.sh` le remplace puis l'envoie séparément. Ne pas le déployer tel quel.
- Ne jamais committer `scripts/.env` ni `.htpasswd` (identifiants SFTP / Basic Auth).
- Les termes sont échappés avec `htmlspecialchars` + `nl2br` à l'affichage ; conserver cet échappement si on touche au rendu.
- Le JSON est un tableau d'objets `{"title": "...", "description": "..."}` ; ne pas changer ce format sans adapter `index.php`, `api/insert.php` et `wiki_pusher.py`.
- `script.js` remet `display = 'block'` sur les cartes filtrées : compatible avec la grille tant que `.term-card` reste un enfant direct de `.glossary-list`.
