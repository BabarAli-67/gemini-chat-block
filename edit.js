/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * WordPress components
 */
import { 
	PanelBody, 
	ColorPicker, 
	RangeControl, 
	TextControl,
	Button,
	Notice
} from '@wordpress/components';

/**
 * WordPress hooks
 */
import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object} props - Block props
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { 
		primaryColor, 
		backgroundColor, 
		textColor, 
		borderRadius, 
		placeholder, 
		welcomeMessage 
	} = attributes;

	const [apiKeyConfigured, setApiKeyConfigured] = useState(false);

	useEffect(() => {
		// Check if API key is configured
		fetch('/wp-admin/admin-ajax.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'action=check_gemini_api_key'
		})
		.then(response => response.json())
		.then(data => {
			setApiKeyConfigured(data.success);
		})
		.catch(() => {
			setApiKeyConfigured(false);
		});
	}, []);

	const blockProps = useBlockProps({
		style: {
			'--gemini-primary-color': primaryColor,
			'--gemini-background-color': backgroundColor,
			'--gemini-text-color': textColor,
			'--gemini-border-radius': `${borderRadius}px`,
		}
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Chat Settings', 'gemini-chat-block')}>
					<TextControl
						label={__('Welcome Message', 'gemini-chat-block')}
						value={welcomeMessage}
						onChange={(value) => setAttributes({ welcomeMessage: value })}
						help={__('The initial message shown to users', 'gemini-chat-block')}
					/>
					<TextControl
						label={__('Input Placeholder', 'gemini-chat-block')}
						value={placeholder}
						onChange={(value) => setAttributes({ placeholder: value })}
						help={__('Placeholder text for the input field', 'gemini-chat-block')}
					/>
				</PanelBody>
				
				<PanelBody title={__('Styling', 'gemini-chat-block')}>
					<div className="gemini-color-controls">
						<div className="gemini-color-control">
							<label>{__('Primary Color', 'gemini-chat-block')}</label>
							<ColorPicker
								color={primaryColor}
								onChange={(color) => setAttributes({ primaryColor: color })}
							/>
						</div>
						<div className="gemini-color-control">
							<label>{__('Background Color', 'gemini-chat-block')}</label>
							<ColorPicker
								color={backgroundColor}
								onChange={(color) => setAttributes({ backgroundColor: color })}
							/>
						</div>
						<div className="gemini-color-control">
							<label>{__('Text Color', 'gemini-chat-block')}</label>
							<ColorPicker
								color={textColor}
								onChange={(color) => setAttributes({ textColor: color })}
							/>
						</div>
					</div>
					<RangeControl
						label={__('Border Radius', 'gemini-chat-block')}
						value={borderRadius}
						onChange={(value) => setAttributes({ borderRadius: value })}
						min={0}
						max={20}
						step={1}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="gemini-chat-block-editor">
					{!apiKeyConfigured && (
						<Notice status="warning" isDismissible={false}>
							<p>
								{__('Gemini API key not configured. ', 'gemini-chat-block')}
								<Button 
									variant="link" 
									href="/wp-admin/options-general.php?page=gemini-chat-block-settings"
									target="_blank"
								>
									{__('Configure now', 'gemini-chat-block')}
								</Button>
							</p>
						</Notice>
					)}
					
					<div className="gemini-chat-container">
						<div className="gemini-chat-header">
							<div className="gemini-chat-title">
								<span className="gemini-chat-icon">🤖</span>
								<span>{__('AI Assistant', 'gemini-chat-block')}</span>
							</div>
						</div>
						
						<div className="gemini-chat-messages">
							<div className="gemini-message assistant-message">
								<div className="gemini-message-content">
									{welcomeMessage}
								</div>
							</div>
						</div>
						
						<div className="gemini-chat-input-container">
							<div className="gemini-chat-input-wrapper">
								<input 
									type="text" 
									placeholder={placeholder}
									className="gemini-chat-input"
									disabled
								/>
								<button className="gemini-chat-send" disabled>
									<span>→</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
