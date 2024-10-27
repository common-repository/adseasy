<?php

/**
 *
 * Class AE Dynamic CSS
 *
 * Extending A5 Dynamic Files
 *
 * Presses the dynamical CSS of the Ads Easy Widget into a virtual style sheet
 *
 */

class AE_DynamicCSS extends A5_DynamicFiles {
	
	private static $options;
	
	function __construct() {
		
		self::$options =  get_option('ae_options');
		
		if (!isset(self::$options['inline'])) self::$options['inline'] = false;
		
		if (!array_key_exists('priority', self::$options)) self::$options['priority'] = false;
		
		if (!array_key_exists('compress', self::$options)) self::$options['compress'] = true;
		
		$this->a5_styles('wp', 'all', self::$options['inline'], self::$options['priority']);
		
		$ae_styles = self::$options['css_cache'];
		
		if (!$ae_styles) :
		
			$eol = (self::$options['compress']) ? '' : "\n";
			$tab = (self::$options['compress']) ? ' ' : "\t";
			
			$css_selector = 'widget_ads_easy_widget[id^="ads_easy_widget"]';
			
			$ae_styles .= (!self::$options['compress']) ? $eol.'/* CSS portion of Ads Easy */'.$eol.$eol : '';
			
			$style = str_replace('; ', ';'.$eol.$tab, str_replace(array("\r\n", "\n", "\r"), ' ', self::$options['ae_css']));
	
			$ae_styles .= parent::build_widget_css($css_selector, '').'{'.$eol.$tab.$style.$eol.'}'.$eol;
			
			self::$options['css_cache'] = $ae_styles;
			
			update_option('ae_options', self::$options);
			
		endif;
		
		parent::$wp_styles .= $ae_styles;

	}
	
} // AE_Dynamic CSS

?>