# Gemini Chat Block - Complete Technical Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture & Flow](#architecture--flow)
3. [File Structure & Purpose](#file-structure--purpose)
4. [How WordPress Processes the Block](#how-wordpress-processes-the-block)
5. [Code Components Explained](#code-components-explained)
6. [Customization Guide](#customization-guide)
7. [Hooks & Filters](#hooks--filters)
8. [Troubleshooting](#troubleshooting)

---

## Overview

The Gemini Chat Block is a WordPress Gutenberg block that provides an interactive AI Assistant powered by Google's Gemini 2.5 Flash model. The block follows WordPress block development best practices and uses modern JavaScript (React) for the editor interface and vanilla JavaScript for the frontend.

### Key Features
- **Manual Block Addition**: Block appears ONLY when admin manually adds it via the block editor
- **Dynamic Rendering**: Uses PHP server-side rendering for secure API key handling
- **Customizable Styling**: Full control over colors, spacing, and appearance
- **Secure API Communication**: Nonce-verified AJAX requests
- **Markdown Support**: Basic formatting in AI responses

---

## Architecture & Flow

### High-Level Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                    Plugin Activation                         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│    Admin Configuration (Settings > Gemini Chat Block)        │
│    - Admin enters Gemini API key                            │
│    - Sets default styling preferences                        │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│            Block Registration (init hook)                    │
│    - WordPress registers block type                         │
│    - Block becomes available in block inserter              │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│        Admin Adds Block to Post/Page (Manual)               │
│    - Admin clicks + button in editor                        │
│    - Searches for "Gemini Chat Block"                       │
│    - Adds block to content                                  │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│              Block Editor (Edit Component)                   │
│    - React component renders preview                        │
│    - Shows inspector controls for customization             │
│    - Checks API key configuration status                    │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│            Post is Published/Updated                         │
│    - Block attributes saved to post_content                 │
│    - Stored as HTML comment with JSON attributes            │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│          Frontend Rendering (render.php)                     │
│    - WordPress parses post_content                          │
│    - Finds block markup                                     │
│    - Calls render.php with block attributes                 │
│    - Generates HTML output                                  │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│          User Interaction (view.js)                          │
│    - User types message                                     │
│    - JavaScript sends AJAX request                          │
│    - API handler processes request                          │
│    - Calls Gemini API                                       │
│    - Returns response to user                               │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow for Chat Messages

```
User Types Message
       ↓
view.js captures input
       ↓
Creates FormData with:
  - action: 'gemini_chat_request'
  - message: user's text
  - nonce: security token
       ↓
Sends to /wp-admin/admin-ajax.php
       ↓
WordPress routes to:
api-handler.php → handle_chat_request()
       ↓
Verifies nonce (security)
       ↓
Validates API key exists
       ↓
Sanitizes user message
       ↓
Calls call_gemini_api()
       ↓
Makes HTTP POST to:
https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent
       ↓
Receives JSON response
       ↓
Extracts text from:
data['candidates'][0]['content']['parts'][0]['text']
       ↓
Returns via wp_send_json_success()
       ↓
view.js receives response
       ↓
Parses markdown formatting
       ↓
Displays in chat interface
```

---

## File Structure & Purpose

```
gemini-chat-block/
│
├── gemini-chat-block.php          # Main plugin file
│   ├── Defines plugin constants
│   ├── Includes admin.php and api-handler.php
│   └── Registers block on 'init' hook
│
├── includes/
│   ├── admin.php                  # Admin settings page
│   │   ├── Creates settings page in WordPress admin
│   │   ├── Registers settings (API key, default styles)
│   │   ├── Renders admin form with color pickers
│   │   └── Enqueues admin CSS/JS
│   │
│   └── api-handler.php            # AJAX & API communication
│       ├── Handles 'gemini_chat_request' AJAX action
│       ├── Handles 'check_gemini_api_key' AJAX action
│       ├── Verifies nonces for security
│       ├── Makes HTTP requests to Gemini API
│       └── Returns formatted responses
│
├── assets/
│   ├── admin.css                  # Styles for admin settings page
│   └── admin.js                   # JavaScript for admin settings page
│
├── src/                           # Source files (compiled to build/)
│   └── gemini-chat-block/
│       ├── block.json             # Block metadata & attributes
│       │   ├── Defines block name, category, icon
│       │   ├── Declares all attributes (colors, text, etc.)
│       │   ├── Specifies supports (spacing, typography)
│       │   └── Links to scripts and styles
│       │
│       ├── index.js               # Block registration
│       │   └── Registers block with WordPress using metadata
│       │
│       ├── edit.js                # Editor component (React)
│       │   ├── Renders block preview in editor
│       │   ├── Provides InspectorControls (sidebar settings)
│       │   ├── Shows color pickers, text inputs, range controls
│       │   ├── Checks API key configuration
│       │   └── Updates block attributes
│       │
│       ├── render.php             # Server-side rendering
│       │   ├── Receives $block object from WordPress
│       │   ├── Extracts attributes safely
│       │   ├── Checks API key configuration
│       │   ├── Enqueues frontend scripts
│       │   ├── Localizes data for JavaScript
│       │   └── Outputs HTML with inline styles
│       │
│       ├── view.js                # Frontend JavaScript
│       │   ├── Initializes chat functionality
│       │   ├── Handles user input
│       │   ├── Sends AJAX requests
│       │   ├── Parses markdown formatting
│       │   ├── Displays messages
│       │   └── Manages loading states
│       │
│       ├── style.scss             # Frontend & editor styles
│       │   ├── CSS custom properties for theming
│       │   ├── Chat container layout
│       │   ├── Message bubble styles
│       │   ├── Input field styles
│       │   ├── Animations (fade-in, typing indicator)
│       │   └── Responsive design
│       │
│       └── editor.scss            # Editor-only styles
│           └── Inspector control styling
│
└── build/                         # Compiled files (generated)
    ├── blocks-manifest.php        # Block metadata as PHP array
    └── gemini-chat-block/
        ├── block.json             # Copied from src/
        ├── render.php             # Copied from src/
        ├── index.js               # Compiled & minified JavaScript
        ├── index.css              # Compiled editor CSS
        ├── view.js                # Compiled frontend JavaScript
        └── style-index.css        # Compiled frontend CSS
```

---

## How WordPress Processes the Block

### 1. Plugin Initialization

When WordPress loads, it runs the `init` action hook:

```php
add_action( 'init', 'create_block_gemini_chat_block_block_init' );
```

This function registers the block using one of three methods (depending on WordPress version):
- `wp_register_block_types_from_metadata_collection()` (WP 6.8+)
- `wp_register_block_metadata_collection()` (WP 6.7+)
- `register_block_type()` (WP 6.0+)

**Important**: Registration does NOT add the block to posts. It only makes it available in the block inserter.

### 2. Block Appears in Editor

When admin edits a post:
1. WordPress loads the block editor (Gutenberg)
2. The editor fetches all registered blocks
3. Our block appears in the inserter under "Widgets" category
4. Admin must MANUALLY click and add it

### 3. Block Attributes Storage

When admin adds the block and customizes it:
```html
<!-- wp:create-block/gemini-chat-block {
  "primaryColor":"#2563eb",
  "backgroundColor":"#ffffff",
  "textColor":"#374151",
  "borderRadius":8,
  "placeholder":"Ask me anything...",
  "welcomeMessage":"Hello! I'm your AI Assistant."
} /-->
```

This HTML comment is stored in `post_content` in the database.

### 4. Frontend Rendering

When a visitor views the post:
1. WordPress parses `post_content`
2. Finds block markers (`<!-- wp:create-block/gemini-chat-block -->`)
3. Creates `WP_Block` object with attributes
4. Calls `render.php` with the block object
5. `render.php` generates HTML output
6. HTML is inserted into page
7. `view.js` is enqueued and runs
8. Chat interface becomes interactive

---

## Code Components Explained

### 1. Main Plugin File (`gemini-chat-block.php`)

**Purpose**: Entry point for the plugin

**What it does**:
```php
// Define constants for paths and URLs
define( 'GEMINI_CHAT_BLOCK_VERSION', '0.1.0' );
define( 'GEMINI_CHAT_BLOCK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GEMINI_CHAT_BLOCK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Load admin functionality only in admin area
if ( is_admin() ) {
    require_once GEMINI_CHAT_BLOCK_PLUGIN_PATH . 'includes/admin.php';
}

// Load API handler for AJAX requests
require_once GEMINI_CHAT_BLOCK_PLUGIN_PATH . 'includes/api-handler.php';

// Register block on WordPress init
add_action( 'init', 'create_block_gemini_chat_block_block_init' );
```

**Key Points**:
- Uses `is_admin()` to conditionally load admin code
- Registers block using WordPress standards
- Block only appears when manually added by admin

---

### 2. Admin Settings (`includes/admin.php`)

**Purpose**: Provide admin interface for configuration

**Class**: `Gemini_Chat_Block_Admin`

**Hooks Used**:
- `admin_menu`: Adds settings page
- `admin_init`: Registers settings
- `admin_enqueue_scripts`: Loads CSS/JS

**Settings Registered**:
1. `gemini_chat_block_api_key` (string): Gemini API key
2. `gemini_chat_block_default_styles` (array): Default color preferences

**Settings Page Location**: Settings > Gemini Chat Block

**What it does**:
```php
// Adds submenu under Settings
add_options_page(
    'Gemini Chat Block Settings',  // Page title
    'Gemini Chat Block',            // Menu title
    'manage_options',               // Capability required
    'gemini-chat-block-settings',   // Menu slug
    array( $this, 'admin_page' )    // Callback function
);

// Registers settings in WordPress options table
register_setting( 'gemini_chat_block_settings', 'gemini_chat_block_api_key' );
```

---

### 3. API Handler (`includes/api-handler.php`)

**Purpose**: Handle AJAX requests and communicate with Gemini API

**Class**: `Gemini_Chat_Block_API`

**AJAX Actions Registered**:
1. `gemini_chat_request`: Handle chat messages (logged-in & public)
2. `check_gemini_api_key`: Verify API key is configured

**Security Measures**:
- Nonce verification: `wp_verify_nonce( $_POST['nonce'], 'gemini_chat_nonce' )`
- Input sanitization: `sanitize_text_field( $_POST['message'] )`
- Proper error handling with `wp_send_json_error()`

**Gemini API Call**:
```php
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;

$data = array(
    'contents' => array(
        array(
            'parts' => array(
                array(
                    'text' => "You are a helpful AI assistant. [user message]"
                )
            )
        )
    ),
    'generationConfig' => array(
        'temperature' => 0.7,      // Creativity level
        'topK' => 40,              // Token sampling
        'topP' => 0.95,            // Cumulative probability
        'maxOutputTokens' => 1024, // Response length limit
    )
);

$response = wp_remote_request( $url, array(
    'method' => 'POST',
    'timeout' => 30,
    'headers' => array( 'Content-Type' => 'application/json' ),
    'body' => json_encode( $data )
) );
```

---

### 4. Block Metadata (`block.json`)

**Purpose**: Define block properties and capabilities

**Key Fields**:

```json
{
  "name": "create-block/gemini-chat-block",  // Unique identifier
  "category": "widgets",                      // Where it appears in inserter
  "icon": "smiley",                          // Dashicon
  "attributes": {                            // Customizable properties
    "primaryColor": { "type": "string", "default": "#2563eb" },
    // ... more attributes
  },
  "supports": {                              // WordPress features
    "html": false,                           // No HTML editing
    "spacing": { "padding": true, "margin": true },
    "typography": { "fontSize": true }
  },
  "render": "file:./render.php"             // Server-side rendering
}
```

**Attributes Explained**:
- `primaryColor`: Main color for buttons and user messages
- `backgroundColor`: Chat container background
- `textColor`: Message text color
- `borderRadius`: Corner roundness (0-20px)
- `placeholder`: Input field placeholder text
- `welcomeMessage`: Initial AI Assistant message

**Supports**:
- `html: false`: Prevents raw HTML editing (security)
- `spacing`: Allows padding/margin controls
- `typography`: Allows font size controls

---

### 5. Editor Component (`edit.js`)

**Purpose**: Render block in WordPress editor

**Framework**: React (via @wordpress/element)

**Key Components Used**:
- `useBlockProps`: Adds WordPress-required props
- `InspectorControls`: Sidebar settings panel
- `PanelBody`: Collapsible settings group
- `ColorPicker`: Color selection
- `RangeControl`: Slider for numbers
- `TextControl`: Text input fields

**State Management**:
```javascript
const [apiKeyConfigured, setApiKeyConfigured] = useState(false);

useEffect(() => {
    // Check API key on component mount
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: 'action=check_gemini_api_key'
    })
    .then(response => response.json())
    .then(data => setApiKeyConfigured(data.success));
}, []);
```

**Attribute Updates**:
```javascript
setAttributes({ primaryColor: newColor })
```

This updates the block's saved attributes in the database.

---

### 6. Server-Side Rendering (`render.php`)

**Purpose**: Generate HTML for frontend

**Input**: `$block` object (WP_Block instance)

**Process**:
```php
// 1. Extract attributes safely
$attributes = array();
if (isset($block)) {
    if (is_object($block) && isset($block->attributes)) {
        $attributes = $block->attributes;  // WP_Block object
    } elseif (is_array($block) && isset($block['attrs'])) {
        $attributes = $block['attrs'];      // Array format (fallback)
    }
}

// 2. Get individual attributes with defaults
$primary_color = $attributes['primaryColor'] ?? '#2563eb';

// 3. Check API key configuration
$api_key = get_option( 'gemini_chat_block_api_key' );
$is_configured = !empty( $api_key );

// 4. Enqueue frontend JavaScript
wp_enqueue_script( 'gemini-chat-block-view', ... );

// 5. Pass data to JavaScript
wp_localize_script( 'gemini-chat-block-view', 'geminiChatBlock', array(
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'gemini_chat_nonce' ),
    'isConfigured' => $is_configured
) );

// 6. Generate HTML with inline styles
$wrapper_attributes = get_block_wrapper_attributes( array(
    'style' => sprintf(
        '--gemini-primary-color: %s; ...',
        esc_attr( $primary_color )
    )
) );
```

**Output**: HTML with CSS custom properties for dynamic styling

---

### 7. Frontend JavaScript (`view.js`)

**Purpose**: Make chat interface interactive

**Key Functions**:

```javascript
// Parse markdown to HTML
function parseMarkdown(text) {
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')  // **bold**
        .replace(/\*(.*?)\*/g, '<em>$1</em>')              // *italic*
        .replace(/`(.*?)`/g, '<code>$1</code>')            // `code`
        .replace(/\n/g, '<br>');                           // line breaks
}

// Send message to API
async function sendMessage() {
    const formData = new FormData();
    formData.append('action', 'gemini_chat_request');
    formData.append('message', message);
    formData.append('nonce', geminiChatBlock.nonce);
    
    const response = await fetch(geminiChatBlock.ajaxUrl, {
        method: 'POST',
        body: formData
    });
    
    const data = await response.json();
    // Display response
}

// Add message to chat
function addMessage(content, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `gemini-message ${type}-message`;
    // Append to messages container
}
```

**Event Listeners**:
- Click on send button
- Enter key in textarea (Shift+Enter for new line)
- Auto-resize textarea as user types

---

### 8. Styling (`style.scss`)

**Purpose**: Style chat interface

**CSS Custom Properties**:
```scss
.wp-block-create-block-gemini-chat-block {
    --gemini-primary-color: #2563eb;
    --gemini-background-color: #ffffff;
    --gemini-text-color: #374151;
    --gemini-border-radius: 8px;
}
```

These are overridden by inline styles from `render.php` based on user customization.

**Key Styles**:
- `.gemini-chat-container`: Main flex container
- `.gemini-chat-messages`: Scrollable message area
- `.user-message`: User's messages (right-aligned, primary color)
- `.assistant-message`: AI responses (left-aligned, gray background)
- `.gemini-typing-indicator`: Animated dots for loading state

**Animations**:
```scss
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-10px); opacity: 1; }
}
```

---

## Customization Guide

### 1. Change Default Colors

**Option A: Via Admin Settings**
- Go to Settings > Gemini Chat Block
- Use color pickers to set defaults
- These apply to all new blocks

**Option B: Modify Code**
Edit `src/gemini-chat-block/block.json`:
```json
"attributes": {
    "primaryColor": {
        "type": "string",
        "default": "#your-color-here"
    }
}
```

### 2. Customize AI Behavior

Edit `includes/api-handler.php` in `call_gemini_api()`:

```php
// Change personality
'text' => "You are a [friendly/professional/technical] AI assistant. [instructions]"

// Adjust creativity
'temperature' => 0.7,  // 0.0 = deterministic, 1.0 = creative

// Change response length
'maxOutputTokens' => 1024,  // Increase for longer responses
```

### 3. Add Custom Styling

Edit `src/gemini-chat-block/style.scss`:

```scss
// Add custom animation
.gemini-message {
    animation: slideIn 0.3s ease-out;
}

// Change message bubble shape
.gemini-message-content {
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
```

After changes, rebuild:
```bash
npm run build
```

### 4. Modify Welcome Message

**Per Block**: Use Inspector Controls in editor

**Default**: Edit `block.json`:
```json
"welcomeMessage": {
    "type": "string",
    "default": "Your custom welcome message"
}
```

### 5. Add New Attributes

1. Add to `block.json`:
```json
"attributes": {
    "showTimestamps": {
        "type": "boolean",
        "default": false
    }
}
```

2. Add control in `edit.js`:
```javascript
<ToggleControl
    label="Show Timestamps"
    checked={showTimestamps}
    onChange={(value) => setAttributes({ showTimestamps: value })}
/>
```

3. Use in `render.php`:
```php
$show_timestamps = $attributes['showTimestamps'] ?? false;
```

4. Rebuild: `npm run build`

---

## Hooks & Filters

### WordPress Hooks Used

#### Actions

1. **`init`** (Priority: 10)
   - **Where**: `gemini-chat-block.php`
   - **Purpose**: Register block with WordPress
   - **Function**: `create_block_gemini_chat_block_block_init()`

2. **`admin_menu`** (Priority: 10)
   - **Where**: `includes/admin.php`
   - **Purpose**: Add settings page to admin menu
   - **Function**: `Gemini_Chat_Block_Admin::add_admin_menu()`

3. **`admin_init`** (Priority: 10)
   - **Where**: `includes/admin.php`
   - **Purpose**: Register plugin settings
   - **Function**: `Gemini_Chat_Block_Admin::register_settings()`

4. **`admin_enqueue_scripts`** (Priority: 10)
   - **Where**: `includes/admin.php`
   - **Purpose**: Load admin CSS/JS on settings page
   - **Function**: `Gemini_Chat_Block_Admin::enqueue_admin_scripts()`

5. **`wp_ajax_gemini_chat_request`** (Priority: 10)
   - **Where**: `includes/api-handler.php`
   - **Purpose**: Handle chat requests from logged-in users
   - **Function**: `Gemini_Chat_Block_API::handle_chat_request()`

6. **`wp_ajax_nopriv_gemini_chat_request`** (Priority: 10)
   - **Where**: `includes/api-handler.php`
   - **Purpose**: Handle chat requests from non-logged-in users
   - **Function**: `Gemini_Chat_Block_API::handle_chat_request()`

7. **`wp_ajax_check_gemini_api_key`** (Priority: 10)
   - **Where**: `includes/api-handler.php`
   - **Purpose**: Check if API key is configured
   - **Function**: `Gemini_Chat_Block_API::check_api_key()`

### Custom Hooks (None Currently)

This plugin does not currently define custom hooks, but you could add them:

**Example - Add filter for AI prompt:**
```php
// In api-handler.php
$prompt = apply_filters( 'gemini_chat_block_prompt', 
    "You are a helpful AI assistant.", 
    $message 
);
```

**Usage in theme:**
```php
add_filter( 'gemini_chat_block_prompt', function( $prompt, $message ) {
    return "You are a technical support assistant. " . $prompt;
}, 10, 2 );
```

---

## Post Types & Taxonomies

### Post Types

**None Used**: This plugin does not register custom post types.

The block can be added to any post type that supports Gutenberg blocks:
- Posts (`post`)
- Pages (`page`)
- Custom post types with `show_in_rest => true`

### Taxonomies

**None Used**: This plugin does not register custom taxonomies.

---

## Troubleshooting

### Block Not Appearing in Inserter

**Problem**: Can't find block when clicking +

**Solutions**:
1. Clear WordPress transients: Install "Transients Manager" plugin
2. Rebuild block: `npm run build` in plugin directory
3. Deactivate and reactivate plugin
4. Check PHP error logs for registration errors

### Block Appearing Automatically on Posts

**Problem**: Block shows up without being added

**This should NOT happen**. Possible causes:
1. Theme or another plugin auto-inserting blocks
2. Post content was imported with block already in it
3. WordPress transient cache issue

**Solutions**:
1. Check post content in database for `<!-- wp:create-block/gemini-chat-block -->`
2. Deactivate other plugins temporarily
3. Switch to default WordPress theme (Twenty Twenty-Four)
4. Clear all caches

### Block Not Selectable in Editor

**Problem**: Can't click on block to select/edit/delete it

**Solution**: Fixed in latest version - block is now properly wrapped with `useBlockProps()` for proper WordPress block selection handling.

### API Key Not Working

**Problem**: Chat doesn't respond

**Solutions**:
1. Verify API key: Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Check browser console for JavaScript errors
3. Check WordPress debug log for PHP errors
4. Test API key with curl:
```bash
curl -X POST \
  "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{"contents":[{"parts":[{"text":"Hello"}]}]}'
```

### Styling Not Applying

**Problem**: Custom colors not showing

**Solutions**:
1. Clear browser cache
2. Rebuild block: `npm run build`
3. Check for CSS conflicts in browser developer tools
4. Verify inline styles in page source

### AJAX Errors

**Problem**: "Security check failed" or no response

**Solutions**:
1. Check that JavaScript is loading: View page source
2. Verify nonce is being created in `render.php`
3. Check AJAX URL is correct (should be `/wp-admin/admin-ajax.php`)
4. Look for JavaScript errors in console

---

## Development Workflow

### Making Changes

1. **Edit source files** in `src/gemini-chat-block/`
2. **Rebuild** with `npm run build`
3. **Test** in WordPress
4. **Repeat**

### Build Commands

```bash
# Development build (watch mode)
npm start

# Production build (minified)
npm run build

# Format code
npm run format

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Create plugin ZIP
npm run plugin-zip
```

### File Watching

For development, use:
```bash
npm start
```

This watches for changes and automatically rebuilds.

---

## Security Considerations

### 1. Nonce Verification
All AJAX requests verify nonces to prevent CSRF attacks.

### 2. Input Sanitization
User messages are sanitized with `sanitize_text_field()`.

### 3. API Key Storage
API key is stored in WordPress options table, not exposed to frontend.

### 4. Capability Checks
Admin settings require `manage_options` capability.

### 5. Escaping Output
All dynamic output is escaped:
- `esc_attr()` for attributes
- `esc_html()` for text content
- `wp_kses_post()` for HTML content

---

## Performance Optimization

### 1. Asset Loading
- Scripts loaded only when block is present
- Admin assets loaded only on settings page
- Production builds are minified

### 2. API Calls
- Timeout set to 30 seconds
- Error handling prevents hanging
- Results cached in browser during conversation

### 3. CSS
- Uses CSS custom properties (fast)
- Compiled and minified SCSS
- Responsive design with mobile-first approach

---

## Conclusion

This plugin follows WordPress coding standards and best practices. The block appears ONLY when manually added by an administrator via the block editor. It provides a secure, customizable, and performant AI chat interface powered by Google's Gemini 2.5 Flash model.

For questions or issues, check the troubleshooting section or review the code comments in each file.
