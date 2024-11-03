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

        // Initialize default location if not exists
        $this->maybe_initialize_default_location();
    }

    private function maybe_initialize_default_location()
    {
        $options = get_option('gdh_locations');
        if (empty($options) || !is_array($options)) {
            $default_location = array(
                'locations' => array(
                    array(
                        'code' => 'en-us',
                        'name' => 'USA',
                        'redirect_url' => '',
                        'is_redirect' => '0',
                        'is_default' => '1'
                    )
                )
            );
            update_option('gdh_locations', $default_location);
        }
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
            $has_default = false;
            foreach ($input['locations'] as $idx => $location) {
                if (!empty($location['code'])) {
                    $is_default = isset($location['is_default']) ? '1' : '0';
                    if ($is_default === '1') {
                        $has_default = true;
                    }

                    $new_input['locations'][$idx] = array(
                        'code' => sanitize_text_field($location['code']),
                        'name' => sanitize_text_field($location['name']),
                        'redirect_url' => esc_url_raw($location['redirect_url']),
                        'is_redirect' => isset($location['is_redirect']) ? '1' : '0',
                        'is_default' => $is_default
                    );
                }
            }

            // Ensure at least one default location exists
            if (!$has_default && !empty($new_input['locations'])) {
                $first_key = array_key_first($new_input['locations']);
                $new_input['locations'][$first_key]['is_default'] = '1';
            }
        }

        return $new_input;
    }

    public function section_info()
    {
        echo 'Configure your locations below. One location must be set as default. The default location will be used when no specific location is selected.';
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
                'is_redirect' => '0',
                'is_default' => '0'
            )); ?>
        </script>
    <?php
    }

    private function render_location_row($idx, $location)
    {
        $is_default = isset($location['is_default']) ? $location['is_default'] : '0';
    ?>
        <div class="location-row" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;">
            <p>
                <input type="text"
                    name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][code]"
                    placeholder="Location Code (e.g., en-us, ca, uk)"
                    value="<?php echo esc_attr($location['code']); ?>"
                    style="width: 200px;"
                    required>

                <input type="text"
                    name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][name]"
                    placeholder="Location Name (e.g., United States)"
                    value="<?php echo esc_attr($location['name']); ?>"
                    style="width: 200px;"
                    required>

                <label style="margin-left: 10px;">
                    <input type="checkbox"
                        name="gdh_locations[locations][<?php echo esc_attr($idx); ?>][is_default]"
                        value="1"
                        <?php checked($is_default, '1'); ?>
                        class="default-toggle">
                    Default Location
                </label>
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

            <?php if ($is_default !== '1'): ?>
                <button type="button" class="button remove-location">Remove Location</button>
            <?php endif; ?>
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

        // Add custom script to handle default location logic
        wp_add_inline_script('gdh-admin-script', '
            jQuery(document).ready(function($) {
                // Handle default location radio-like behavior
                $(document).on("change", ".default-toggle", function() {
                    if($(this).is(":checked")) {
                        $(".default-toggle").not(this).prop("checked", false);
                    }
                });

                // Prevent removal of default location
                $(document).on("click", ".remove-location", function() {
                    var row = $(this).closest(".location-row");
                    if(row.find(".default-toggle").is(":checked")) {
                        alert("Cannot remove default location");
                        return false;
                    }
                    row.remove();
                });
            });
        ');
    }
}

if (is_admin()) {
    new GDH_Settings_Page();
}
