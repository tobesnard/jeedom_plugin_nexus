<?php

namespace Nexus\Security\Camera;

/**
 * ReolinkSecurityManager
 * 
 * Gestionnaire expert pour caméras Reolink (API V20).
 * Supporte l'armement, le désarmement, le PTZ et la configuration IA.
 */
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
    // Actions Principales
    // =========================================================================

    public function disarmAll(bool $includeAudio = true): array
    {
        $result = $this->sendBatchRequest($this->buildPayload(0, $includeAudio));
        return [
            'action'   => 'disarm',
            'success'  => $this->isSuccess($result),
            'response' => json_decode($result, true),
        ];
    }

    public function armAll(bool $includeAudio = true): array
    {
        $result = $this->sendBatchRequest($this->buildPayload(1, $includeAudio));
        return [
            'action'   => 'arm',
            'success'  => $this->isSuccess($result),
            'response' => json_decode($result, true),
        ];
    }

    // =========================================================================
    // Générateurs de Payloads (Modulaires)
    // =========================================================================

    private function buildPayload(int $status, bool $includeAudio = true): array
    {
        $payload = [
            $this->getPushPayload($status),
            $this->getRecPayload($status),
            $this->getEmailPayload($status),
        ];

        if ($includeAudio) {
            $payload[] = $this->getAudioAlarmPayload($status);
            $payload[] = $this->getBuzzerPayload($status);
        }

        $payload[] = $this->getAiCfgPayload();

        return $payload;
    }

    private function getPushPayload(int $status): array
    {
        $table = str_repeat((string) $status, 168);
        return [
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
        ];
    }

    private function getRecPayload(int $status): array
    {
        $table = str_repeat((string) $status, 168);
        return [
            "cmd"   => "SetRecV20",
            "param" => [
                "Rec" => [
                    "channel"        => $this->channel,
                    "enable"         => $status,
                    "overwrite"      => 1,
                    "postRec"        => "1 Minute",
                    "preRec"         => $status,
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
        ];
    }

    private function getEmailPayload(int $status): array
    {
        $table = str_repeat((string) $status, 168);
        return [
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
        ];
    }

    private function getAudioAlarmPayload(int $status): array
    {
        $table = str_repeat((string) $status, 168);
        $table_false = str_repeat("0", 168);
        return [
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
        ];
    }

    private function getBuzzerPayload(int $status): array
    {
        $table = str_repeat((string) $status, 168);
        $table_false = str_repeat("0", 168);
        return [
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
        ];
    }

    private function getAiCfgPayload(): array
    {
        return [
            "cmd"    => "SetAiCfg",
            "action" => 0,
            "param"  => [
                "channel"      => $this->channel,
                "aiTrack"      => 4,
                "AiDetectType" => ["people" => 1, "vehicle" => 0, "dog_cat" => 1, "face" => 0],
                "trackType"    => ["people" => 1, "vehicle" => 0, "dog_cat" => 1, "face" => 0]
            ]
        ];
    }

    // =========================================================================
    // PTZ
    // =========================================================================

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

        $result = $this->sendBatchRequest($payload, 'PtzCtrl');

        return [
            'action'   => "ptz{$op}",
            'success'  => $this->isSuccess($result),
            'response' => json_decode($result, true),
        ];
    }

    public function moveUp(int $speed = 32, ?int $channel = null): array { return $this->ptzControl('Up', $speed, $channel); }
    public function moveDown(int $speed = 32, ?int $channel = null): array { return $this->ptzControl('Down', $speed, $channel); }
    public function moveLeft(int $speed = 32, ?int $channel = null): array { return $this->ptzControl('Left', $speed, $channel); }
    public function moveRight(int $speed = 32, ?int $channel = null): array { return $this->ptzControl('Right', $speed, $channel); }
    public function stopMove(?int $channel = null): array { return $this->ptzControl('Stop', 0, $channel); }

    // =========================================================================
    // Getters d'état
    // =========================================================================

    public function getPushStatus(): bool { return $this->fetchBinaryStatus("GetPushV20", "Push"); }
    public function getMailStatus(): bool { return $this->fetchBinaryStatus("GetEmailV20", "Email"); }
    public function getRecStatus(): bool { return $this->fetchBinaryStatus("GetRecV20", "Rec"); }
    public function getSirenStatus(): bool { return $this->fetchBinaryStatus("GetAudioAlarmV20", "Audio"); }
    public function getBuzzerStatus(): bool { return $this->fetchBinaryStatus("GetBuzzerAlarmV20", "Buzzer"); }

    public function getSpotlightStatus(): bool
    {
        $payload = [["cmd" => "GetWhiteLed", "param" => ["channel" => $this->channel]]];
        $response = json_decode($this->sendBatchRequest($payload), true);
        return (isset($response[0]['value']['WhiteLed']['state']) && (int)$response[0]['value']['WhiteLed']['state'] === 1);
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function sendBatchRequest(array $payload, string $cmd = 'Batch'): string
    {
        $url = $this->baseUrl . "?cmd={$cmd}&user={$this->user}&password={$this->password}";
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return json_encode([['code' => 1, 'error' => ['detail' => $err]]]);
        }
        curl_close($ch);
        return $result ?: json_encode([['code' => 1, 'error' => ['detail' => 'Empty']]]);
    }

    private function isSuccess(string $jsonResponse): bool
    {
        $response = json_decode($jsonResponse, true);
        if (!is_array($response)) return false;
        foreach ($response as $res) {
            if (isset($res['code']) && $res['code'] !== 0) {
                if (isset($res['error']['rspCode']) && $res['error']['rspCode'] === -9) continue;
                return false;
            }
        }
        return true;
    }

    private function fetchBinaryStatus(string $cmd, string $key): bool
    {
        $payload = [["cmd" => $cmd, "param" => ["channel" => $this->channel]]];
        $response = json_decode($this->sendBatchRequest($payload), true);
        return (isset($response[0]['value'][$key]['enable']) && (bool)$response[0]['value'][$key]['enable']);
    }
}