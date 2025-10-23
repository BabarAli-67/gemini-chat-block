<?php
/**
 * Render the Gemini Chat Block on the frontend
 */

// Get attributes safely - handle both WP_Block object and array formats
$attributes = array();
if (isset($block)) {
	if (is_object($block) && isset($block->attributes)) {
		$attributes = $block->attributes;
	} elseif (is_array($block) && isset($block['attrs'])) {
		$attributes = $block['attrs'];
	}
}

$primary_color = $attributes['primaryColor'] ?? '#2563eb';
$background_color = $attributes['backgroundColor'] ?? '#ffffff';
$text_color = $attributes['textColor'] ?? '#374151';
$border_radius = $attributes['borderRadius'] ?? 8;
$placeholder = $attributes['placeholder'] ?? 'Ask me anything...';
$welcome_message = $attributes['welcomeMessage'] ?? 'Hello! I\'m your AI Assistant. How can I help you today?';

// Check if API key is configured
$api_key = get_option( 'gemini_chat_block_api_key' );
$is_configured = !empty( $api_key );

// Enqueue scripts and styles
wp_enqueue_script( 
	'gemini-chat-block-view', 
	GEMINI_CHAT_BLOCK_PLUGIN_URL . 'build/gemini-chat-block/view.js', 
	array(), 
	GEMINI_CHAT_BLOCK_VERSION, 
	true 
);

wp_localize_script( 'gemini-chat-block-view', 'geminiChatBlock', array(
	'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	'nonce' => wp_create_nonce( 'gemini_chat_nonce' ),
	'isConfigured' => $is_configured
) );

$wrapper_attributes = get_block_wrapper_attributes( array(
	'style' => sprintf(
		'--gemini-primary-color: %s; --gemini-background-color: %s; --gemini-text-color: %s; --gemini-border-radius: %spx;',
		esc_attr( $primary_color ),
		esc_attr( $background_color ),
		esc_attr( $text_color ),
		esc_attr( $border_radius )
	)
) );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( ! $is_configured ) : ?>
		<div class="gemini-chat-error">
			<div class="gemini-error-icon">⚠️</div>
			<div class="gemini-error-message">
				<h3>AI Assistant Not Available</h3>
				<p>The AI Assistant chat is not configured. Please contact the site administrator.</p>
			</div>
		</div>
	<?php else : ?>
		<div class="gemini-chat-container">
			<div class="gemini-chat-header">
				<div class="gemini-chat-title">
					<span class="gemini-chat-icon">🤖</span>
					<span>AI Assistant</span>
				</div>
			</div>
			
			<div class="gemini-chat-messages">
				<div class="gemini-message assistant-message gemini-welcome-message">
					<div class="gemini-message-content">
						<?php echo esc_html( $welcome_message ); ?>
					</div>
				</div>
			</div>
			
			<div class="gemini-chat-input-container">
				<div class="gemini-chat-input-wrapper">
					<textarea 
						class="gemini-chat-input" 
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						rows="1"
					></textarea>
					<button class="gemini-chat-send" type="button">
						<span>→</span>
					</button>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
