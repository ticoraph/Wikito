# 📚 Wikito automatisé - Setup

## Installation

### 1. Installer les dépendances
```bash
pip install -r requirements.txt
```

### 2. Configurer les variables d'environnement (optionnel)
```bash
export WIKITO_API_URL="https://wikito.example.com/api/insert.php"
export WIKITO_USERNAME="votre_utilisateur"
export WIKITO_PASSWORD="votre_mot_de_passe"
export ANTHROPIC_API_KEY="sk-..."  # votre clé Anthropic
```

## Utilisation

### Mode simple (un terme à la fois)
```bash
python wiki_pusher.py "Kubernetes"
```

**Résultat:**
```
⏳ Explication du terme: 'Cloud Computing'
✏️  Explication générée:
   Cloud Computing est la fourniture de services informatiques (serveurs, stockage, 
   logiciels) via Internet plutôt que sur des serveurs locaux.

✅ Succès ! Terme 'Cloud Computing' envoyé à Wikito
```

### Mode interactif (dialogue continu)
```bash
python wiki_pusher.py --interactive
```

Vous pouvez alors:
- Taper `add` pour ajouter un terme
- Taper `quit` pour quitter
- Communiquer avec Claude pour affiner vos définitions

### Options personnalisées
```bash
python wiki_pusher.py "GCP" \
  --api-url "https://monapi.com/endpoint" \
  --username "mon_user" \
  --password "mon_pass"
```

## Intégration Claude Desktop

### Méthode 1: Hotkey Workflow (recommandé)
Installez le plugin Cowork et configurez un raccourci clavier pour exécuter le script.

### Méthode 2: Alias Shell
Ajoutez à votre `.bashrc` ou `.zshrc`:
```bash
alias wiki='python /chemin/vers/wiki_pusher.py'
```

Puis utilisez:
```bash
wiki "Votre terme"
wiki -i  # mode interactif
```

### Méthode 3: Claude Desktop Plugin (avancé)
Créez un MCP (Model Context Protocol) pour exposer cette fonctionnalité directement dans Claude.

## Structure API

L'API `wikito.example.com/api/insert.php` reçoit:

```bash
curl -X POST https://wikito.example.com/api/insert.php \
  -u "votre_utilisateur:votre_mot_de_passe" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "GCP Google Cloud Platform",
    "description": "GCP est la suite de services de cloud computing de Google permettant de développer, déployer et héberger des applications."
  }'
```

## Exemples

### Ajouter plusieurs termes
```bash
#!/bin/bash
TERMES=("API REST" "Microservices" "DevOps" "CI/CD" "Kubernetes")

for terme in "${TERMES[@]}"; do
  python wiki_pusher.py "$terme"
  sleep 2  # pause entre les envois
done
```

### Depuis un fichier
```bash
#!/bin/bash
while IFS= read -r terme; do
  [ -z "$terme" ] && continue
  python wiki_pusher.py "$terme"
done < termes.txt
```

## Troubleshooting

| Problème | Solution |
|----------|----------|
| `ModuleNotFoundError: anthropic` | Installer: `pip install anthropic --break-system-packages` |
| `Erreur API 401` | Vérifier username/password |
| `Erreur API 404` | Vérifier l'URL API |
| `Timeout` | Vérifier la connexion Internet |

## Architecture

```
User Input (CLI / Claude)
         ↓
  wiki_pusher.py
         ↓
   [Claude API]  ← Génère explication 1 phrase
         ↓
  [HTTP Request] ← Envoie à Wikito
         ↓
   Wiki updaté ✅
```

---

**Questions?** Consultez le code source `wiki_pusher.py` - c'est du Python standard!
