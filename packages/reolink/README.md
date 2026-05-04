# Package Reolink - Gestion des caméras de sécurité

## Description
Ce package permet de contrôler les caméras Reolink pour armer/désarmer la surveillance.

## Fonctions disponibles

### `camera_arm()`
Active la surveillance de la caméra (Mode Away)
- Active les notifications push
- Active la détection de mouvement

**Retour :**
```php
[
    'action' => 'arm',
    'success' => true|false,
    'response' => 'JSON response'
]
```

### `camera_disarm()`
Désactive la surveillance de la caméra (Mode Home)
- Désactive les notifications push
- Désactive la détection de mouvement

**Retour :**
```php
[
    'action' => 'disarm',
    'success' => true|false,
    'response' => 'JSON response'
]
```

## Utilisation

```php
require_once __DIR__ . "/reolink.inc.php";

// Armer la caméra
$result = camera_arm();
if ($result['success']) {
    echo "Caméra armée avec succès\n";
} else {
    echo "Erreur: " . $result['response'] . "\n";
}

// Désarmer la caméra
$result = camera_disarm();
if ($result['success']) {
    echo "Caméra désarmée avec succès\n";
}
```

## Tests

```bash
# Test complet
php packages/reolink/tests/demo.inc.php

# Test de débogage
php packages/reolink/tests/test_debug.php

# Vérifier les capacités de la caméra
php packages/reolink/tests/test_get_abilities.php
```

## Configuration

Les identifiants de connexion sont actuellement codés en dur dans `reolink.inc.php` :
- IP : 192.168.1.244
- Utilisateur : admin
- Mot de passe : L1mp3rm@n3nce

## Commandes API Reolink supportées

D'après les tests, votre modèle de caméra supporte :
- ✅ `SetPush` - Notifications push
- ✅ `SetMdAlarm` - Détection de mouvement

Commandes NON supportées par ce modèle :
- ❌ `SetEmail` - Notifications email
- ❌ `SetRec` - Enregistrement
- ❌ `SetFtp` - Upload FTP
- ❌ `SetAudioAlarm` - Alarme sonore
- ❌ `SetAiTrack` - Suivi IA
- ❌ `SetAiCfg` - Configuration IA (personnes, animaux, véhicules)

## Corrections apportées

1. **Payload simplifié** : Utilisation uniquement des commandes supportées par le modèle
2. **Validation améliorée** : Méthode `isSuccess()` pour vérifier les codes de retour
3. **Retour structuré** : Array avec action, succès et réponse complète
4. **Logs ajoutés** : Journalisation via `Helpers::log()`
5. **Gestion d'erreurs** : Fallback en cas d'échec de connexion

## Notes techniques

L'API Reolink utilise un système de batch pour envoyer plusieurs commandes en une seule requête.
Chaque commande retourne un code :
- `code: 0` = Succès
- `code: 1` = Erreur (avec détails dans le champ `error`)

Les erreurs courantes :
- `rspCode: -9` = Commande non supportée
- `rspCode: -4` = Erreur de paramètres
