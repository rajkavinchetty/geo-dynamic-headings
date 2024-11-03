<?php
if (!defined('ABSPATH')) {
    exit;
}

class Geo_Heading_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'geo_heading';
    }

    public function get_title()
    {
        return esc_html__('Geo Heading', 'geo-dynamic-headings');
    }

    public function get_icon()
    {
        return 'eicon-heading';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['heading', 'geo', 'location', 'dynamic'];
    }

    private function get_location_options()
    {
        $locations = get_option('gdh_locations');
        $options = [];

        if (!empty($locations['locations'])) {
            foreach ($locations['locations'] as $location) {
                $options[$location['code']] = $location['name'];
            }
        }

        return $options;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'geo-dynamic-headings'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'geo_location',
            [
                'label' => esc_html__('Geo Location', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_location_options(),
                'default' => $this->get_default_location_code(),
            ]
        );

        $repeater->add_control(
            'heading_text',
            [
                'label' => esc_html__('Heading Text', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Heading Text', 'geo-dynamic-headings'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'headings_list',
            [
                'label' => esc_html__('Headings', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'geo_location' => $this->get_default_location_code(),
                        'heading_text' => esc_html__('Default Heading', 'geo-dynamic-headings'),
                    ],
                ],
                'title_field' => '{{{ geo_location }}}: {{{ heading_text }}}',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'geo-dynamic-headings'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => esc_html__('Text Color', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .geo-heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .geo-heading',
            ]
        );

        $this->add_responsive_control(
            'heading_align',
            [
                'label' => esc_html__('Alignment', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'geo-dynamic-headings'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'geo-dynamic-headings'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'geo-dynamic-headings'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .geo-heading' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Get the default location code
        $default_location_code = $this->get_default_location_code();

        // If no cookie is set, use the default location code
        $current_geo = isset($_COOKIE['GEO']) ? strtolower($_COOKIE['GEO']) : $default_location_code;

        $heading_text = '';
        // First try to find a heading for the current geo
        foreach ($settings['headings_list'] as $heading) {
            if (strtolower($heading['geo_location']) === $current_geo) {
                $heading_text = $heading['heading_text'];
                break;
            }
        }

        // If no matching geo found, use the heading for the default location
        if (empty($heading_text)) {
            foreach ($settings['headings_list'] as $heading) {
                if (strtolower($heading['geo_location']) === $default_location_code) {
                    $heading_text = $heading['heading_text'];
                    break;
                }
            }
        }

        // Final fallback
        if (empty($heading_text)) {
            $heading_text = esc_html__('Default Heading', 'geo-dynamic-headings');
        }

        printf('<h2 class="geo-heading">' . $heading_text . '</h2>');
    }

    private function get_default_location_code()
    {
        $locations = get_option('gdh_locations');
        if (!empty($locations['locations'])) {
            // First try to find the explicitly set default location
            foreach ($locations['locations'] as $location) {
                if (isset($location['is_default']) && $location['is_default'] === '1') {
                    return $location['code'];
                }
            }
            // If no explicit default, return the first location's code
            return $locations['locations'][0]['code'];
        }
        return 'en-us'; // Fallback to en-us if no locations are set
    }
}
