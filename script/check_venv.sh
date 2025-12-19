#!/usr/bin/env bash
# Vérification et auto-réparation de l'environnement Python virtuel (.venv)
# Exécute la logique complète (création/installation) pour chaque requirements.txt trouvé.

# --- Configuration Globale ---
# Dossiers à inspecter : le dossier racine (.) et les sous-dossiers de packages
SEARCH_DIRS="."
if [ -d "packages" ]; then
    # Ajoute les sous-dossiers directs de packages (ex: packages/huesync)
    for SUBDIR in packages/*/; do
        if [ -d "$SUBDIR" ]; then
            SEARCH_DIRS="$SEARCH_DIRS $SUBDIR"
        fi
    done
fi

echo "=== Démarrage de la vérification des environnements virtuels Python ==="

# Variable pour suivre l'état global
GLOBAL_STATUS=0

# Boucle sur les répertoires trouvés
for DIR in $SEARCH_DIRS; do
    # Normalise le chemin
    DIR=$(echo "$DIR" | sed 's/\/\+$//')

    # Vérifie si le fichier requirements.txt existe dans le répertoire actuel
    REQ_FILE="$DIR/requirements.txt"
    VENV_DIR="$DIR/.venv"

    if [ -f "$REQ_FILE" ]; then
        echo -e "\n------------------------------------------------------------"
        echo "## 📦 Dépendances trouvées dans : $DIR"
        echo "------------------------------------------------------------"

        # 1. Créer l'environnement s'il n'existe pas
        if [ ! -d "$VENV_DIR" ]; then
            echo "[INFO] Aucun environnement trouvé, création de '$VENV_DIR' en cours..."
            python3 -m venv "$VENV_DIR" || {
                echo "[ERREUR] Impossible de créer l'environnement virtuel pour $DIR."
                GLOBAL_STATUS=1
                continue
            }
            echo "[OK] Environnement virtuel créé dans $VENV_DIR."
        else
            echo "[INFO] Environnement virtuel '$VENV_DIR' déjà existant."
        fi

        # 2. Activer l'environnement et exécuter les commandes à l'intérieur d'une sous-shell
        (
            # Utilise une sous-shell pour que l'activation (source) ne pollue pas l'environnement parent
            source "$VENV_DIR/bin/activate"

            if [ -z "$VIRTUAL_ENV" ]; then
                echo "[ERREUR] Impossible d'activer l'environnement virtuel de $DIR."
                exit 1 # Sort de la sous-shell
            fi

            PYTHON_VERSION=$(python --version 2>&1)
            echo "[INFO] Environnement actif : $VIRTUAL_ENV"
            echo "[INFO] Version Python utilisée : $PYTHON_VERSION"

            # 3. Installer les dépendances
            echo "[INFO] Vérification et installation des dépendances de $REQ_FILE..."
            # L'option --upgrade permet d'assurer que les versions sont correctes
            pip install -r "$REQ_FILE" --upgrade

            if [ $? -ne 0 ]; then
                echo "[ERREUR] Échec lors de l'installation des dépendances pour $DIR."
                exit 1 # Sort de la sous-shell
            fi
            echo "[OK] Toutes les dépendances sont installées pour $DIR."
        )

        # Vérifie si la sous-shell a retourné une erreur (statut non nul)
        if [ $? -ne 0 ]; then
            GLOBAL_STATUS=1
        fi
    else
        echo "[INFO] Aucun requirements.txt trouvé dans $DIR. Ignoré."
    fi
done

echo -e "\n=== Vérification globale terminée ==="

if [ $GLOBAL_STATUS -ne 0 ]; then
    echo "[AVERTISSEMENT] Une ou plusieurs vérifications ont échoué."
    exit 1
else
    echo "[SUCCÈS] Tous les environnements et dépendances ont été vérifiés avec succès."
    exit 0
fi
