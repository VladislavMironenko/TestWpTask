<?php
/**
 * Plugin Name: WP Chat Plugin
 * Description: A chat plugin that integrates with WordPress, GPT-4 AI, and Bitrix24 CRM.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: wp-chat-plugin
 */

// Подключаем необходимые файлы
require_once plugin_dir_path(__FILE__) . 'includes/class-crm-integration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ai-integration.php';


global $crm_integration;
global $ai_integration;

$crm_integration = new CRM_Integration();
$ai_integration = new AI_Integration();

// Регистрируем действия для загрузки стилей и скриптов
function wp_chat_plugin_enqueue_scripts() {
    wp_enqueue_style('wp-chat-plugin-style', plugins_url('assets/css/style.css', __FILE__));
    wp_enqueue_script('wp-chat-plugin-script', plugins_url('assets/js/chat.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'wp_chat_plugin_enqueue_scripts');

// Добавляем чат в подвал сайта
function wp_chat_plugin_add_chat_window() {
    include plugin_dir_path(__FILE__) . 'templates/chat-window.php';
}
add_action('wp_footer', 'wp_chat_plugin_add_chat_window');

function start_session_on_plugin_init() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session_on_plugin_init');


add_action('wp_ajax_save_survey_answers', 'wp_chat_plugin_save_chat_message');
add_action('wp_ajax_save_chat_message', 'wp_chat_plugin_save_chat_message');
add_action('wp_ajax_nopriv_save_chat_message', 'wp_chat_plugin_save_chat_message');

function wp_chat_plugin_save_chat_message() {
    // Проверка nonce для безопасности
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    // Получаем сообщение
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_text_field($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $answers = sanitize_text_field($_POST['answers']);
    $deal_id = $_SESSION['current_deal_id'] ?? null;

    if ($phone && $email && $name){
        $message = [
            'Имя: ' . $name,
            'Почта: ' . $email,
            'Номер телефона: ' . $phone
        ];
        $deal_id = $GLOBALS['crm_integration']->create_deal($message);
        $_SESSION['current_deal_id'] = $deal_id;
    } else if ($answers) {
        $deal_id_message = $GLOBALS['crm_integration']->update_deal($deal_id , "\nQuestions: " . $answers);
    } else {
        $message = sanitize_text_field($_POST['message']);
        $deal_id_message = $GLOBALS['crm_integration']->update_deal($deal_id , "\nUser: " . $message);
        $ai_response = $GLOBALS['ai_integration']->get_ai_response($message);
        $deal_id_message = $GLOBALS['crm_integration']->update_deal($deal_id , "\nAI: " . $ai_response);
    }

    // Формирование ответа
    if ($deal_id) {
        wp_send_json_success([
            'ai_response' => $ai_response,
            'response' => "Success",
            'deal_id' => $deal_id,
        ]);
    } else {
        wp_send_json_error([
            'message' => 'Failed to process message',
        ]);
    }

    wp_die(); // Завершить обработку AJAX
}

add_action('wp_ajax_get_session_data', 'wp_chat_plugin_get_session_data');
add_action('wp_ajax_nopriv_get_session_data', 'wp_chat_plugin_get_session_data');

function wp_chat_plugin_get_session_data() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    // Return session data
    wp_send_json_success([
        'session_data' => $_SESSION['user_chat_data'] ?? null
    ]);

    wp_die();
}


