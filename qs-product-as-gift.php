<?php
/*
Plugin Name: Quiero Spain product as gift
Plugin URI: https://novadevs.com/
Description: Plugin que habilita la posibilidad de agregar al carrito un producto en forma de regalo
Version: 1.0.0-rc
Author: Bruno Lorente
Author URI: https://github.com/brunolorente
License: GPLv2 or later
Text Domain: novadevs
*/

if (!defined('QS_PAG')) {
    define('QS_PAG', plugin_dir_path(__FILE__));
}

// Including admin page file
require_once(QS_PAG . '/activation.php');

/**
 * Check if WooCommerce is activated
 */
if (! function_exists('is_woocommerce_activated')) {
    function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * The plugin CSS
 */
if (! function_exists('QS_PAG_styles')) {
    function QS_PAG_styles()
    {
        wp_register_style('font-awesome', 'https://use.fontawesome.com/releases/v5.7.0/css/all.css');
        wp_enqueue_style('font-awesome');
        wp_register_style('QS_PAG_css', plugin_dir_url(__FILE__) . 'css/main.css');
        wp_enqueue_style('QS_PAG_css');
    }
}
add_action('wp_enqueue_scripts', 'QS_PAG_styles');

/**
 * The plugin JS
 */
if (! function_exists('QS_PAG_scripts')) {
    function QS_PAG_scripts()
    {
        // Own scripts
        wp_deregister_script('QS_PAG_scripts');
        wp_enqueue_script('QS_PAG_scripts', plugin_dir_url(__FILE__) . 'scripts/main.js', array('jquery', 'wc-add-to-cart-variation'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'QS_PAG_scripts');



function myplugin_plugin_path()
{
    // gets the absolute path to this plugin directory
    return untrailingslashit(plugin_dir_path(__FILE__));
}
  
// https://www.skyverge.com/blog/override-woocommerce-template-file-within-a-plugin/
function myplugin_woocommerce_locate_template($template, $template_name, $template_path)
{
    global $woocommerce;
  
    $_template = $template;
  
    if (! $template_path) {
        $template_path = $woocommerce->template_url;
    }
  
    $plugin_path  = myplugin_plugin_path() . '/woocommerce/';
  
    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
        $template_path . $template_name,
        $template_name
      )
    );
  
    // Modification: Get the template from this plugin, if it exists
    if (! $template && file_exists($plugin_path . $template_name)) {
        $template = $plugin_path . $template_name;
    }
  
    // Use default template
    if (! $template) {
        $template = $_template;
    }
  
    // Return what we found
    return $template;
}

add_filter('woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 10, 3);


    /**
     * Output a list of variation attributes for use in the cart forms.
     *
     * @param array $args Arguments.
     * @since 2.4.0
     */
    function wc_dropdown_variation_attribute_options($args = array())
    {
        $args = wp_parse_args(
            apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args),
            array(
                'options'          => false,
                'attribute'        => false,
                'product'          => false,
                'selected'         => false,
                'name'             => '',
                'id'               => '',
                'class'            => '',
                'show_option_none' => __('Choose an option', 'woocommerce'),
            )
        );

        // Get selected value.
        if (false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product) {
            $selected_key = 'attribute_' . sanitize_title($args['attribute']);
            // phpcs:disable WordPress.Security.NonceVerification.Recommended
            $args['selected'] = isset($_REQUEST[ $selected_key ]) ? wc_clean(wp_unslash($_REQUEST[ $selected_key ])) : $args['product']->get_variation_default_attribute($args['attribute']);
            // phpcs:enable WordPress.Security.NonceVerification.Recommended
        }
        $options               = $args['options'];
        $product               = $args['product'];
        $attribute             = $args['attribute'];
        $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
        $id                    = $args['id'] ? $args['id'] : sanitize_title($attribute);
        $class                 = $args['class'];
        $show_option_none      = (bool) $args['show_option_none'];
        $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce'); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

        if (empty($options) && ! empty($product) && ! empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options    = $attributes[ $attribute ];
        }

        if ($args['attribute'] == "pa_gift") {
            $html  = '<select id="' . esc_attr($id) . '" class="d-none ' . esc_attr($class) . '" name="' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
        } else {
            $html  = '<select id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" name="' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
        }
        $html .= '<option value="">' . esc_html($show_option_none_text) . '</option>';

        if (! empty($options)) {
            if ($product && taxonomy_exists($attribute)) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms(
                    $product->get_id(),
                    $attribute,
                    array(
                        'fields' => 'all',
                    )
                );

                foreach ($terms as $term) {
                    if (in_array($term->slug, $options, true)) {
                        $html .= '<option value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product)) . '</option>';
                    }
                }
            } else {
                foreach ($options as $option) {
                    // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                    $selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);
                    $html    .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product)) . '</option>';
                }
            }
        }

        $html .= '</select>';


        if ($args['attribute'] == "pa_gift") {
            if (! empty($options)) {
                $html .= '<div class="switch-text"><i class="fa fa-gift"></i> Regálalo';
                $html .= '<label id="product-combinations" class="switch">';
                if ($product && taxonomy_exists($attribute)) {
                    // Get terms if this is a taxonomy - ordered. We need the names too.
                    $terms = wc_get_product_terms(
                        $product->get_id(),
                        $attribute,
                        array(
                            'fields' => 'all',
                        )
                    );
    
                    if (count($terms) == 2) {
                        $html .= '<input type="checkbox" id="gift-switch" data-target="false">';
                        $html .= '<div class="slider round"></div>';
                        $html .= '</label></div>';

                        $html .= '<div id="hidden-block">';
                        foreach ($terms as $term) {
                            if (in_array($term->slug, $options, true)) {
                                $html .= '<input id="'.esc_attr($term->slug).'"  data-target="#'.esc_attr($id).'" data-value="'. esc_attr($term->slug) .'" type="hidden" value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>';
                            }
                        }
                        $html .= '</div>';
                    } else {
                        echo '<div class="alert alert-warning"><p>ERROR: This module just works with two options</p></div>';
                    }
                }
            }
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args);
    }
