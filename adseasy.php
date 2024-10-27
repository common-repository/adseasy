<?php
/*
Plugin Name: Ads Easy
Plugin URI: http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/ads-easy
Description: If you don't want to have Ads in your posts and you don't need other stats than those you get from wordpress and your adservers, this is the most easy solution. Place the code you get to the widget, style the widget and define, on what pages it shows up and to what kind of visitors. 
Version: 3.3
Author: Stefan Crämer
Author URI: http://www.stefan-craemer.com
License: GPL3
Text Domain: adseasy
Domain Path: /languages 
*/

/*  Copyright 2011 - 2016 Stefan Crämer  (email : support@atelier-fuenf.de)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

/* Stop direct call */

defined('ABSPATH') OR exit;

define( 'AE_PATH', plugin_dir_path(__FILE__) );
define( 'AE_BASE', plugin_basename(__FILE__) );

# loading the framework
if (!class_exists('A5_FormField')) require_once AE_PATH.'class-lib/A5_FormFieldClass.php';
if (!class_exists('A5_OptionPage')) require_once AE_PATH.'class-lib/A5_OptionPageClass.php';
if (!class_exists('A5_DynamicFiles')) require_once AE_PATH.'class-lib/A5_DynamicFileClass.php';
if (!class_exists('A5_Widget')) require_once AE_PATH.'class-lib/A5_WidgetClass.php';

#loading plugin specific classes
if (!class_exists('AE_Admin')) require_once AE_PATH.'class-lib/AE_AdminClass.php';
if (!class_exists('AE_DynamicCSS')) require_once AE_PATH.'class-lib/AE_DynamicCSSClass.php';
if (!class_exists('Ads_Easy_Widget')) require_once AE_PATH.'class-lib/AE_WidgetClass.php';

class AdsEasy {
	
	private static $options;
	
	function __construct() {
		
		self::$options = get_option('ae_options');
		
		// import laguage files
	
		load_plugin_textdomain('adseasy', false , basename(dirname(__FILE__)).'/languages');
		
		register_activation_hook(__FILE__, array($this, '_install'));
		register_deactivation_hook(__FILE__, array($this, '_uninstall'));
		
		add_filter('plugin_row_meta', array($this, 'register_links'), 10, 2);
		add_filter('plugin_action_links', array($this, 'register_action_links'), 10, 2);
		
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		
		if (true == WP_DEBUG):
		
			add_action('wp_before_admin_bar_render', array($this, 'admin_bar_menu'));
		
		endif;
		
		if (@!array_key_exists('flushed', self::$options)) add_action('init', array ($this, 'update_rewrite_rules'));
		
		/**
		 *
		 * Attaching stylesheet, if neccessary
		 *
		 */
		
		if (!empty(self::$options['ae_css'])) $AE_DynamicCSS = new AE_DynamicCSS;
				
		/**
		 *
		 * Getting the Adsense Tags and the new button to the editor
		 *
		 */
		
		if (isset(self::$options['use_google_tags'])) :
		
			// add the button to the editor and the shortcode to wp
		
			if (!class_exists('A5_AddMceButton')) require_once AE_PATH.'class-lib/A5_MCEButtonClass.php';
		
			add_action('wp_head', array($this, 'write_header_info'), 1000);
			
			add_shortcode('ae_ignore_tag', array($this, 'set_ignore_tags'));
			
			add_filter('loop_start', array($this, 'google_start'));
			add_filter('loop_end', array($this, 'google_end'));
			
			$tinymce_button = new A5_AddMceButton ('adseasy', 'AdsEasy', 'mce_buttons');
			
		endif;
		
		$AE_Admin = new AE_Admin;
	
	}
	
	/* attach JavaScript file for textarea resizing */
	
	function enqueue_scripts($hook) {
		
		if ($hook != 'widgets.php' && $hook != 'post.php' && $hook != 'plugins_page_ads-easy-settings') return;
		
		$min = (SCRIPT_DEBUG == false) ? '.min.' : '.';
		
		wp_register_script('ta-expander-script', plugins_url('ta-expander'.$min.'js', __FILE__), array('jquery'), '3.0', true);
		wp_enqueue_script('ta-expander-script');
	
	}
	
	//Additional links on the plugin page
	
