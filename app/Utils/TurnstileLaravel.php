<?php

namespace App\Utils;

class TurnstileLaravel
{
    public function validate(string $response): array
    {
        $recaptchaKey = config('v2board.recaptcha_key');

        if (empty($recaptchaKey)) {
            return [
                'status' => 0,
                'error' => '未找到 Turnstile 密钥'
            ];
        }

        try {
            $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
            $data = [
                'secret' => $recaptchaKey,
                'response' => $response
            ];
    
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POSTFIELDS => json_encode($data),
            ]);
    
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
        } catch (Exception $e) {
            return [
                'status' => 0,
                'error' => '未知错误'
            ];
        }

        if ($result === false) {
            return [
                'status' => 0,
                'error' => '与 Turnstile 通信发生错误'
            ];
        }

        $json = json_decode($result);
        if (isset($json->success) && $json->success) {
            return [
                'status' => 1,
            ];
        }

        return [
            'status' => 0,
            'error' => '人机验证失败',
            'turnstile_response' => $json,
        ];
    }
}