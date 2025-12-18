# Wake Up Call Plugin

Ce plugin permet de contrôler des appareils Chromecast pour jouer des médias d'appel de réveil ou d'alarme.

## Fonctionnalités

- Lecture de médias audio/vidéo sur Chromecast.
- Gestion de plusieurs appareils via alias IP.
- Persistance des connexions pour optimisation.
- Configuration flexible via JSON.

## Installation

1. Assurez-vous que Composer est installé.
2. Exécutez `composer install` pour installer les dépendances.

## Configuration

Modifiez le fichier `core/config/config.json` pour définir vos appareils et médias :

```json
{
    "db_path": "/tmp/nexus/wake_up_call",
    "port": "8009",
    "devices": {
        "cuisine": "192.168.1.90",
        "escalier": "192.168.1.74"
    },
    "media": {
        "siren": {
            "url": "http://example.com/siren.mp3",
            "type": "BUFFERED",
            "mime": "audio/mpeg"
        }
    }
}
```

## Utilisation

Utilisez les fonctions définies dans `core/php/wake_up_call.inc.php` :

- `wakeUpCall_bunny()` : Joue une vidéo de test.
- `wakeUpCall_siren()` : Joue une sirène d'alarme.
- `wakeUpCall_wakeUp()` : Joue une musique de réveil.
- `wakeUpCall_stop()` : Arrête la lecture.

Ou directement via la classe :

```php
use Nexus\Multimedia\WakeUpCall\WakeUpCall;

$cast = WakeUpCall::load('cuisine');
$cast->playMedia('siren');
```

## Tests

Exécutez les tests avec `composer test` ou `phpunit tests/`.

## Nettoyage du code

Utilisez `composer cs-fix` pour corriger le style du code.

## Architecture

- `WakeUpCall` : Classe principale étendant `Chromecast`.
- Persistance des instances par IP.
- Configuration JSON pour flexibilité.
- Bibliothèque Chromecast tierce dans `3rdparty/cast/`.

## Dépendances

- Bibliothèque Chromecast tierce.
- PHPUnit pour les tests.
- PHP-CS-Fixer pour le style.