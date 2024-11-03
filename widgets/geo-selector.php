<?php
if (!defined('ABSPATH')) {
    exit;
}

class Geo_Selector_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'geo_selector';
    }

    public function get_title()
    {
        return esc_html__('Geo Selector', 'geo-dynamic-headings');
    }

    public function get_icon()
    {
        return 'eicon-select';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['geo', 'location', 'selector', 'country'];
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

        $this->add_control(
            'selector_type',
            [
                'label' => esc_html__('Selector Type', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'dropdown' => esc_html__('Dropdown', 'geo-dynamic-headings'),
                    'buttons' => esc_html__('Buttons', 'geo-dynamic-headings'),
                ],
                'default' => 'dropdown',
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
            'selector_color',
            [
                'label' => esc_html__('Text Color', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .geo-selector' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .geo-selector-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'active_color',
            [
                'label' => esc_html__('Active Button Color', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition' => [
                    'selector_type' => 'buttons',
                ],
                'selectors' => [
                    '{{WRAPPER}} .geo-selector-button.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'selector_typography',
                'selector' => '{{WRAPPER}} .geo-selector, {{WRAPPER}} .geo-selector-button',
            ]
        );

        $this->add_responsive_control(
            'selector_align',
            [
                'label' => esc_html__('Alignment', 'geo-dynamic-headings'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'geo-dynamic-headings'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'geo-dynamic-headings'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Right', 'geo-dynamic-headings'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .geo-selector-wrapper' => 'display: flex; justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $current_geo = isset($_COOKIE['GEO']) ? strtolower($_COOKIE['GEO']) : 'default';

        $locations = GeoDynamicHeadings::get_locations();

        echo '<div class="geo-selector-wrapper">';

        if ($settings['selector_type'] === 'dropdown') {
            echo '<select class="geo-selector" onchange="setGeoLocation(this.value)">';
            foreach ($locations as $value => $label) {
                $selected = ($current_geo === $value) ? 'selected' : '';
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($value),
                    esc_attr($selected),
                    esc_html($label)
                );
            }
            echo '</select>';
        } else {
            echo '<div class="geo-selector-buttons">';
            foreach ($locations as $value => $label) {
                $active = ($current_geo === $value) ? 'active' : '';
                printf(
                    '<button class="geo-selector-button %s" onclick="setGeoLocation(\'%s\')">%s</button>',
                    esc_attr($active),
                    esc_attr($value),
                    esc_html($label)
                );
            }
            echo '</div>';
        }

        echo '</div>';
    }
}
