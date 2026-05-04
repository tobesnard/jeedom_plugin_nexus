<?php

namespace Nexus\Security\Camera;

class ReolinkSecurityManager
{
    private string $ip;
    private string $user;
    private string $password;
    private string $baseUrl;

    public function __construct(string $ip, string $user, string $password)
    {
        $this->ip = $ip;
        $this->user = $user;
        $this->password = $password;
        $this->baseUrl = "http://{$this->ip}/cgi-bin/api.cgi";
    }

    public function disarmAll(): array
    {
        $result = $this->sendBatchRequest($this->buildPayload(0));
        return [
            'action' => 'disarm',
            'success' => $this->isSuccess($result),
            'response' => json_decode($result, true)
        ];
    }

    public function armAll(): array
    {
        $result = $this->sendBatchRequest($this->buildPayload(1));
        return [
            'action' => 'arm',
            'success' => $this->isSuccess($result),
            'response' => json_decode($result, true)
        ];
    }

    private function buildPayload(int $status): array
    {
        $table = str_repeat((string)$status, 168);
        $timingTable = ($status === 1) ? str_repeat("0", 168) : str_repeat("0", 168); // Souvent maintenu à 0 pour éviter le continu

        return [
            // 1. Notifications Push
            [
                "cmd" => "SetPushV20",
                "param" => [
                    "Push" => [
                        "channel" => 0,
                        "enable" => $status,
                        "scheduleEnable" => 1,
                        "schedule" => [
                            "channel" => 0,
                            "table" => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table
                            ]
                        ]
                    ]
                ]
            ],
            // 2. Enregistrement V20 (Neutralise l'écriture SD)
            [
                "cmd" => "SetRecV20",
                "param" => [
                    "Rec" => [
                        "channel" => 0,
                        "enable" => $status,
                        "overwrite" => 1,
                        "postRec" => "1 Minute",
                        "preRec" => 0,
                        "saveDay" => 7,
                        "scheduleEnable" => 1,
                        "schedule" => [
                            "channel" => 0,
                            "table" => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table,
                                "TIMING"     => "000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000"
                            ]
                        ]
                    ]
                ]
            ],

            // 3. Email V20 (Neutralise les envois de mail)
            [
                "cmd" => "SetEmailV20",
                "param" => [
                    "Email" => [
                        "channel" => 0,
                        "enable" => $status,
                        "schedule" => [
                            "channel" => 0,
                            "table" => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table
                            ]
                        ]
                    ]
                ]
            ],

            // 2. Sirène (Audio Alarm) V20
            [
                "cmd" => "SetAudioAlarmV20",
                "param" => [
                    "Audio" => [
                        "channel" => 0,
                        "enable" => $status,
                        "schedule" => [
                            "channel" => 0,
                            "table" => [
                                "AI_DOG_CAT" => $table,
                                "AI_PEOPLE"  => $table,
                                "AI_VEHICLE" => $table,
                                "MD"         => $table
                            ]
                        ]
                    ]
                ]
            ],

            // 7. Projecteurs (WhiteLed)
            [
                "cmd" => "SetWhiteLed",
                "param" => [
                    "WhiteLed" => [
                        "channel" => 0,
                        "mode" => $status === 1 ? 1 : 0, // 1: Auto (intelligent), 0: Fermé/Off
                        "state" => $status,
                        "bright" => 100,
                        "wlAiDetectType" => [
                            "dog_cat" => 0,
                            "people" => $status, // Active la lumière sur détection humaine si armé
                            "vehicle" => 0
                        ],
                        "LightingSchedule" => [
                            "StartHour" => 18,
                            "StartMin" => 0,
                            "EndHour" => 6,
                            "EndMin" => 0
                        ]
                    ]
                ]
            ],

            // 4. Détection de mouvement (Legacy/MD)
            [
                "cmd" => "SetMdAlarm",
                "param" => [
                    "MdAlarm" => [
                        "channel" => 0,
                        "enable" => $status,
                        "schedule" => ["table" => $table]
                    ]
                ]
            ]
        ];
    }

    private function sendBatchRequest(array $payload): string
    {
        $url = $this->baseUrl . "?cmd=Batch&user={$this->user}&password={$this->password}";
        $ch = curl_init($url);
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
                    $err = $cmdResult['error']['rspCode'];
                    // On ignore -9 (not support)
                    if ($err === -9) {
                        continue; 
                    }
                }
                return false;
            }
        }
        return true;
    }
}