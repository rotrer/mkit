<?php
/**
 * Plugin Name: DFP Mediatrends - terra.cl
 * Plugin URI: http://mediatrends.cl
 * Description: Personalizacion de ads
 * Version: 1
 * Author: Mediatrends
 * Author URI: http://mediatrends.cl
 */

global $adunits;
$adunits = '{"92781973":{"id_tag_template":"#mid_se","div_display":"<div id=\"zona-92781973\" style=\"width:300px; height:250px;\"><script type=\"text\/javascript\">googletag.cmd.push(function() { googletag.display(\"zona-92781973\"); });<\/script><\/div>"},"92242453":{"id_tag_template":"#top_menu","div_display":"<div id=\"zona-92242453\" style=\"width:300px; height:250px;\"><script type=\"text\/javascript\">googletag.cmd.push(function() { googletag.display(\"zona-92242453\"); });<\/script><\/div>"},"92790253":{"id_tag_template":"#side_250","div_display":"<div id=\"zona-92790253\" style=\"width:970px; height:90px;\"><script type=\"text\/javascript\">googletag.cmd.push(function() { googletag.display(\"zona-92790253\"); });<\/script><\/div>"}}';

function activar_plugin() {
	global $adunits;

	$hasPreviuos = get_option('mt_plugin');
	if ($hasPreviuos) {
		cleanTagTemplates($hasPreviuos);
		add_option( 'mt_plugin', $adunits );
	} else {
		add_option( 'mt_plugin', $adunits );
	}
	

	$units = json_decode($adunits);
	$erroWrite = array();
	$pathTemplate = get_template_directory();
	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathTemplate), RecursiveIteratorIterator::SELF_FIRST);
	foreach($objects as $name => $object){
		if (preg_match('/^.+\.php$/i', $name)) {
			if (is_writable($name)) {
				$phpFileTemplate = file_get_contents($name);

				foreach ($units as $key => $unit) {
					$id_tag_template = str_replace("#", "", $unit->id_tag_template);
					preg_match('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', $phpFileTemplate, $matches);
					if (count($matches) > 0) {
						if (!empty($matches[2])) {
							$divReplace = '$1$4';
						} else {
							$divReplace = '$1$3';
						}
						$htmlAd = $unit->div_display;
						$result = preg_replace('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', $divReplace, $phpFileTemplate);
						$result = preg_replace('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', '$1' . $htmlAd . '$3', $result);
						file_put_contents($name, $result);
					}
				}
			} else {
				$erroWrite[] = $name;
			}
		}
	}
}
register_activation_hook( __FILE__, 'activar_plugin' );

function desactivar_plugin( $plugin, $network_activation ) {
	global $adunits;
	cleanTagTemplates($adunits);
	delete_option( 'mt_plugin' );
}
add_action( 'deactivated_plugin', 'desactivar_plugin', 10, 2 );

add_action('wp_head', 'head_setup_mt');
function head_setup_mt() {
?>
	<script type='text/javascript'>
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
(function() {
var gads = document.createElement('script');
gads.async = true;
gads.type = 'text/javascript';
var useSSL = 'https:' == document.location.protocol;
gads.src = (useSSL ? 'https:' : 'http:') + 
'//www.googletagservices.com/tag/js/gpt.js';
var node = document.getElementsByTagName('script')[0];
node.parentNode.insertBefore(gads, node);
})();
</script>

<script type='text/javascript'>
googletag.cmd.push(function() {
googletag.defineSlot('/92947493/300x250_PATINETA_ALL', [300,250], 'zona-92781973').addService(googletag.pubads());googletag.defineSlot('/92947493/300X250_CFD_sidebar_2', [300,250], 'zona-92242453').addService(googletag.pubads());googletag.defineSlot('/92947493/intersitial_WEEDAFTERS_ALL', [970,90], 'zona-92790253').addService(googletag.pubads());

googletag.enableServices();
});
</script>

	<style type="text/css">
		
	</style>
<?php
}

function wath_widget_media($text) {
	global $adunits;

	$units = json_decode($adunits);

	foreach ($units as $key => $unit) {
		$id_tag_template = str_replace("#", "", $unit->id_tag_template);
		preg_match('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', $text, $matches);
		if (count($matches) > 0) {
			$htmlAd = $unit->div_display;
			$result = preg_replace('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', '$1$3', $text);
			$result = preg_replace('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', '$1' . $htmlAd . '$3', $result);
		}
	}

	return (!empty($result)) ? $result : $text;
}
add_filter('widget_text', 'wath_widget_media');

function cleanTagTemplates($adunits) {
	$units = json_decode($adunits);
	$erroWrite = array();
	$pathTemplate = get_template_directory();
	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathTemplate), RecursiveIteratorIterator::SELF_FIRST);
	foreach($objects as $name => $object){
		if (preg_match('/^.+\.php$/i', $name)) {
			if (is_writable($name)) {
				$phpFileTemplate = file_get_contents($name);

				foreach ($units as $key => $unit) {
					$id_tag_template = str_replace("#", "", $unit->id_tag_template);
					preg_match('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', $phpFileTemplate, $matches);
					if (count($matches) > 0) {
						if (!empty($matches[2])) {
							$htmlAd = $unit->div_display; echo $htmlAd;
							$result = preg_replace('/(<div.*?id="' . $id_tag_template . '"[^>]*>)(.*?)(<\/div>)/i', '$1$4', $phpFileTemplate);
							file_put_contents($name, $result);
						}
					}
				}
			} else {
				$erroWrite[] = $name;
			}
		}
	}
}

//////////////////////////////
//Delete plugins
//////////////////////////////
 /**
 * Check for hook
 */
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'uninstall_mt');

/**
 * Delete options in database
 */
function uninstall_mt() {
	global $adunits;
	cleanTagTemplates($adunits);
}

//////////////////////////////
//Update plugins
//////////////////////////////
// $api_url = 'http://media-adserver.media.cl/repositories';
// $plugin_slug = 'mt-adnetworks.cl-2/adnetworks.cl.php';
// $plugin_slug = '547f0bfd-45cc-4c96-a33b-0d06de47d803';

// // TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
// set_site_transient('update_plugins', null);

// // TEMP: Show which variables are being requested when query plugin API
// add_filter('plugins_api_result', 'aaa_result', 10, 3);
// function aaa_result($res, $action, $args) {
// 	print_r($res);
// 	return $res;
// }

$api_url = 'http://media-adserver.media.cl/repositories/api';
// $plugin_slug = 'mt-terra.cl/terra.cl.php';
$plugin_key = '547f0bf8-d340-4497-8a23-0d06de47d803';
$plugin_slug = basename(dirname(__FILE__));


// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');

function check_for_plugin_update($checked_data) {
	global $api_url, $plugin_slug;
	
	if (empty($checked_data->checked))
		return $checked_data;
	
	$request_args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	
	$request_string = prepare_request('basic_check', $request_args);
	
	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);
	
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
	
	return $checked_data;
}


// Take over the Plugin info screen
add_filter('plugins_api', 'my_plugin_api_call', 10, 3);

function my_plugin_api_call($def, $action, $args) {
	global $plugin_slug, $api_url;
	
	if ($args->slug != $plugin_slug)
		return false;
	
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
	$args->version = $current_version;
	
	$request_string = prepare_request($action, $args);
	
	$request = wp_remote_post($api_url, $request_string);
	
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	
	return $res;
}


function prepare_request($action, $args) {
	global $wp_version;
	
	return array(
		'body' => array(
			'action' => $action, 
			'request' => serialize($args),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);	
}
