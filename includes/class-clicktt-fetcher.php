<?php
if (!defined('ABSPATH')) exit;

final class CTTCM_Fetcher {
    public function get(string $url): array {
        $url = esc_url_raw($url);
        if (!$this->allowed($url)) return ['ok'=>false, 'error'=>'Nicht erlaubte URL. Nur mytischtennis.de ist zulässig.'];
        $response = wp_safe_remote_get($url, [
            'timeout' => 15,
            'redirection' => 5,
            'headers' => [
                'User-Agent' => 'Click-TT Club Manager/' . CTTCM_VERSION . '; ' . home_url('/'),
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.5',
            ],
        ]);
        if (is_wp_error($response)) return ['ok'=>false, 'error'=>$response->get_error_message()];
        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        if ($code < 200 || $code >= 300) return ['ok'=>false, 'error'=>'HTTP-Status ' . $code];
        if (strlen($body) < 500) return ['ok'=>false, 'error'=>'Die Antwort ist ungewöhnlich kurz.'];
        return ['ok'=>true, 'body'=>$body, 'final_url'=>$url];
    }
    private function allowed(string $url): bool {
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        return $host === 'mytischtennis.de' || $host === 'www.mytischtennis.de';
    }
}