	function register_links($links, $file) {
		
		if ($file == AE_BASE) :
			
			$links[] = '<a href="http://wordpress.org/extend/plugins/adseasy/faq/" target="_blank">'.__('FAQ', 'adseasy').'</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VRMSV3NXQDXSA" target="_blank">'.__('Donate', 'adseasy').'</a>';
		
		endif;
		
		return $links;
	
	}
	
	function register_action_links( $links, $file ) {
		
		if ($file == AE_BASE) array_unshift($links, '<a href="'.admin_url( 'plugins.php?page=ads-easy-settings' ).'">'.__('Settings', 'adseasy').'</a>');
	
		return $links;
	
	}
	
	/**
	 *
	 * Getting the Adsense Tags in the defined areas of the code and create hooks for other plugins
	 *
	 */
	
	function write_header_info() {
		
		echo "<!-- Google AdSense Tags powered by Stefan Crämer's AdEasy ".__('http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/ads-easy', 'adseasy')." -->\n";
		
	}
	
	function google_start() {
		
		echo "<!-- google_ad_section_start -->\n";
		
	}
	
	function google_end() {
		
		echo "<!-- google_ad_section_end -->\n";
		
	}
	
	/**
	 *
	 * shortcode for the ignore tags
	 *
	 */
	function set_ignore_tags($atts, $content = null){
		
		$eol = "\n";
		
		return $eol.'<!-- google_ad_section_end -->'.$eol.'<!-- google_ad_section_start(weight=ignore) -->'.$eol.do_shortcode($content).$eol.'<!-- google_ad_section_end -->'.$eol.'<!-- google_ad_section_start -->'.$eol;
	}
	
	// Adding the options
	
	static function _install() {
		
		$compress = (SCRIPT_DEBUG) ? false : true;
		
		$options = array(
			'ae_time' => 5,
			'inline' => false,
			'compress' => $compress,
			'flushed' => true,
			'css_cache' => ''
		);
		
		add_option('ae_options', $options);
		
		add_rewrite_rule('a5-framework-frontend.css', 'index.php?A5_file=wp_css', 'top');
		add_rewrite_rule('a5-framework-frontend.js', 'index.php?A5_file=wp_js', 'top');
		add_rewrite_rule('a5-framework-backend.css', 'index.php?A5_file=admin_css', 'top');
		add_rewrite_rule('a5-framework-backend.js', 'index.php?A5_file=admin_js', 'top');
		add_rewrite_rule('a5-framework-login.css', 'index.php?A5_file=login_css', 'top');
		add_rewrite_rule('a5-framework-login.js', 'index.php?A5_file=login_js', 'top');
		add_rewrite_rule('a5-export-settings', 'index.php?A5_file=export', 'top');
		flush_rewrite_rules();
		
	}
	
	// Deleting the options
	
	static function _uninstall() {
		
		delete_option('ae_options');
		
		flush_rewrite_rules();
		
	}
	
	function update_rewrite_rules() {
		
		add_rewrite_rule('a5-framework-frontend.css', 'index.php?A5_file=wp_css', 'top');
		add_rewrite_rule('a5-framework-frontend.js', 'index.php?A5_file=wp_js', 'top');
		add_rewrite_rule('a5-framework-backend.css', 'index.php?A5_file=admin_css', 'top');
		add_rewrite_rule('a5-framework-backend.js', 'index.php?A5_file=admin_js', 'top');
		add_rewrite_rule('a5-framework-login.css', 'index.php?A5_file=login_css', 'top');
		add_rewrite_rule('a5-framework-login.js', 'index.php?A5_file=login_js', 'top');
		add_rewrite_rule('a5-export-settings', 'index.php?A5_file=export', 'top');
		
		flush_rewrite_rules();
		
		self::$options['flushed'] = true;
		
		update_option('rpw_options', self::$options);
	
	}
	
	/**
	 *
	 * Adds a link to the settings to the admin bar in case WP_DEBUG is true
	 *
	 */
	function admin_bar_menu() {
		
		global $wp_admin_bar;
		
		if (!is_super_admin() || !is_admin_bar_showing()) return;
		
		$wp_admin_bar->add_node(array('parent' => '', 'id' => 'a5-framework', 'title' => 'A5 Framework'));
		
		$wp_admin_bar->add_node(array('parent' => 'a5-framework', 'id' => 'a5-adseasy', 'title' => 'Ads Easy', 'href' => admin_url( 'plugins.php?page=ads-easy-settings' )));
		
	}

} // end of class

$AdsEasy = new AdsEasy;

?>