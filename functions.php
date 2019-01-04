<?php

namespace Waboot\components\style;

use WBF\modules\components\Component;
use WBF\modules\components\ComponentsManager;

/**
 * @return bool|Component
 */
function get_style_component_instance(){
	$activeComponents = ComponentsManager::getActiveComponents();
	if(isset($activeComponents['style'])){
		return $activeComponents['style'];
	}
	return false;
}

/**
 * @return string|bool
 */
function get_theme_options_css_src_path(){
	$styleComponents = get_style_component_instance();
	if(!$styleComponents){
		return false;
	}
	$path = apply_filters("waboot/assets/theme_options_style_file/source", $styleComponents->directory."/_theme-options.src");
	return is_string($path) ? $path : false;
}

function get_theme_options_css_dest_path(){
	$path = apply_filters("waboot/assets/theme_options_style_file/destination", WBF()->get_working_directory()."/theme-options.css");
	return is_string($path) ? $path : false;
}

function get_theme_options_css_dest_uri(){
	$path = get_theme_options_css_dest_path();
	return is_string($path) ? \WBF\components\utils\Paths::path_to_url($path) : false;
}

/**
 * Print out the custom css file from the theme options value. Called during "update_option".
 *
 * It's a callback set in options "save action" param in options.php
 *
 * @param $option
 * @param $old_value
 * @param $value
 *
 * @return FALSE|string
 * @throws \Exception
 */
