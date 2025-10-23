<?php
/**
 * Admin functionality for Gemini Chat Block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gemini_Chat_Block_Admin {
	
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}
	
	public function add_admin_menu() {
		add_options_page(
			'Gemini Chat Block Settings',
			'Gemini Chat Block',
			'manage_options',
			'gemini-chat-block-settings',
			array( $this, 'admin_page' )
		);
	}
	
	public function register_settings() {
		register_setting( 'gemini_chat_block_settings', 'gemini_chat_block_api_key' );
		register_setting( 'gemini_chat_block_settings', 'gemini_chat_block_default_styles' );
	}
	
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_gemini-chat-block-settings' !== $hook ) {
			return;
		}
		
		wp_enqueue_style( 'gemini-chat-block-admin', GEMINI_CHAT_BLOCK_PLUGIN_URL . 'assets/admin.css', array(), GEMINI_CHAT_BLOCK_VERSION );
		wp_enqueue_script( 'gemini-chat-block-admin', GEMINI_CHAT_BLOCK_PLUGIN_URL . 'assets/admin.js', array( 'jquery' ), GEMINI_CHAT_BLOCK_VERSION, true );
	}
	
	public function admin_page() {
		$api_key = get_option( 'gemini_chat_block_api_key', '' );
		$default_styles = get_option( 'gemini_chat_block_default_styles', array() );
		
		?>
		<div class="wrap">
			<h1>Gemini Chat Block Settings</h1>
			
			<?php if ( empty( $api_key ) ) : ?>
				<div class="notice notice-warning">
					<p><strong>Configuration Required:</strong> Please enter your Gemini API key to use the chat block.</p>
				</div>
			<?php endif; ?>
			
			<form method="post" action="options.php">
				<?php settings_fields( 'gemini_chat_block_settings' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="gemini_chat_block_api_key">Gemini API Key</label>
						</th>
						<td>
							<input type="password" 
								   id="gemini_chat_block_api_key" 
								   name="gemini_chat_block_api_key" 
								   value="<?php echo esc_attr( $api_key ); ?>" 
								   class="regular-text" 
								   required />
							<p class="description">
								Enter your Google Gemini API key. You can get one from 
								<a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>.
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="primary_color">Primary Color</label>
						</th>
						<td>
							<input type="color" 
								   id="primary_color" 
								   name="gemini_chat_block_default_styles[primary_color]" 
								   value="<?php echo esc_attr( $default_styles['primary_color'] ?? '#2563eb' ); ?>" />
							<p class="description">Main color for the chat interface.</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="background_color">Background Color</label>
						</th>
						<td>
							<input type="color" 
								   id="background_color" 
								   name="gemini_chat_block_default_styles[background_color]" 
								   value="<?php echo esc_attr( $default_styles['background_color'] ?? '#ffffff' ); ?>" />
							<p class="description">Background color for the chat container.</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="text_color">Text Color</label>
						</th>
						<td>
							<input type="color" 
								   id="text_color" 
								   name="gemini_chat_block_default_styles[text_color]" 
								   value="<?php echo esc_attr( $default_styles['text_color'] ?? '#374151' ); ?>" />
							<p class="description">Text color for messages.</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="border_radius">Border Radius</label>
						</th>
						<td>
							<input type="range" 
								   id="border_radius" 
								   name="gemini_chat_block_default_styles[border_radius]" 
								   min="0" 
								   max="20" 
								   value="<?php echo esc_attr( $default_styles['border_radius'] ?? '8' ); ?>" />
							<span id="border_radius_value"><?php echo esc_attr( $default_styles['border_radius'] ?? '8' ); ?>px</span>
							<p class="description">Corner roundness for chat elements.</p>
						</td>
					</tr>
				</table>
				
				<?php submit_button( 'Save Settings' ); ?>
			</form>
			
			<div class="gemini-chat-block-preview">
				<h3>Preview</h3>
				<div id="style-preview" class="gemini-chat-preview">
					<div class="chat-message user-message">
						<p>Hello, AI Assistant!</p>
					</div>
					<div class="chat-message assistant-message">
						<p>Hello! How can I help you today?</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

new Gemini_Chat_Block_Admin();
