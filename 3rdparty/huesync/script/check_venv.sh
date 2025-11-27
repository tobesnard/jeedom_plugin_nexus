#!/usr/bin/env bash
# Vérification et auto-réparation de l'environnement Python virtuel (.venv)

# --- Configuration ---
VENV_DIR=".venv"
REQUIREMENTS_FILE="requirements.txt"

echo "=== Vérification de l'environnement virtuel Python ==="

# 1. Créer l'environnement s'il n'existe pas
if [ ! -d "$VENV_DIR" ]; then
    echo "[INFO] Aucun environnement trouvé, création en cours..."
    python3 -m venv "$VENV_DIR" || {
        echo "[ERREUR] Impossible de créer l'environnement virtuel."
        exit 1
    }
    echo "[OK] Environnement virtuel créé dans $VENV_DIR."
fi

# 2. Activer l'environnement
if [ -z "$VIRTUAL_ENV" ]; then
    echo "[INFO] Activation de l'environnement virtuel..."
    source "$VENV_DIR/bin/activate"
    if [ -z "$VIRTUAL_ENV" ]; then
        echo "[ERREUR] Impossible d'activer l'environnement virtuel."
        exit 1
    fi
fi
echo "[OK] Environnement virtuel actif : $VIRTUAL_ENV"

# 3. Vérifier la version de Python
PYTHON_VERSION=$(python --version 2>&1)
echo "[INFO] Version Python utilisée : $PYTHON_VERSION"

# 4. Installer les dépendances si nécessaire
if [ ! -f "$REQUIREMENTS_FILE" ]; then
    echo "[AVERTISSEMENT] Aucun fichier $REQUIREMENTS_FILE trouvé."
    echo "Utilise 'pip freeze > requirements.txt' pour en générer un."
else
    echo "[INFO] Vérification et installation des dépendances..."
    pip install -r "$REQUIREMENTS_FILE"
    if [ $? -ne 0 ]; then
        echo "[ERREUR] Échec lors de l'installation des dépendances."
        exit 1
    fi
    echo "[OK] Toutes les dépendances sont installées."
fi

echo "=== Vérification terminée avec succès ==="
exit 0
