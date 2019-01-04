<?php
/**
Component Name: Style
Description: Adds style options to Waboot
Category: Styles
Tags: Styles
Version: 1.1.0
Author: Waboot Team <info@waboot.io>
Author URI: http://www.waboot.io
 */

require_once 'functions.php';

class Style extends \WBF\modules\components\Component{
	public function setup() {
		parent::setup();
	}

	public function run() {
		parent::run();
	}

	public function styles() {
		$file = \Waboot\components\style\get_theme_options_css_dest_path();
		if(!is_readable($file)){
			return;
		}
		$assets['waboot-theme-options-style'] = [
			'uri' => \Waboot\components\style\get_theme_options_css_dest_uri(),
			'path' => $file,
			'type' => 'css'
		];
		$am = new \WBF\components\assets\AssetsManager($assets);
		try{
			$am->enqueue();
		}catch (\Exception $e){
			trigger_error($e->getMessage(),E_USER_NOTICE);
		}
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$orgzr->add_section('styles', _x('Styles','Theme Options tab','waboot'));

		$orgzr->set_section('styles');

		$orgzr->add([
			'name' => _x( 'Backgrounds', 'Theme options', 'waboot' ),
			'desc' => _x( 'Settings about page backgrounds', 'waboot' ),
			'type' => 'info'
		]);

		$orgzr->set_group("css_injection");

		$orgzr->add([
			'name' => _x('Body Background Color', 'Theme options', 'waboot'),
			'desc' => _x('Change the body background color.', 'Theme options', 'waboot'),
			'id' => 'body_bgcolor',
			'std' => "#ededed",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->add([
			'name' => _x( 'Body Background Image', 'Theme options', 'waboot' ),
			'desc' => _x( 'Upload a background image, or specify the image address of your image. (http://yoursite.com/image.png)', 'Theme options', 'waboot' ),
			'id' => 'body_bgimage',
			'std' => '',
			'type' => 'upload',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->add([
			'name' => _x( 'Body Background Image Repeat', 'Theme options', 'waboot' ),
			'desc' => _x( 'Select how you want your background image to display.', 'waboot' ),
			'id' => 'body_bgrepeat',
			'type' => 'select',
			'options' => array( 'no-repeat' => 'No Repeat', 'repeat' => 'Repeat','repeat-x' => 'Repeat Horizontally', 'repeat-y' => 'Repeat Vertically' ),
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->add([
			'name' => _x( 'Body Background image position', 'Theme options', 'waboot' ),
			'desc' => _x( 'Select how you would like to position the background', 'waboot' ),
			'id' => 'body_bgpos',
			'std' => 'top left',
			'type' => 'select',
			'options' => array(
				'top left' => 'top left', 'top center' => 'top center', 'top right' => 'top right',
				'center left' => 'center left', 'center center' => 'center center', 'center right' => 'center right',
				'bottom left' => 'bottom left', 'bottom center' => 'bottom center', 'bottom right' => 'bottom right'
			),
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->add([
			'name' => _x( 'Body Background Attachment', 'Theme options', 'waboot' ),
			'desc' => _x( 'Select whether the background should be fixed or move when the user scrolls', 'Theme options', 'waboot' ),
			'id' => 'body_bgattach',
			'std' => 'scroll',
			'type' => 'select',
			'options' => array( 'scroll' => 'scroll','fixed' => 'fixed' ),
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->add([
			'name' => _x('Page Background color', 'Theme options', 'Theme options', 'waboot'),
			'desc' => _x('Change the page background color.', 'Theme options', 'waboot'),
			'id' => 'page_bgcolor',
			'type' => 'color',
			'std' => '#ffffff',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->add([
			'name' => _x('Content Background color', 'Theme options', 'Theme options', 'waboot'),
			'desc' => _x('Change the content background color.', 'Theme options', 'waboot'),
			'id' => 'content_bgcolor',
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->reset_group();
		$orgzr->set_group("std_options");

		/*
		 * CSS VARIABLES TAB
		 */

		$orgzr->add(array(
			'name' => _x( 'Styles', 'Theme options', 'Theme options', 'waboot' ),
			'desc' => _x( 'Settings about css styles', 'Theme options', 'waboot' ),
			'type' => 'info'
		));

		$orgzr->set_group("css_injection");

		$orgzr->add(array(
			'name' => _x('Text color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of html, body, p, ul, li', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_text_color',
			'std' => "#333333",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Title color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of h1, h2, h3, h4, h5, h6', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_title_color',
			'std' => "#333333",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Primary color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of @brand-primary.', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_brand_primary',
			'std' => "#428bca",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Success color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of @brand-success.', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_brand_success',
			'std' => "#5cb85c",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Info color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of @brand-info.', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_brand_info',
			'std' => "#5bc0de",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Warning color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of @brand-warning.', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_brand_warning',
			'std' => "#f0ad4e",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Danger color', 'Theme options', 'waboot'),
			'desc' => _x('Change the color of @brand-danger.', 'Theme options', 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_brand_danger',
			'std' => "#d9534f",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->add(array(
			'name' => _x('Well background', "Theme Options", 'waboot'),
			'desc' => _x('Change the color of @well-bg.', "Theme Options", 'waboot'),
			'class' => 'half_option',
			'id' => 'btstrp_well_bg',
			'std' => "#f5f5f5",
			'type' => 'color',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		));

		$orgzr->reset_group();
		$orgzr->set_group("std_options");

		/*
		 * TYPOGRAPHY
		 */

		$orgzr->add(array(
			'name' => _x( 'Typography', 'Theme options', 'waboot' ),
			'desc' => _x( 'Settings about typography', 'Theme options', 'waboot' ),
			'type' => 'info'
		));

		$orgzr->set_group("css_injection");

		$updateFontCacheLink = \class_exists(\WBF\includes\GoogleFontsRetriever::class) && \function_exists('\WBF\includes\GoogleFontsRetriever::get_update_font_cache_link') ? \WBF\includes\GoogleFontsRetriever::get_update_font_cache_link() : '#';

		$orgzr->add(array(
			'name' => _x('Google Fonts API Key', 'Theme options', 'waboot'),
			'desc' => sprintf(_x('Add here your google fonts api key. Update the fonts cache by clicking <a href="%s">here</a>.', 'Theme options', 'waboot'),$updateFontCacheLink),
			'class' => 'half_option',
			'id' => 'google_fonts_api_key',
			'std' => '',
			'type' => 'text'
		));

		$orgzr->add([
			'name' => _x('Fonts to load', "Theme Options", 'waboot'),
			'id' => 'fonts',
			'css_selectors' => ['body,p,ul', 'h1,h2,h3', 'h4,h5,h6'],
			'std' => [],
			'type' => 'fonts_selector',
			'fonts_type' => 'google',
			'save_action' => "\\Waboot\\components\\style\\deploy_theme_options_css"
		]);

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}