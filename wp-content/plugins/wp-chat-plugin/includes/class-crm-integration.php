<?php
class CRM_Integration {
    private $webhook_url = 'https://b24-oum0co.bitrix24.ru/rest/1/9dk5j9r389jo2sp3/';

    public function create_deal($text) { 
        $deal_data = array( 
            'fields' => array( 
                'TITLE' => 'Пользователь с сайта', 
                'COMMENTS' => "Данные сделки:\n" . implode("\n", $text),
            ) 
        ); 
        
        // Use wp_remote_post for WordPress HTTP requests
        $response = wp_remote_post($this->webhook_url . 'crm.deal.add', array(
            'body' => json_encode($deal_data),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
        
        // Decode the response
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        // Log the result for debugging
        if (isset($result['result'])) {
            return $result['result']; // Return the deal ID
        } else {
            error_log('CRM Deal Creation Failed: ' . print_r($result, true));
            return false;
        }
    }
    public function update_deal($deal_id, $message) {
        // Данные для получения текущей сделки
        $get_data = array(
            'id' => strval($deal_id)
        );

        // Запрос на получение текущей сделки
        $get_response = wp_remote_post($this->webhook_url . 'crm.deal.get', array(
            'body' => json_encode($get_data),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));

        // Проверка на ошибку в запросе
        if (is_wp_error($get_response)) {
            return false;
        }

        // Получаем данные из ответа
        $body = wp_remote_retrieve_body($get_response);
        $current_deal = json_decode($body, true);

        // Проверяем, есть ли результат в ответе
        if (isset($current_deal['result']['COMMENTS'])) {
            $current_comments = $current_deal['result']['COMMENTS'];
        } else {
            $current_comments = '';
        }

        // Подготовка данных для обновления сделки
        $update_data = array(
            'id' => $deal_id,
            'fields' => array(
                'COMMENTS' => $current_comments .  $message,
            )
        );

        // Запрос на обновление сделки
        $response = wp_remote_post($this->webhook_url . 'crm.deal.update', array(
            'body' => json_encode($update_data),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));

        // Проверка на ошибку в запросе
        if (is_wp_error($response)) {
            return false;
        }

        // Возвращаем статус код ответа
        return wp_remote_retrieve_response_message($response);
    }
}