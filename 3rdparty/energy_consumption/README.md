# Energy Consumption (mini library)

Ce dépôt contient une petite bibliothèque pour calculer la consommation électrique
et estimer les coûts à partir de relevés journaliers (intégration Jeedom).

Principales composantes:

- `core/class/` : implémentations principales (`Consumption`, `Contract`, `ContractFactory`, `EnergyFacade`).
- `core/class/Service/KwhReading` : interface `IKwhReading`, impl. Jeedom (`JeedomKwhReading`) et wrapper `JeedomClient`.
- `core/tests` : scripts de démonstration et tests unitaires PHPUnit.

Utilisation rapide (CLI de démo):

```bash
php core/tests/core.class.test.php
php core/tests/energy_consumption.inc.test.php
```

Installer les dépendances de développement (PHPUnit):

```bash
composer require --dev phpunit/phpunit
```

Exécuter la suite PHPUnit:

```bash
vendor/bin/phpunit --configuration phpunit.xml
```

Notes:
- `JeedomKwhReading` est découplé de l'API Jeedom via `JeedomClient` pour faciliter le mock en tests.
- Pour l'intégration complète, ce paquet s'attend à tourner dans un environnement Jeedom (accès aux classes `cmd`/`history`).
