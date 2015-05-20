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
function head_setup_mt() {
	global $post;

	$ga_account = '';
	$facebook_page = '';
	$twitter_profile = '';
	$instagram_id = '';
	$likes_fb = 0;
	$twitter_count = 0;
	$instagram_count = 0;

	#GA Stats
	$sessions_count = 0;
	$users_count = 0;
	$avg_sessions_duration = 0;
	$pageviews_count = 0;
	$pageviews_per_session = 0;

	if ( function_exists('get_field') ) {
		$path_plugin = plugin_dir_path( __FILE__ );
		#Google Analytics
		$ga_account = get_field('ga_account', $post->ID);
		if ( $ga_account  ) {
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
			
			$results = $service->data_ga->get(
				$googleApi['gaAccount'], 
				'90daysAgo', 
				'yesterday', 
				'ga:users,ga:sessions,ga:avgSessionDuration,ga:pageviews,ga:pageviewsPerSession',
				array(
					// 'filter' => 'ga:medium==organic',
					// 'dimensions' => 'ga:deviceCategory'
				)
			);
			if ( $results->totalsForAllResults ) {
				$users_count = ceil( $results->totalsForAllResults['ga:users'] );
				$sessions_count = ceil( $results->totalsForAllResults['ga:sessions'] );
				$avg_sessions_duration = ceil( $results->totalsForAllResults['ga:avgSessionDuration'] );
				$pageviews_count = ceil( $results->totalsForAllResults['ga:pageviews'] );
				$pageviews_per_session = $results->totalsForAllResults['ga:pageviewsPerSession'];
			}
		}

		#Facebook likes
		$facebook_page = get_field('facebook_page', $post->ID);
		if ( $facebook_page  ) {
			$likes_fb = fbLikeCount( trim( $facebook_page ) );
		}

		#Twitter followers
		$twitter_profile = get_field('twitter_profile', $post->ID);
		if ( $twitter_profile  ) {
			$followers_count_response = file_get_contents( 'https://cdn.syndication.twimg.com/widgets/followbutton/info.json?screen_names=' . trim( $twitter_profile ) );
			if ( $followers_count_response ) {
				$followers_count_response = json_decode( $followers_count_response );
				$twitter_count = $followers_count_response[0]->followers_count;
			}
		}

		#Instagram followers
		$instagram_id = get_field('instagram_id', $post->ID);
		if ( $instagram_id  ) {
			$followers_count_response = file_get_contents( 'https://api.instagram.com/v1/users/' . trim( $instagram_id ) . '/?access_token=53042481.ab103e5.0c6f8f50471a4e1f97595f8db529a47a' );
			if ( $followers_count_response ) {
				$followers_count_response = json_decode( $followers_count_response );
				$instagram_count = $followers_count_response->data->counts->followed_by;
			}
		}
	} else {

	}

	$js_path = plugins_url( 'js/mediakit.js', __FILE__ );
?>

	<script type="text/javascript">
		if (!window.jQuery) {
			var jq = document.createElement('script'); jq.type = 'text/javascript';
			jq.src = '//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js';
			document.getElementsByTagName('head')[0].appendChild(jq);
		}
		var fb_likes = '<?php echo $likes_fb; ?>';
		var twitter_followers = '<?php echo $twitter_count; ?>';
		var instagram_followers = '<?php echo $instagram_count; ?>';
		//GA
		var sessions_count = '<?php echo $sessions_count; ?>'
		var users_count = '<?php echo $users_count; ?>'
		var avg_sessions_duration = '<?php echo $avg_sessions_duration; ?>'
		var pageviews_count = '<?php echo $pageviews_count; ?>'
		var pageviews_per_session = '<?php echo $pageviews_per_session; ?>'
	</script>
	<script type="text/javascript" src="<?php echo $js_path ?>"></script>
<?php
}

//Get Facebook Likes Count of a page
function fbLikeCount($id){
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