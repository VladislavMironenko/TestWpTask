<?php
class AI_Integration {
    public function get_ai_response($message) {
        $api_key = 'eTMX6SXG7MoBls723QzWEjmZZnQhyZP95etx9K1t';
        $url = 'https://api.cohere.ai/generate';
    
        $args = [
            'body' => json_encode([
                'model' => 'command-xlarge-nightly',
                'prompt' => $message,
                'max_tokens' => 250,
                'temperature' => 0.7
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 120
        ];
    
        $response = wp_remote_post($url, $args);
    
        if (is_wp_error($response)) {
            return "Ошибка запроса: " . $response->get_error_message();
        }
    
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
    
        if (isset($result['error'])) {
            return "Ошибка API: " . $result['error'];
        }
    
        if (!empty($result) && isset($result['text'])) {
            return trim($result['text']);
        }
    
        return "Нет ответа от модели.";
    }    
}
