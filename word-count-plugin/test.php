<?php 

/*  
    Plugin Name: Word Count Plugin
    Description: Counts the words, characters and states how long it will take to read.
    Version: 1.0
    Author: Ash
    Author URI: https://ashblignaut.co.za
    Text Domain: wcpdomain
    Domain Path: /languages
*/

class WordCountAndTimePlugin {

    function __construct() {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
    }

    function languages() {
        load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content) {
        if( (is_main_query() && is_single() && (get_option('wcp_wordcount', '1') || get_option('wcp_charcount', '1') || get_option('wcp_readtime', '1')) ) ) {
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content) {
        $html = '<h3>'. esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

        // Get word count once coz both wordcount and read time will need it
        if(get_option('wcp_wordcount', '1') || get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if(get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . esc_html__('words', 'wcpdomain'). '<br>';
        }
        if(get_option('wcp_charcount', '1')) {
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>';
        }
        if(get_option('wcp_readtime', '1')) {
            $html .= 'This post will take about ' . round($wordCount/225) . ' min(s) to read.<br>';
        }

        $html .= '</p>';

        if(get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function settings() {
        /* 
        //  add_settings_section($id, $title, $callback, $page)
        //  add_settings_field($id, $title, $callback, $page, $section, $args)
        //  register_setting($option_group, $option_name, $sanitize_callback)
        */

        add_settings_section('wcp_first_section', NULL, NULL, 'word-count-settings-page');

        // Location
        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        // Headline Text
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        // Word Count
        add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkBoxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        // Character Count
        add_settings_field('wcp_charcount', 'Character Count', array($this, 'checkBoxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charcount'));
        register_setting('wordcountplugin', 'wcp_charcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        // Read Time
        add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkBoxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    // Custom error validation
    function sanitizeLocation($input) {
        if($input != '0' && $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be Beginning or End');
            return get_option('wcp_location');
        }
        return $input;
    }

    function checkBoxHTML($args) { ?>
        <input type="checkbox" name="<?php echo $args['theName']; ?>" value="1" <?php checked(get_option($args['theName']), '1'); ?>>
    <?php }

    function headlineHTML() { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
    <?php }

    function locationHTML() { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of post</option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of post</option>
        </select>
    <?php }

    function adminPage() {
        // add_options_page($page_title, $menu_title, $capability, $menu_slug, $function)
        // add_options_page('Word Count Settings', 'Word Count', 'manage_options', 'word-count-settings-page', array($this, 'ourHTML'));
        add_options_page('Word Count Settings', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'ourHTML'));
    }
    function ourHTML() { ?>
    
        <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('wordcountplugin');
                    do_settings_sections('word-count-settings-page');
                    submit_button();
                ?>
            </form>
        </div>

    <?php }

} 

$wordCountAndTimePlugin = new WordCountAndTimePlugin();



