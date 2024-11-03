<?php
if (!defined('ABSPATH')) {
    exit;
}

class GDH_Settings_Page
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_plugin_page()
    {
        add_options_page(
            'Geo Dynamic Headings Settings',
            'Geo Headings',
            'manage_options',
            'geo-dynamic-headings',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page()
    {
        $this->options = get_option('gdh_locations');
?>
        <div class="wrap">
            <h1>Geo Dynamic Headings Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('gdh_locations_group');
                do_settings_sections('geo-dynamic-headings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function page_init()
    {
        register_setting(
            'gdh_locations_group',
            'gdh_locations',
            array($this, 'sanitize')
        );

        add_settings_section(
            'gdh_locations_section',
            'Location Settings',
            array($this, 'section_info'),
            'geo-dynamic-headings'
        );

        add_settings_field(
            'locations',
            'Locations',
            array($this, 'locations_callback'),
            'geo-dynamic-headings',
            'gdh_locations_section'
        );
    }

    public function sanitize($input)
    {
        $new_input = array();

        if (isset($input['locations']) && is_array($input['locations'])) {
            foreach ($input['locations'] as $idx => $location) {
                if (!empty($location['code'])) {
                    $new_input['locations'][$idx] = array(
                        'code' => sanitize_text_field($location['code']),
                        'name' => sanitize_text_field($location['name']),
                        'redirect_url' => esc_url_raw($location['redirect_url']),
                        'is_redirect' => isset($location['is_redirect']) ? '1' : '0'
                    );
                }
            }
        }

        return $new_input;
    }

    public function section_info()
    {
        echo 'Configure your locations below. You can add multiple locations and specify redirect URLs if needed.';
    }

    public function locations_callback()
    {
        $locations = isset($this->options['locations']) ? $this->options['locations'] : array();
    ?>
        <div id="gdh-locations-container">
            <?php
            if (!empty($locations)) {
                foreach ($locations as $idx => $location) {
                    $this->render_location_row($idx, $location);
                }
            }
            ?>
        </div>
        <button type="button" class="button" id="add-location">Add Location</button>

        <script type="text/template" id="location-row-template">
            <?php $this->render_location_row('{{INDEX}}', array(
                'code' => '',
                'name' => '',
                'redirect_url' => '',
                'is_redirect' => '0'
            )); ?>
        </script>
    <?php
    }

    private function render_location_row($idx, $location)
    {
    ?>
        <div class="location-row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;">
            <p>
                <input type="text"
                    name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][code]"
                    placeholder="Location Code (e.g., us, ca, uk)"
                    value="<?php echo esc_attr($location['code']); ?>"
                    style="width: 200px;"
                    required>

                <input type="text"
                    name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][name]"
                    placeholder="Location Name (e.g., United States)"
                    value="<?php echo esc_attr($location['name']); ?>"
                    style="width: 200px;"
                    required>
            </p>

            <p>
                <label>
                    <input type="checkbox"
                        name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][is_redirect]"
                        value="1"
                        <?php checked($location['is_redirect'], '1'); ?>
                        class="redirect-toggle">
                    Redirect to external website
                </label>
            </p>

            <p class="redirect-url-field" style="<?php echo $location['is_redirect'] !== '1' ? 'display: none;' : ''; ?>">
                <input type="url"
                    name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][redirect_url]"
                    placeholder="https://example.com"
                    value="<?php echo esc_url($location['redirect_url']); ?>"
                    style="width: 400px;">
            </p>

            <button type="button" class="button remove-location">Remove Location</button>
        </div>
<?php
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('settings_page_geo-dynamic-headings' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'gdh-admin-script',
            plugins_url('assets/js/admin.js', dirname(__FILE__)),
            array('jquery'),
            '1.0.0',
            true
        );
    }
}

if (is_admin()) {
    new GDH_Settings_Page();
}
