jQuery(document).ready(function($) {
	if ( is_single !== undefined && is_single === 1 ) 
	{

		//Facebook Calls
		var data = {
			'action': 'fb_calls',
			'post_id': post_id
		};
		$.ajax({
			method: "POST",
			url: ajaxurl,
			data: data,
			dataType: 'json'
		})
			.done(function( response ) {
				if ( response.error !== undefined && response.error == false ) 
				{
					$("#fb_likes").empty().html( numberWithCommas( response.data.likes_fb ) );
				};
			});

		//Twitter Calls
		var data = {
			'action': 'tw_calls',
			'post_id': post_id
		};
		$.ajax({
			method: "POST",
			url: ajaxurl,
			data: data,
			dataType: 'json'
		})
			.done(function( response ) {
				if ( response.error !== undefined && response.error == false ) 
				{
					$("#twitter_followers").empty().html( numberWithCommas( response.data.twitter_count ) );
				};
			});

		//Instagram Calls
		var data = {
			'action': 'inst_calls',
			'post_id': post_id
		};
		$.ajax({
			method: "POST",
			url: ajaxurl,
			data: data,
			dataType: 'json'
		})
			.done(function( response ) {
				if ( response.error !== undefined && response.error == false ) 
				{
					$("#instagram_followers").empty().html( numberWithCommas( response.data.instagram_count ) );
				};
			});

		//GA Calls
		ga_calls('', '');

		/* Special date widget */
	 
		var to = new Date();
		var from = new Date();
				from.setDate( from.getDate() - 90);
		
		$('#datepicker-calendar').DatePicker({
			inline: true,
			date: [from, to],
			calendars: 3,
			mode: 'range',
			starts: 1,
			locale: {
				daysMin: ["D", "L", "M", "M", "J", "V", "S"],
				months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "June¡io", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Deciembre"],
				monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
			},
			current: new Date(to.getFullYear(), to.getMonth() - 1, 1),
			onChange: function(dates,el) {
				// update the range display
				$('#date-range-field span').text(dates[0].getDate()+' '+dates[0].getMonthName(true)+', '+dates[0].getFullYear()+' - '+
																		dates[1].getDate()+' '+dates[1].getMonthName(true)+', '+dates[1].getFullYear());
			}
		});
		
		// initialize the special date dropdown field
		$('#date-range-field span').text(from.getDate()+' '+from.getMonthName(true)+', '+from.getFullYear()+' - '+
																		to.getDate()+' '+to.getMonthName(true)+', '+to.getFullYear());
		
		// bind a click handler to the date display field, which when clicked
		// toggles the date picker calendar, flips the up/down indicator arrow,
		// and keeps the borders looking pretty
		$('#date-range-field').bind('click', function(){
			$('#datepicker-calendar').toggle();
			if($('#date-range-field a').text().charCodeAt(0) == 9660) {
				// switch to up-arrow
				$('#date-range-field a').html('&#9650;');
				$('#date-range-field').css({borderBottomLeftRadius:0, borderBottomRightRadius:0});
				$('#date-range-field a').css({borderBottomRightRadius:0});
			} else {
				// switch to down-arrow
				$('#date-range-field a').html('&#9660;');
				$('#date-range-field').css({borderBottomLeftRadius:5, borderBottomRightRadius:5});
				$('#date-range-field a').css({borderBottomRightRadius:5});
			}
			return false;
		});
		
		// global click handler to hide the widget calendar when it's open, and
		// some other part of the document is clicked.  Note that this works best
		// defined out here rather than built in to the datepicker core because this
		// particular example is actually an 'inline' datepicker which is displayed
		// by an external event, unlike a non-inline datepicker which is automatically
		// displayed/hidden by clicks within/without the datepicker element and datepicker respectively
		$('html').click(function() {
			if($('#datepicker-calendar').is(":visible")) {
				$('#datepicker-calendar').hide();
				$('#date-range-field a').html('&#9660;');
				$('#date-range-field').css({borderBottomLeftRadius:5, borderBottomRightRadius:5});
				$('#date-range-field a').css({borderBottomRightRadius:5});
			}
		});

		$('#cancel_ga').click(function(event){
			if($('#datepicker-calendar').is(":visible")) {
				$('#datepicker-calendar').hide();
				$('#date-range-field a').html('&#9660;');
				$('#date-range-field').css({borderBottomLeftRadius:5, borderBottomRightRadius:5});
				$('#date-range-field a').css({borderBottomRightRadius:5});
			}

			event.preventDefault();
		});

		$('#apply_ga').click(function(event){
			var dates_filter = $('#datepicker-calendar').DatePickerGetDate();
			var init_date = new Date( dates_filter[0][0] );
			var end_date = new Date( dates_filter[0][1] );

			// Desktop
			$("#users_count").empty().html('...');
			$("#sessions_count").empty().html('...');
			$("#avg_sessions_duration").empty().html('...');
			$("#pageviews_count").empty().html('...');
			$("#pageviews_per_session").empty().html('...');

			// Mobile
			$("#users_count_mobile").empty().html('...');
			$("#sessions_count_mobile").empty().html('...');
			$("#avg_sessions_duration_mobile").empty().html('...');
			$("#pageviews_count_mobile").empty().html('...');
			$("#pageviews_per_session_mobile").empty().html('...');

			ga_calls(init_date.yyyymmdd(), end_date.yyyymmdd());

			event.preventDefault();
		});
		
		// stop the click propagation when clicking on the calendar element
		// so that we don't close it
		$('#datepicker-calendar').click(function(event){
			event.stopPropagation();
		});
	};
});

function ga_calls(start_date, end_date) {
	var $ = jQuery;
	var data = {
		'action': 'ga_calls',
		'post_id': post_id,
		'start_date': start_date,
		'end_date': end_date
	};
	$.ajax({
		method: "POST",
		url: ajaxurl,
		data: data,
		dataType: 'json'
	})
		.done(function( response ) {
			if ( response.error !== undefined && response.error == false ) 
			{
				// Desktop
				$("#users_count").empty().html( numberWithCommas( response.data.desktop.users_count ) );
				$("#sessions_count").empty().html( numberWithCommas( response.data.desktop.sessions_count ) );
				$("#avg_sessions_duration").empty().html( secondsToMinSeconds( response.data.desktop.avg_sessions_duration ) );
				$("#pageviews_count").empty().html( numberWithCommas( response.data.desktop.pageviews_count ) );
				$("#pageviews_per_session").empty().html( response.data.desktop.pageviews_per_session );

				// Mobile
				$("#users_count_mobile").empty().html( numberWithCommas( response.data.mobile.users_count ) );
				$("#sessions_count_mobile").empty().html( numberWithCommas( response.data.mobile.sessions_count ) );
				$("#avg_sessions_duration_mobile").empty().html( secondsToMinSeconds( response.data.mobile.avg_sessions_duration ) );
				$("#pageviews_count_mobile").empty().html( numberWithCommas( response.data.mobile.pageviews_count ) );
				$("#pageviews_per_session_mobile").empty().html( response.data.mobile.pageviews_per_session );
			};
		});
}
function numberWithCommas(x)
{
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function secondsToMinSeconds ( time )
{
	var minutes = Math.floor(time / 60);
	var seconds = time - minutes * 60;

	return minutes + ' :' + seconds;
}

Date.prototype.yyyymmdd = function() {
	var yyyy = this.getFullYear().toString();
	var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
	var dd  = this.getDate().toString();

	return yyyy + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + (dd[1]?dd:"0"+dd[0]); // padding
};