function deploy_theme_options_css($option, $old_value, $value){
	$input_file_path = get_theme_options_css_src_path();
	$output_file_path = get_theme_options_css_dest_path();

	if(!is_array($value)) return false;

	$output_string = "";

	$inputFile = new \SplFileInfo($input_file_path);
	if(!$inputFile->isFile()){
		return false;
	}

	$outputFile = new \SplFileInfo($output_file_path);
	if(!is_dir($outputFile->getPath())){
		if(!wp_mkdir_p($output_file_path) && !is_dir($outputFile->getPath())){
			return false;
		}
	}

	if(has_filter('waboot/theme_options_css_file/content')){
		$additional_content = apply_filters('waboot/theme_options_css_file/content','');
		if(is_string($additional_content) && $additional_content !== ''){
			$tmp_input_file_path = WBF()->get_working_directory().'/_theme-options.src.addons';
			if(is_file($tmp_input_file_path)){
				unlink($tmp_input_file_path);
			}
			$additional_content = "\r\n".$additional_content."\r\n";
			$inputFile_content = file_get_contents($inputFile->getPathname());
			$inputFile_content .= $additional_content;
			$r = file_put_contents($tmp_input_file_path,$inputFile_content);
			if(is_file($tmp_input_file_path)){
				$inputFile = new \SplFileInfo($tmp_input_file_path);
			}
		}
	}

	$inputFileObj = $inputFile->openFile( "r" );
	$outputFileObj = $outputFile->openFile( "w" );
	$byte_written = 0;

	$parseLine = function($line) use($option,$old_value,$value){
		$genericOptionfindRegExp = "/{{ ?([a-zA-Z0-9\-_]+) ?}}/";
		$funcRegExp = "/{{ ?apply:([a-zA-Z\-_]+)\(([a-zA-Z0-9\-_, ]+)\) ?}}/";
		$fontOptionfindRegExp = "/\{\{ ?font: ?([a-z]+) ?\}\}/";
		$assignOptionFindRegExp = "/\{\{ ?font-assignment: ?([a-z]+) ?\}\}/";
		//Replace {{ <theme-option-id> }}
		if(preg_match($genericOptionfindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value)){
				if($value[ $matches[1] ] != ""){
					$line = preg_replace( $genericOptionfindRegExp, $value[$matches[1]], $line);
				}else{
					$line = "\t/*{$matches[1]} is empty*/\n";
				}
			}else{
				$line = "\t/*{$matches[1]} not found*/\n";
			}
		}

		//Replace {{ apply:<func>(<theme-option-id>) }}
		if(preg_match($funcRegExp, $line, $matches)){
			require_once get_template_directory()."/inc/styles-functions.php";
			if(count($matches) == 3 && function_exists($matches[1])){
				$func = $matches[1];
				$args = explode(",",$matches[2]);
				foreach ($args as $k => $v){
					if(isset($value[$v])){
						$args[$k] = $value[$v]; //If one of the args is a theme option name, replace it with it's value!
					}
				}
				if(function_exists($func)){
					$r = call_user_func($func,$args);
					$line = preg_replace( $funcRegExp, $r, $line);
				}else{
					$line = "\t/*$func not found*/\n";
				}
			}else{
				$line = "\t/*Invalid function call*/\n";
			}
		}

		//Replace {{ font: <theme-option-id> }}
		if(preg_match($fontOptionfindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value) && $value[ $matches[1] ] != "" && isset($value[ $matches[1] ]['import'])){
				$fonts = $value[ $matches[1] ]['import'];
				$families = '';
				$subsets = '';
				$arr_subsets = [];

				if (is_array($fonts) && count($fonts) > 0) {
					$i = 0;
					foreach ( $fonts as $font ) {
						$families_separator = ($i>0) ? '|' : '';

						if (count($font['weight']) == 1){
							if ($font['weight'][0] == 'regular') {
								$weight = "";
							} else {
								$weight = ":" . implode(",", $font['weight']);
							}
						} else if (count($font['weight'])>1) {
							$weight = ":".implode(",", $font['weight']);
							$weight = str_replace ( "regular" , "400" , $weight);
						} else {
							$weight = "";
						}

						$font['family'] = preg_replace("/[\s]/", "+", $font['family']);
						$families .= $families_separator . $font['family'] . $weight;

						// builds an array with all the subsets of all the selected fonts
						foreach ( $font['subset'] as $subset ) {
							if ($subset != 'latin') {       // latin is excluded
								array_push($arr_subsets, $subset);
							}
						}
						$i++;
					}

					$arr_subsets = array_unique($arr_subsets);
					if (count($arr_subsets) > 0) {          // if we have some subset different from latin
						// another loop for array of subsets
						$j = 0;
						foreach ( $arr_subsets as $subset ) {
							$subsets_start = ($j==0) ? '&subset=' : '';
							$subsets_separator = ($j>0) ? ',' : '';
							$subsets .= $subsets_start . $subsets_separator . $subset; // e.g. &subset=greek,latin-ext';

							$j++;
						}
					}

					$css_rule = "@import 'https://fonts.googleapis.com/css?family=".$families."".$subsets."';";
					$line = preg_replace( $fontOptionfindRegExp, $css_rule, $line);
				} else {
					$line = "\t/*{$matches[1]} no fonts assigned*/\n";
				}
			}else{
				$line = "\t/*{$matches[1]} not found or invalid*/\n";
			}
		}

		//Replace {{ font-assignment: <theme-option-id> }}
		if(preg_match($assignOptionFindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value) &&  $value[ $matches[1] ] != "" && isset($value[ $matches[1] ]['assign'])){
				$assignments = $value[ $matches[1] ]['assign'];
				$css_rule = "";
				foreach($assignments as $selector => $props){

					if($props['weight'] == "regular") {
						$props['weight'] = "400";
					} elseif (preg_match('/italic/', $props['weight'])) {
						$props['weight'] = preg_replace('/italic/', '', $props['weight']);
						if ($props['weight'] == '') $props['weight'] = '400';
						$props['style'] = 'italic';
					}
					$selector = preg_replace('/-/',',', $selector);
					if($props['family'] !== ''){
						$css_rule .= "{$selector}{\n";
						$css_rule .= "\tfont-family: '{$props['family']}';\n";
						$css_rule .= "\tfont-weight: {$props['weight']};\n";
						if ($props['style'] != '') $css_rule .= "\tfont-style: {$props['style']};\n";
						$css_rule .= "}\n";
					}
				}
				$line = preg_replace( $assignOptionFindRegExp, $css_rule, $line);
			}else{
				$line = "\t/*{$matches[1]} not found*/\n";
			}
		}

		return $line;
	};

	while(!$inputFileObj->eof()){
		$line = $inputFileObj->fgets();
		$parsedLine = $parseLine($line);
		$byte_written += $outputFileObj->fwrite($parsedLine);
	}

	return $output_string;
}