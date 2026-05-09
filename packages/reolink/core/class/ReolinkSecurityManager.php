<?php

namespace Nexus\Security\Camera;

class ReolinkSecurityManager
{
    private string $ip;
    private string $user;
    private string $password;
    private string $baseUrl;
    private int $channel;

    public function __construct(string $ip, string $user, string $password, int $channel = 0)
    {
        $this->ip       = $ip;
        $this->user     = $user;
        $this->password = $password;
        $this->channel  = $channel;
        $this->baseUrl  = "http://{$this->ip}/cgi-bin/api.cgi";
    }

    // =========================================================================
    // Armement / Désarmement
    // =========================================================================

    public function disarmAll(): array
    {
        $result = $this->sendBatchRequest($this->buildPayload(0));
        return [
            'action'   => 'disarm',
            'success'  => $this->isSuccess($result),
            'response' => json_decode($result, true),
        ];
    }

    public function armAll(): array
    {
        $result = $this->sendBatchRequest($this->buildPayload(1));
        return [
            'action'   => 'arm',
            'success'  => $this->isSuccess($result),
            'response' => json_decode($result, true),
        ];
    }

    private function buildPayload(int $status): array
    {
        $table = str_repeat((string) $status, 168);
        $table_false = str_repeat("0", 168);
        $table_true = str_repeat("1", 168);

        return [
            // 1. Notifications Push
            [
                "cmd"   => "SetPushV20",
                "param" => [
                    "Push" => [
                        "channel"        => $this->channel,
                        "enable"         => $status,
                        "scheduleEnable" => 1,
                        "schedule"       => [
                            "channel" => $this->channel,
                            "table"   => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table,
                            ],
                        ],
                    ],
                ],
            ],
            // 2. Enregistrement V20
            [
                "cmd"   => "SetRecV20",
                "param" => [
                    "Rec" => [
                        "channel"        => $this->channel,
                        "enable"         => $status,
                        "overwrite"      => 1,
                        "postRec"        => "1 Minute",
                        "preRec"         => 0,
                        "saveDay"        => 7,
                        "scheduleEnable" => 1,
                        "schedule"       => [
                            "channel" => $this->channel,
                            "table"   => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table,
                                "TIMING"     => str_repeat("0", 168),
                            ],
                        ],
                    ],
                ],
            ],
            // 3. Email V20
            [
                "cmd"   => "SetEmailV20",
                "param" => [
                    "Email" => [
                        "channel"  => $this->channel,
                        "enable"   => $status,
                        "schedule" => [
                            "channel" => $this->channel,
                            "table"   => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table,
                            ],
                        ],
                    ],
                ],
            ],
            // 4. Sirène (Audio Alarm) V20
            [
                "cmd"   => "SetAudioAlarmV20",
                "param" => [
                    "Audio" => [
                        "channel"  => $this->channel,
                        "enable"   => $status,
                        "schedule" => [
                            "channel" => $this->channel,
                            "table"   => [
                                "AI_DOG_CAT" => $table_false,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table_false,
                                "MD"         => $table_false,
                            ],
                        ],
                    ],
                ],
            ],
            // 5. Buzzer Alarm V20
            [
                "cmd"   => "SetBuzzerAlarmV20",
                "param" => [
                    "Buzzer" => [
                        "channel"            => $this->channel,
                        "enable"             => $status,
                        "scheduleEnable"     => 1,
                        "diskErrorAlert"     => 0,
                        "diskFullAlert"      => 0,
                        "ipConflictAlert"    => 0,
                        "nvrDisconnectAlert" => 0,
                        "schedule"           => [
                            "channel" => $this->channel,
                            "table"   => [
                                "AI_DOG_CAT" => $table_false,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table_false,
                                "MD"         => $table_false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // =========================================================================
    // PTZ
    // =========================================================================

    /**
     * Pilote les mouvements PTZ de la caméra.
     *
     * @param string   $op      Opération : Up, Down, Left, Right, Stop, ZoomInc, ZoomDec
     * @param int      $speed   Vitesse de 1 à 64 (défaut : 32)
     * @param int|null $channel Channel cible. Si null, utilise $this->channel
     */
    public function ptzControl(string $op, int $speed = 32, ?int $channel = null): array
    {
        $payload = [
            [
                "cmd"   => "PtzCtrl",
                "param" => [
                    "channel" => $channel ?? $this->channel,
                    "op"      => $op,
                    "speed"   => $speed,
                ],
            ],
        ];

        // PtzCtrl doit être envoyé avec cmd=PtzCtrl dans l'URL, pas cmd=Batch
        $result = $this->sendBatchRequest($payload, 'PtzCtrl');

        return [
            'action'   => "ptz{$op}",
            'success'  => $this->isSuccess($result),
            'response' => json_decode($result, true),
        ];
    }

    public function moveUp(int $speed = 32, ?int $channel = null): array
    {
        return $this->ptzControl('Up', $speed, $channel);
    }

    public function moveDown(int $speed = 32, ?int $channel = null): array
    {
        return $this->ptzControl('Down', $speed, $channel);
    }

    public function moveLeft(int $speed = 32, ?int $channel = null): array
    {
        return $this->ptzControl('Left', $speed, $channel);
    }

    public function moveRight(int $speed = 32, ?int $channel = null): array
    {
        return $this->ptzControl('Right', $speed, $channel);
    }

    public function stopMove(?int $channel = null): array
    {
        return $this->ptzControl('Stop', 0, $channel);
    }

    /**
     * Zoom avant — cible le channel 1 (lentille téléphoto) par défaut
     */
    public function zoomInc(int $speed = 32, int $channel = 1): array
    {
        return $this->ptzControl('ZoomInc', $speed, $channel);
    }

    /**
     * Zoom arrière — cible le channel 1 (lentille téléphoto) par défaut
     */
    public function zoomDec(int $speed = 32, int $channel = 1): array
    {
        return $this->ptzControl('ZoomDec', $speed, $channel);
    }

    // =========================================================================
    // Getters d'état
    // =========================================================================

    public function getPushStatus(): bool
    {
        return $this->fetchBinaryStatus("GetPushV20", "Push");
    }

    public function getMailStatus(): bool
    {
        return $this->fetchBinaryStatus("GetEmailV20", "Email");
    }

    public function getRecStatus(): bool
    {
        return $this->fetchBinaryStatus("GetRecV20", "Rec");
    }

    public function getSirenStatus(): bool
    {
        return $this->fetchBinaryStatus("GetAudioAlarmV20", "Audio");
    }

    public function getBuzzerStatus(): bool
    {
        return $this->fetchBinaryStatus("GetBuzzerAlarmV20", "Buzzer");
    }

    public function getSpotlightStatus(): bool
    {
        $payload = [
            [
                "cmd"   => "GetWhiteLed",
                "param" => ["channel" => $this->channel],
            ],
        ];

        $response = json_decode($this->sendBatchRequest($payload), true);

        if (
            isset($response[0]['code'])
            && $response[0]['code'] === 0
            && isset($response[0]['value']['WhiteLed']['state'])
        ) {
            return (int) $response[0]['value']['WhiteLed']['state'] === 1;
        }

        return false;
    }

    // =========================================================================
    // Internals
    // =========================================================================

    /**
     * Envoi d'une requête POST vers l'API Reolink.
     *
     * @param array  $payload Tableau de commandes JSON
     * @param string $cmd     Commande dans l'URL (Batch, PtzCtrl, …)
     */
    private function sendBatchRequest(array $payload, string $cmd = 'Batch'): string
    {
        $url = $this->baseUrl . "?cmd={$cmd}&user={$this->user}&password={$this->password}";
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return json_encode([['code' => 1, 'error' => ['detail' => $error]]]);
        }
        curl_close($ch);
        return $result ?: json_encode([['code' => 1, 'error' => ['detail' => 'Empty response']]]);
    }

    private function isSuccess(string $jsonResponse): bool
    {
        $response = json_decode($jsonResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($response)) {
            return false;
        }

        foreach ($response as $cmdResult) {
            if (isset($cmdResult['code']) && $cmdResult['code'] !== 0) {
                if (isset($cmdResult['error']['rspCode'])) {
                    // On ignore -9 (not support)
                    if ($cmdResult['error']['rspCode'] === -9) {
                        continue;
                    }
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Exécute une commande GET et extrait l'état d'activation (enable)
     */
    private function fetchBinaryStatus(string $cmd, string $key): bool
    {
        $payload = [
            [
                "cmd"   => $cmd,
                "param" => ["channel" => $this->channel],
            ],
        ];

        $response = json_decode($this->sendBatchRequest($payload), true);

        if (
            isset($response[0]['code'])
            && $response[0]['code'] === 0
            && isset($response[0]['value'][$key]['enable'])
        ) {
            return (bool) $response[0]['value'][$key]['enable'];
        }

        return false;
    }
}