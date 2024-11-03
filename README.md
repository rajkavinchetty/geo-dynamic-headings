# Geo Dynamic Headings for Elementor

A WordPress plugin that enables dynamic heading content based on geographical locations using Elementor page builder. Perfect for websites that need to display different content for different regions or countries.

## 🚀 Features

- **Dynamic Geo-based Headings**: Display different heading text based on user's geographical location
- **Flexible Location Management**: Add, edit, and manage multiple locations through an intuitive admin interface
- **Multiple Display Options**: Choose between dropdown or button selectors for location switching
- **Redirect Support**: Optional URL redirects for specific locations
- **Full Elementor Integration**: Custom widgets that work seamlessly with Elementor
- **Responsive Design**: Fully responsive and mobile-friendly
- **Cookie-based**: Uses cookies to remember user's location preference

## 📋 Requirements

- WordPress 5.0 or higher
- Elementor 3.0.0 or higher
- PHP 7.0 or higher

## 💻 Installation

1. Download the plugin zip file
2. Go to WordPress admin > Plugins > Add New
3. Click "Upload Plugin" and select the downloaded zip file
4. Click "Install Now" and then "Activate"

Or manually:

1. Download and unzip the plugin
2. Upload the `geo-dynamic-headings` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

## 🔧 Configuration

### Setting Up Locations

1. Go to WordPress admin > Settings > Geo Headings
2. Add your locations with the following details:
   - Location Code (e.g., en-us, ca, uk)
   - Location Name
   - Default Location status
   - Redirect URL (optional)
3. Save your settings

### Using the Widgets

The plugin adds two Elementor widgets:

1. **Geo Heading**
   - Add different heading text for each location
   - Style your headings using Elementor's style controls
   - Set default content for unmatched locations

2. **Geo Selector**
   - Choose between dropdown or button display
   - Customize the appearance using Elementor's style controls
   - Automatically updates content when location changes
