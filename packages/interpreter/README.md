# Interpréteur de Règles Jeedom

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL%203.0+-green.svg)](LICENSE)
[![Quality](https://img.shields.io/badge/Quality-Refactored-brightgreen.svg)](#)

Un système d'interprétation de règles flexible et extensible pour Jeedom, implémentant le pattern **Interpreter** du Gang of Four avec les meilleures pratiques PHP 7.4+.

## 🚀 Fonctionnalités

- **Architecture Pattern GoF** : Implémentation propre du pattern Interpreter
- **Types stricts PHP 7.4+** : Code moderne avec déclarations de types
- **Gestion d'erreurs robuste** : Exceptions typées et logging complet
- **Cache intelligent** : Optimisation des performances avec mise en cache
- **Tests unitaires** : Couverture de tests complète avec PHPUnit
- **Documentation PHPDoc** : Documentation complète pour toutes les classes
- **Validation avancée** : Validation des expressions et paramètres
- **Debug et profiling** : Outils de débogage intégrés

## 📋 Prérequis

- PHP 7.4 ou supérieur
- Extension JSON
- Jeedom Core (pour l'intégration)
- Composer (pour la gestion des dépendances)

## 🛠 Installation

### Via Composer

```bash
composer require jeedom/rule-interpreter
```

### Installation manuelle

1. Clonez le repository :
```bash
git clone https://github.com/jeedom/rule-interpreter.git
cd rule-interpreter
```

2. Installez les dépendances :
```bash
composer install
```

## 📖 Utilisation

### Exemple de base

```php
<?php

use Interpreter\Context\RuleContext;
use Interpreter\Expression\Terminal\LiteralExpression;
use Interpreter\Application\Services\JeedomCmdService;

// Création du contexte avec service Jeedom
$cmdService = new JeedomCmdService();
$context = new RuleContext();

// Création d'une expression littérale
$expression = new LiteralExpression(42);

// Interprétation
$result = $expression->interpret($context);
echo $result; // Affiche : 42
```

### Expressions supportées

#### Expressions terminales
- **LiteralExpression** : Valeurs constantes (nombres, chaînes, booléens)
- **CmdByIdExpression** : Commandes par ID
- **CmdByStringExpression** : Commandes par chaîne

#### Expressions logiques
- **AndExpression** : Opérateur ET logique
- **OrExpression** : Opérateur OU logique
- **NotExpression** : Opérateur NON logique

#### Expressions de comparaison
- **EqExpression** : Égalité (==)
- **NeExpression** : Inégalité (!=)
- **GtExpression** : Supérieur (>)
- **LtExpression** : Inférieur (<)
- **GeExpression** : Supérieur ou égal (>=)
- **LeExpression** : Inférieur ou égal (<=)

### Gestion des variables

```php
$context = new RuleContext(true); // Mode debug activé

// Définir des variables
$context->set('temperature', 22.5);
$context->set('mode', 'auto');

// Récupérer des variables
$temp = $context->get('temperature'); // 22.5
$mode = $context->get('mode', 'manual'); // 'auto'

// Vérifier l'existence
if ($context->has('temperature')) {
    echo "Température définie";
}
```

### Déclenchement d'événements

```php
// Déclencher un événement
$context->triggerEvent('alarm_triggered', ['level' => 'high']);

// Vérifier les événements
$events = $context->getTriggeredEvents();
foreach ($events as $event) {
    echo "Événement : {$event['name']} à {$event['timestamp']}\n";
}
```

## 🔧 Développement

### Lancement des tests

```bash
# Tests unitaires
composer test

# Tests avec couverture
composer test-coverage

# Analyse statique
composer analyse

# Vérification du style de code
composer cs-check

# Correction automatique du style
composer cs-fix

# Tous les contrôles qualité
composer quality
```

### Structure du projet

```
src/
├── Application/
│   └── Services/           # Services d'application
│       ├── ICmdService.php      # Interface de service
│       ├── JeedomCmdService.php # Implémentation Jeedom
│       └── CmdServiceMop.php    # Service mock
├── Context/
│   └── RuleContext.php     # Contexte d'exécution
├── Expression/
│   ├── Expression.php      # Interface principale
│   ├── AbstractExpression.php # Classe de base
│   ├── NonTerminal/        # Expressions composites
│   │   ├── Action/             # Actions (events, exec, log)
│   │   ├── Comparison/         # Comparaisons
│   │   └── Logical/            # Opérateurs logiques
│   └── Terminal/           # Expressions terminales
│       ├── LiteralExpression.php    # Valeurs littérales
│       ├── CmdByIdExpression.php    # Commandes par ID
│       └── CmdByStringExpression.php # Commandes par string
└── Parser/
    └── BashRuleParser.php  # Analyseur syntaxique
```

## 🧪 Tests

Le projet inclut une suite de tests complète :

```bash
# Tests unitaires complets
./vendor/bin/phpunit

# Tests avec rapport de couverture
./vendor/bin/phpunit --coverage-html coverage/

# Tests d'une classe spécifique
./vendor/bin/phpunit tests/Expression/Terminal/LiteralExpressionTest.php
```

## 📊 Qualité du code

### Analyse statique avec PHPStan

```bash
./vendor/bin/phpstan analyse src --level max
```

### Style de code PSR-12

```bash
# Vérification
./vendor/bin/phpcs src --standard=PSR12

# Correction automatique
./vendor/bin/php-cs-fixer fix src
```

## 🔍 Debug et profiling

### Mode debug

```php
$context = new RuleContext(true); // Active le debug

// Les opérations seront loggées automatiquement
$context->set('var', 'value');
$context->triggerEvent('event');

// Rapport détaillé
$report = $context->getDetailedReport();
print_r($report);
```

### Statistiques du service de commandes

```php
$service = new JeedomCmdService();

// ... utilisation du service ...

// Statistiques
$stats = $service->getStats();
echo "Exécutions : {$stats['executions']}\n";
echo "Cache hits : {$stats['cache_hits']}\n";
echo "Taux de cache : {$stats['cache_hit_ratio']}%\n";
```

## 🤝 Contribution

1. Fork le projet
2. Créez une branche feature (`git checkout -b feature/amazing-feature`)
3. Committez vos changements (`git commit -m 'Add amazing feature'`)
4. Push vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

### Guidelines

- Respectez PSR-12 pour le style de code
- Ajoutez des tests pour toute nouvelle fonctionnalité
- Documentez avec PHPDoc
- Utilisez les types stricts PHP 7.4+
- Maintenez un niveau PHPStan maximum

## 📄 Licence

Ce projet est sous licence GPL 3.0+. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🏷️ Versions

- **1.0.0** - Version initiale refactorisée avec PHP 7.4+
  - Architecture pattern GoF Interpreter
  - Types stricts et documentation complète
  - Cache et optimisations
  - Tests unitaires complets
  - Outils de qualité intégrés

## 📞 Support

- [Issues GitHub](https://github.com/jeedom/core/issues)
- [Documentation Jeedom](https://doc.jeedom.com)
- [Forum Communauté](https://community.jeedom.com)

## 👥 Auteurs

- **Tony <tobesnard@gmail.com>** - *Développement initial et refactorisation*

## 🙏 Remerciements

- Gang of Four pour le pattern Interpreter
- La communauté PHP pour les meilleures pratiques
- L'équipe Jeedom pour l'écosystème
