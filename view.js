/**
 * Frontend JavaScript for Gemini Chat Block
 */

(function() {
	'use strict';

	// Simple markdown parser for basic formatting
	function parseMarkdown(text) {
		return text
			.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
			.replace(/\*(.*?)\*/g, '<em>$1</em>')
			.replace(/`(.*?)`/g, '<code>$1</code>')
			.replace(/\n/g, '<br>');
	}

	// Initialize chat functionality
	function initChatBlock() {
		const chatBlocks = document.querySelectorAll('.wp-block-create-block-gemini-chat-block');
		
		chatBlocks.forEach(block => {
			const input = block.querySelector('.gemini-chat-input');
			const sendButton = block.querySelector('.gemini-chat-send');
			const messagesContainer = block.querySelector('.gemini-chat-messages');
			const welcomeMessage = block.querySelector('.gemini-welcome-message');
			
			if (!input || !sendButton || !messagesContainer) return;

			let isLoading = false;

			// Send message function
			async function sendMessage() {
				const message = input.value.trim();
				if (!message || isLoading) return;

				// Add user message
				addMessage(message, 'user');
				input.value = '';
				sendButton.disabled = true;
				isLoading = true;

				// Show loading indicator
				const loadingMessage = addMessage('Thinking...', 'assistant', true);

				try {
					const formData = new FormData();
					formData.append('action', 'gemini_chat_request');
					formData.append('message', message);
					formData.append('nonce', geminiChatBlock.nonce);

					const response = await fetch(geminiChatBlock.ajaxUrl, {
						method: 'POST',
						body: formData
					});

					const data = await response.json();

					// Remove loading message
					loadingMessage.remove();

					if (data.success) {
						addMessage(data.data.response, 'assistant');
					} else {
						addMessage(data.data.message || 'Sorry, I encountered an error. Please try again.', 'assistant');
					}
				} catch (error) {
					loadingMessage.remove();
					addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
				}

				sendButton.disabled = false;
				isLoading = false;
			}

			// Add message to chat
			function addMessage(content, type, isLoading = false) {
				const messageDiv = document.createElement('div');
				messageDiv.className = `gemini-message ${type}-message`;
				
				const contentDiv = document.createElement('div');
				contentDiv.className = 'gemini-message-content';
				
				if (isLoading) {
					contentDiv.innerHTML = '<div class="gemini-typing-indicator"><span></span><span></span><span></span></div>';
				} else {
					contentDiv.innerHTML = parseMarkdown(content);
				}
				
				messageDiv.appendChild(contentDiv);
				messagesContainer.appendChild(messageDiv);
				
				// Scroll to bottom
				messagesContainer.scrollTop = messagesContainer.scrollHeight;
				
				return messageDiv;
			}

			// Event listeners
			sendButton.addEventListener('click', sendMessage);
			
			input.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' && !e.shiftKey) {
					e.preventDefault();
					sendMessage();
				}
			});

			// Auto-resize input
			input.addEventListener('input', () => {
				input.style.height = 'auto';
				input.style.height = Math.min(input.scrollHeight, 120) + 'px';
			});
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initChatBlock);
	} else {
		initChatBlock();
	}

})();
