jQuery(document).ready(function($) {
	$("#fb_likes").empty().html( numberWithCommas( fb_likes ) );
	$("#twitter_followers").empty().html( numberWithCommas( twitter_followers ) );
	$("#instagram_followers").empty().html( numberWithCommas( instagram_followers ) );

	//GA
	$("#sessions_count").empty().html( numberWithCommas( sessions_count ) );
	$("#users_count").empty().html( numberWithCommas( users_count ) );
	$("#avg_sessions_duration").empty().html( secondsToMinSeconds( avg_sessions_duration ) );
	$("#pageviews_count").empty().html( numberWithCommas( pageviews_count ) );
	$("#pageviews_per_session").empty().html( pageviews_per_session );
});

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function secondsToMinSeconds ( time ) {
	var minutes = Math.floor(time / 60);
	var seconds = time - minutes * 60;

	return minutes + ' :' + seconds;
}