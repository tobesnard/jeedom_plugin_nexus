# Nexus Project - Jeedom Expansion Framework

[![Jeedom V4.2+](https://img.shields.io/badge/Jeedom-V4.2+-blue.svg)](https://www.jeedom.com)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4+-777bb4.svg)](https://www.php.net)
[![Python 3.10+](https://img.shields.io/badge/Python-3.10+-3776ab.svg)](https://www.python.org)

**Nexus** est une infrastructure logicielle modulaire conçue pour étendre les capacités de votre installation Jeedom via une architecture robuste basée sur des packages spécialisés.

## Caractéristiques Principales
- **Architecture Modulaire** : 17 packages indépendants (PSR-4) pour une maintenance et une extensibilité optimales.
- **Moteur de Règles (Interpreter)** : Système de logique avancée basé sur des patterns de conception.
- **Orchestration IA** : Client unifié pour l'intégration de services d'intelligence artificielle.
- **Gestion Énergétique** : Suivi détaillé de la consommation électrique et calcul des coûts.
- **Écosystème Connecté** : Intégrations natives pour Philips Hue, TV, PS5, Chromecast, et Hydrao.
- **Qualité de Code** : Typage strict PHP 7.4+, tests unitaires (PHPUnit) et respect des standards PSR.

## Structure du Projet
Le projet est divisé en packages autonomes situés dans le dossier `packages/` :
- `ai_client` : Orchestration des clients IA.
- `energy_consumption` : Calcul de consommation et gestion de contrats.
- `interpreter` : Cœur de l'interpréteur de commandes et règles.
- `philips_hue` / `philips_tv` : Contrôle des équipements Philips.
- `wake_up_call` : Gestion multicast pour les réveils via Chromecast.
- ... et bien plus.

## Installation & Développement
Le plugin utilise Composer pour gérer ses dépendances et l'autoloading.

1. **Installation des dépendances** :
   ```bash
   composer install
   ```
2. **Configuration de l'environnement** :
   ```bash
   php script/setup_env.php
   ```
3. **Tests** :
   Le projet inclut une suite complète de tests PHPUnit. Vous pouvez les lancer via npm :
   ```bash
   npm run test-all
   ```

## Technologies
- **PHP** (Core & Business Logic)
- **Python** (IoT & Background tasks)
- **Composer** (Dependency Management)
- **Guzzle** (HTTP Client)
- **PHPUnit** (Testing Framework)

## Auteur
- **Tony**

---
*Ce projet fait partie du plugin Nexus pour Jeedom.*



