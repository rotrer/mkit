<?php
/**
 * Plugin Name: Google Analytics Mediakit
 * Plugin URI: http://mediatrends.cl
 * Description: Métricas Google Analytics por sitio
 * Version: 1
 * Author: Mediatrends
 * Author URI: http://mediatrends.cl
 */


// register_activation_hook( __FILE__, 'activar_plugin' );
// function activar_plugin() {
// }

// add_action( 'deactivated_plugin', 'desactivar_plugin', 10, 2 );
// function desactivar_plugin( $plugin, $network_activation ) {
// }
set_include_path(__DIR__ . "/api-library/vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/Analytics.php';

add_action('wp_head', 'head_setup_mt');
function head_setup_mt() 
{
	if ( is_single() ) 
	{
	
		global $post;
		wp_enqueue_script( 'ajax-script', plugins_url( '/js/mediakit.js', __FILE__ ), array('jquery') );

		$js_datepicker = plugins_url( 'datepicker/js/datepicker.js', __FILE__ );
		$css_base_datepicker = plugins_url( 'datepicker/css/base.css', __FILE__ );
		$css_clean_datepicker = plugins_url( 'datepicker/css/clean.css', __FILE__ );
		$css_custom_datepicker = plugins_url( 'datepicker/css/custom.css', __FILE__ );
?>

		<script type="text/javascript">
			if (!window.jQuery) {
				var jq = document.createElement('script'); jq.type = 'text/javascript';
				jq.src = '//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js';
				document.getElementsByTagName('head')[0].appendChild(jq);
			}
			var is_single = <?php echo is_single(); ?>;
			var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
			var post_id = <?php echo $post->ID; ?>
		</script>
		<script type="text/javascript" src="<?php echo $js_datepicker; ?>"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $css_base_datepicker; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $css_clean_datepicker; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $css_custom_datepicker; ?>" />

<?php
	}
}

#Google Analyctics calls
add_action( 'wp_ajax_ga_calls', 'ga_calls_callback' );
add_action( 'wp_ajax_nopriv_ga_calls', 'ga_calls_callback' );
function ga_calls_callback() 
{
	global $post;
	
	#Sets
	$path_plugin = plugin_dir_path( __FILE__ );
	$ga_account = '';
	$has_error = true;
	#GA Stats
	$sessions_count = 0;
	$users_count = 0;
	$avg_sessions_duration = 0;
	$pageviews_count = 0;
	$pageviews_per_session = 0;
	#GA Stats Mobile
	$sessions_count_mobile = 0;
	$users_count_mobile = 0;
	$avg_sessions_duration_mobile = 0;
	$pageviews_count_mobile = 0;
	$pageviews_per_session_mobile = 0;
	

	if ( function_exists('get_field') ) 
	{
		
		$post_id = $_POST['post_id'];
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$ga_account = get_field('ga_account', $post_id);

		if ( $ga_account  ) 
		{
			// Declaramos nuestra configuración 
			$googleApi = array(
				'id' => '999359394470-cg7pe74pt9uqauoupd6cjjfdf5q5nsv3.apps.googleusercontent.com', // Id que nos ha dado la APIs Console
				'email' => '999359394470-cg7pe74pt9uqauoupd6cjjfdf5q5nsv3@developer.gserviceaccount.com', // email que nos ha dado la APIs Console
				'keyFile' => $path_plugin . 'api-library/Mediakit_Google_Lib-d29b45137945.p12', // nombre del fichero llave
				'gaAccount' => 'ga:' . $ga_account // id de la cuenta de analytics a la que nos conectamos
			);
			 
			// Creamos el cliente de conexión
			$client = new Google_Client();
			$client->setApplicationName('blog.ikhuerta.com-analytics-sample');
			$client->setAssertionCredentials(
				new Google_Auth_AssertionCredentials(
					$googleApi['email'],
					array('https://www.googleapis.com/auth/analytics.readonly'),
					file_get_contents($googleApi['keyFile'])
				)
			);
			$client->setClientId($googleApi['id']);
			$client->setAccessType('offline_access');

			$service = new Google_Service_Analytics($client);
			
			// Set dates params
			// Start date
			if ( trim( $start_date ) == '' ) 
			{
				$start_date = '90daysAgo';
			}
			// End date
			if ( trim( $end_date ) == '' ) 
			{
				$end_date = 'yesterday';
			}

			$resultsDesktop = $service->data_ga->get(
				$googleApi['gaAccount'],
				$start_date,
				$end_date,
				'ga:users,ga:sessions,ga:avgSessionDuration,ga:pageviews,ga:pageviewsPerSession',
				array(
					// 'filter' => 'ga:medium==organic',
					'dimensions' => 'ga:deviceCategory'
				)
			);

			if ( $resultsDesktop->totalsForAllResults ) 
			{
				//Totales
				$users_count = ceil( $resultsDesktop->totalsForAllResults['ga:users'] );
				$sessions_count = ceil( $resultsDesktop->totalsForAllResults['ga:sessions'] );
				$avg_sessions_duration = ceil( $resultsDesktop->totalsForAllResults['ga:avgSessionDuration'] );
				$pageviews_count = ceil( $resultsDesktop->totalsForAllResults['ga:pageviews'] );
				$pageviews_per_session = number_format($resultsDesktop->totalsForAllResults['ga:pageviewsPerSession'] , 2, '.', '');

				//Mobile
				if ( count( $resultsDesktop->rows > 0 ) ) 
				{
					foreach ( $resultsDesktop->rows as $key => $row ) {
						if ( $row[0] == 'mobile' || $row[0] == 'tablet' ) {
							$users_count_mobile += $row[1];
							$sessions_count_mobile += $row[2];
							$avg_sessions_duration_mobile += $row[3];
							$pageviews_count_mobile += $row[4];
							$pageviews_per_session_mobile += $row[5];
						}
					}
				}
				$avg_sessions_duration_mobile = ceil ( $avg_sessions_duration_mobile / 2 );

				$pageviews_per_session_mobile = $pageviews_per_session_mobile / 2;
				$pageviews_per_session_mobile = number_format($pageviews_per_session_mobile , 2, '.', '');

				$response =  array(
							"desktop" => array(
									"users_count" => $users_count,
									"sessions_count" => $sessions_count,
									"avg_sessions_duration" => $avg_sessions_duration,
									"pageviews_count" => $pageviews_count,
									"pageviews_per_session" => $pageviews_per_session,
								),
							"mobile" => array(
									"users_count" => $users_count_mobile,
									"sessions_count" => $sessions_count_mobile,
									"avg_sessions_duration" => $avg_sessions_duration_mobile,
									"pageviews_count" => $pageviews_count_mobile,
									"pageviews_per_session" => $pageviews_per_session_mobile,
								)
						);

				$has_error = false;
			}
		}
	}

	echo json_encode( array( "error" => $has_error, "data" => $response ) );
	wp_die();
}

#Facebook calls
add_action( 'wp_ajax_fb_calls', 'fb_calls_callback' );
add_action( 'wp_ajax_nopriv_fb_calls', 'fb_calls_callback' );
function fb_calls_callback() 
{
	global $post;

	$post_id = $_POST['post_id'];
	$facebook_page = '';
	$likes_fb = 0;
	$has_error = true;

	if ( function_exists('get_field') ) 
	{
		#Facebook likes
		$facebook_page = get_field('facebook_page', $post_id);
		if ( $facebook_page  ) 
		{
			$likes_fb = fbLikeCount( trim( $facebook_page ) );
			$response =  array(
							"likes_fb" => $likes_fb
						);

			$has_error = false;
		}
	}

	echo json_encode( array( "error" => $has_error, "data" => $response ) );
	wp_die();
}

#Twitter calls
add_action( 'wp_ajax_tw_calls', 'tw_calls_callback' );
add_action( 'wp_ajax_nopriv_tw_calls', 'tw_calls_callback' );
function tw_calls_callback() 
{
	global $post;

	$post_id = $_POST['post_id'];
	$twitter_profile = '';
	$twitter_count = 0;
	$has_error = true;

	if ( function_exists('get_field') ) 
	{

		#Twitter followers
		$twitter_profile = get_field('twitter_profile', $post_id);
		if ( $twitter_profile  ) 
		{
			$followers_count_response = file_get_contents( 'https://cdn.syndication.twimg.com/widgets/followbutton/info.json?screen_names=' . trim( $twitter_profile ) );
			if ( $followers_count_response ) 
			{
				$followers_count_response = json_decode( $followers_count_response );
				$twitter_count = $followers_count_response[0]->followers_count;

				$response =  array(
								"twitter_count" => $twitter_count
							);

				$has_error = false;
			}
		}

	}

	echo json_encode( array( "error" => $has_error, "data" => $response ) );
	wp_die();
}

#Instagram calls
add_action( 'wp_ajax_inst_calls', 'inst_calls_callback' );
add_action( 'wp_ajax_nopriv_inst_calls', 'inst_calls_callback' );
function inst_calls_callback() 
{
	global $post;

	$post_id = $_POST['post_id'];
	$instagram_id = '';
	$instagram_count = 0;
	$has_error = true;

	if ( function_exists('get_field') ) 
	{

		#Instagram followers
		$instagram_id = get_field('instagram_id', $post_id);
		if ( $instagram_id  ) 
		{
			$followers_count_response = file_get_contents( 'https://api.instagram.com/v1/users/' . trim( $instagram_id ) . '/?access_token=53042481.ab103e5.0c6f8f50471a4e1f97595f8db529a47a' );
			if ( $followers_count_response ) 
			{
				$followers_count_response = json_decode( $followers_count_response );
				$instagram_count = $followers_count_response->data->counts->followed_by;

				$response =  array(
								"instagram_count" => $instagram_count
							);

				$has_error = false;
			}
		}
	}

	echo json_encode( array( "error" => $has_error, "data" => $response ) );
	wp_die();
}


//Get Facebook Likes Count of a page
function fbLikeCount($id)
{
	//Construct a Facebook URL
	$json_url ='https://graph.facebook.com/'.$id.'';
	$json = file_get_contents($json_url);
	$json_output = json_decode($json);
 
	//Extract the likes count from the JSON object
	if($json_output->likes){
		return $likes = $json_output->likes;
	}else{
		return 0;
	}
}