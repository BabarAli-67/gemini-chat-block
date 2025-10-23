<?php
/**
 * API Handler for Gemini Chat Block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gemini_Chat_Block_API {
	
	public function __construct() {
		add_action( 'wp_ajax_gemini_chat_request', array( $this, 'handle_chat_request' ) );
		add_action( 'wp_ajax_nopriv_gemini_chat_request', array( $this, 'handle_chat_request' ) );
		add_action( 'wp_ajax_check_gemini_api_key', array( $this, 'check_api_key' ) );
	}
	
	public function handle_chat_request() {
		// Verify nonce for security
		if ( ! wp_verify_nonce( $_POST['nonce'], 'gemini_chat_nonce' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$api_key = get_option( 'gemini_chat_block_api_key' );
		if ( empty( $api_key ) ) {
			wp_send_json_error( array( 'message' => 'API key not configured' ) );
		}
		
		$message = sanitize_text_field( $_POST['message'] );
		if ( empty( $message ) ) {
			wp_send_json_error( array( 'message' => 'Message is required' ) );
		}
		
		$response = $this->call_gemini_api( $api_key, $message );
		
		if ( $response ) {
			wp_send_json_success( array( 'response' => $response ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to get response from AI Assistant' ) );
		}
	}
	
	private function call_gemini_api( $api_key, $message ) {
		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;
		
		$data = array(
			'contents' => array(
				array(
					'parts' => array(
						array(
							'text' => "You are a helpful AI assistant. Please provide concise, helpful responses. Format your response using basic markdown if needed (use **bold** for emphasis, *italic* for emphasis, etc.). Keep responses brief and to the point. User question: " . $message
						)
					)
				)
			),
			'generationConfig' => array(
				'temperature' => 0.7,
				'topK' => 40,
				'topP' => 0.95,
				'maxOutputTokens' => 1024,
			)
		);
		
		$args = array(
			'method' => 'POST',
			'timeout' => 30,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body' => json_encode( $data ),
		);
		
		$response = wp_remote_request( $url, $args );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return $data['candidates'][0]['content']['parts'][0]['text'];
		}
		
		return false;
	}
	
	public function check_api_key() {
		$api_key = get_option( 'gemini_chat_block_api_key' );
		wp_send_json_success( array( 'configured' => !empty( $api_key ) ) );
	}
}

new Gemini_Chat_Block_API();
