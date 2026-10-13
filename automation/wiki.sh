#!/bin/bash
# Wikito Wrapper - Utilisation simple du wiki auto

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PYTHON_SCRIPT="$SCRIPT_DIR/wiki_pusher.py"

# Variables d'environnement (obligatoires, aucune valeur par défaut)
export WIKITO_API_URL="${WIKITO_API_URL:-}"
export WIKITO_USERNAME="${WIKITO_USERNAME:-}"
export WIKITO_PASSWORD="${WIKITO_PASSWORD:-}"

if [ -z "$WIKITO_API_URL" ] || [ -z "$WIKITO_USERNAME" ] || [ -z "$WIKITO_PASSWORD" ]; then
    echo "Définir WIKITO_API_URL, WIKITO_USERNAME et WIKITO_PASSWORD avant d'utiliser ce script." >&2
    exit 2
fi

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

usage() {
    echo -e "${BLUE}📚 Wikito automatisé${NC}"
    echo ""
    echo "Usage: $0 [OPTION] [TERME]"
    echo ""
    echo "Options:"
    echo "  -i, --interactive    Mode interactif"
    echo "  -b, --batch FILE     Traiter un fichier de termes (un par ligne)"
    echo "  -h, --help          Afficher cette aide"
    echo ""
    echo "Exemples:"
    echo "  $0 'Cloud Computing'              # Ajouter un terme"
    echo "  $0 --interactive                  # Mode interactif"
    echo "  $0 --batch termes.txt             # Traiter termes.txt"
}

batch_mode() {
    local file=$1

    if [ ! -f "$file" ]; then
        echo -e "${RED}❌ Fichier non trouvé: $file${NC}"
        exit 1
    fi

    echo -e "${BLUE}📄 Traitement du fichier: $file${NC}"
    echo ""

    local count=0
    while IFS= read -r terme; do
        # Ignorer les lignes vides et les commentaires
        [[ -z "$terme" || "$terme" =~ ^# ]] && continue

        echo -e "${BLUE}➤ Traitement: $terme${NC}"
        python3 "$PYTHON_SCRIPT" "$terme"
        echo ""

        count=$((count + 1))
        # Petit délai pour ne pas surcharger l'API
        sleep 1
    done < "$file"

    echo -e "${GREEN}✅ Traitement terminé: $count termes ajoutés${NC}"
}

# Vérifier si le script Python existe
if [ ! -f "$PYTHON_SCRIPT" ]; then
    echo -e "${RED}❌ Script Python non trouvé: $PYTHON_SCRIPT${NC}"
    exit 1
fi

# Gestion des arguments
if [ $# -eq 0 ]; then
    usage
    exit 0
fi

case "$1" in
-h | --help)
    usage
    exit 0
    ;;
-i | --interactive)
    python3 "$PYTHON_SCRIPT" --interactive
    ;;
-b | --batch)
    if [ -z "$2" ]; then
        echo -e "${RED}❌ Fichier requis pour --batch${NC}"
        exit 1
    fi
    batch_mode "$2"
    ;;
*)
    # Mode simple: traiter l'argument comme un terme
    python3 "$PYTHON_SCRIPT" "$1"
    ;;
esac
