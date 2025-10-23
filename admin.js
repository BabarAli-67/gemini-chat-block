/**
 * Admin JavaScript for Gemini Chat Block
 */

jQuery(document).ready(function($) {
	// Update border radius value display
	$('#border_radius').on('input', function() {
		$('#border_radius_value').text($(this).val() + 'px');
		updatePreview();
	});

	// Update preview when colors change
	$('input[type="color"]').on('change', function() {
		updatePreview();
	});

	// Update preview function
	function updatePreview() {
		const primaryColor = $('#primary_color').val();
		const backgroundColor = $('#background_color').val();
		const textColor = $('#text_color').val();
		const borderRadius = $('#border_radius').val();

		const preview = $('.gemini-chat-preview');
		
		preview.css({
			'--primary-color': primaryColor,
			'--background-color': backgroundColor,
			'--text-color': textColor,
			'--border-radius': borderRadius + 'px'
		});

		// Update specific elements
		preview.find('.gemini-chat-header').css('background', primaryColor);
		preview.find('.user-message .gemini-message-content').css('background', primaryColor);
		preview.find('.gemini-chat-send').css('background', primaryColor);
		preview.find('.assistant-message .gemini-message-content').css('color', textColor);
		preview.find('.gemini-chat-input').css({
			'border-radius': borderRadius + 'px'
		});
		preview.find('.gemini-message-content').css({
			'border-radius': borderRadius + 'px'
		});
	}

	// Initialize preview
	updatePreview();
});
