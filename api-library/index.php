<?php 
set_include_path("vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/Analytics.php';

// Declaramos nuestra configuración 
$googleApi = array(
	'id' => '999359394470-cg7pe74pt9uqauoupd6cjjfdf5q5nsv3.apps.googleusercontent.com', // Id que nos ha dado la APIs Console
	'email' => '999359394470-cg7pe74pt9uqauoupd6cjjfdf5q5nsv3@developer.gserviceaccount.com', // email que nos ha dado la APIs Console
	'keyFile' => 'Mediakit_Google_Lib-d29b45137945.p12', // nombre del fichero llave
	'gaAccount' => 'ga:86288954' // id de la cuenta de analytics a la que nos conectamos
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
	'2015-04-18', 
	'2015-05-18', 
	'ga:users,ga:sessions,ga:avgSessionDuration,ga:pageviews,ga:pageviewsPerSession',
	array(
		// 'filter' => 'ga:medium==organic',
		// 'dimensions' => 'ga:deviceCategory'
	)
);

var_dump($results);
