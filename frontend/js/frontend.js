( function( $ ) {
	var category, locations_data = {}, map_settings;

	/* Search Function */
	if ( $( '#b-location-list' ).hasClass( 'have-search' ) ) {
		$( '#b-search' ).keyup( function( event ) {
			var search = $( this ).val().toUpperCase();
			var searched = 0;
			if ( search != '' ) {
				$( '#b-location ul' ).removeClass( 'empty-search' ).addClass( 'searching' );
				$( '#b-location ul > li' ).each( function( index, el ) {
					if( $( 'a', el ).text().toUpperCase().indexOf( search ) > -1 ) {
						$( el ).show();
						searched = 1;
					} else {
						$( el ).hide();
					}
				} );

				if ( searched == 0 ) {
					$( '#b-location ul' ).append( '<li style="display: inline-block !important;">No Location Found</li>' )
				}
			} else { 
				$( '#b-location ul' ).removeClass( 'searching' ).addClass( 'empty-search' );
			}
		} );
	}

	ajax_get_location();
	if ( $( '#b-location-list' ).hasClass( 'have-categories' ) ) {
		$( '#b-categories' ).on( 'change', function() {
			category = $( this ).val();
			ajax_get_location();
		} );
	}

	function ajax_get_location()
	{
		$( '.b-preloader' ).fadeIn();
		$.ajax( {
		    url      : ajaxurl,
		    type     : 'post',
		    dataType : 'json',
		    data     : {
		        action   : 'b_location_action',
		        category : category
		    },
		    success  : function( response ) {
		    	$( '.b-preloader' ).fadeOut();

		    	map_settings = response.map_settings

		    	var locations_result = '';
		    	locations_data = {};
		    	if ( response.filtered_locations ) {
		    		/* Loop the result and get the data */
		    		$.each( response.filtered_locations, function( index, val ) {
		    			/* Construct HTML and place the location data that be shown in frontend */
		    			locations_result += '<li><a href="#'+ val.location_slug +'" id="'+ val.location_id +'">'+ val.location_name +'</a></li>';

		    			/* Store the location data in the array that can be used in initializing map */
		    			locations_data[ val.location_id ] = [
							val.location_long,
							val.location_lat,
							val.location_details,
						];
		    		} );
		    		
		    		/* Initialize Google Map */
					google.maps.event.addDomListener( window, 'load', initialize_map() );
					google.maps.event.addDomListener(window, 'resize', function() {
					    initialize_map();
					} );
		    	} else {
		    		locations_result += '<li>No Location found.</li>';
		    	}

		    	/* Place the constructed HTML */
		    	$( '#b-location ul' ).html( locations_result );
		    },
		    error    : function( response ) {
		    	$( '#b-location ul' ).html( '<li>Loading failed</li>' );

		    	console.log( response );
		    }
		} );
	}

	function initialize_map()
	{
		var settings = {
			zoom                 	: parseInt( zoom_level ),
			center               	: new google.maps.LatLng( center_long, center_lat, 16 ),
			mapTypeId            	: google.maps.MapTypeId.ROADMAP,
			draggable  				: ( google_map_draggable == 'on' ) ? false : true,
			disableDoubleClickZoom  : ( google_map_doubleclickzoom == 'on' ) ? false : true,
			zoomControl  			: ( google_map_zoomcontrol == 'on' ) ? false : true,
			scrollwheel 			: ( google_map_scrollwheel == 'on' ) ? false : true,
			streetViewControl		: ( google_map_streetview == 'on' ) ? false : true,
		};

		if ( google_map_theme != '' )
			settings['styles'] = eval( google_map_theme );
		
		// console.log( JSON.parse( google_map_theme ) );
		var map = new google.maps.Map( document.getElementById( 'b-map' ), settings );

		var infowindow = new google.maps.InfoWindow();

		var marker;
		
		/* Initialize the action when clicked the marker in google map */
		for ( var key in locations_data ) {
			/* Populate the map */
			var data = {
				position	: new google.maps.LatLng(locations_data[key][0], locations_data[key][1]),
				map     	: map,
			};
			if ( google_map_marker != '' )
				data['icon'] = google_map_marker;

		    marker = new google.maps.Marker( data );

		    /* Action in clicking the pin in the map */
			google.maps.event.addListener( marker, 'click', ( function( marker, key ) {
				return function() {
					/* Show details of the location */
					infowindow.setContent( locations_data[key][2] );
					infowindow.open( map, marker );
				}
			} ) ( marker, key ) );
		}
		
		/* Action in clicking the location */
		$( document ).on( 'click', '#b-location a', function( event ) {
			event.preventDefault();
			/* Populate the map */
			var id = $( this ).attr( 'id' );
			var data = {
				position	: new google.maps.LatLng(locations_data[id][0], locations_data[id][1]),
				map     	: map,
			};
			if ( google_map_marker != '' )
				data['icon'] = google_map_marker;
			marker = new google.maps.Marker( data );

			/* Show details of the location */
			infowindow.setContent( locations_data[id][2] );
			infowindow.open( map, marker );

			/* Initialize the action when clicked the marker in google map */
			for ( var key in locations_data ) {
				/* Populate the map */
			    var data = {
					position	: new google.maps.LatLng(locations_data[key][0], locations_data[key][1]),
					map     	: map,
				};
				if ( google_map_marker != '' )
					data['icon'] = google_map_marker;

			    marker = new google.maps.Marker( data );

			    /* Action in clicking the pin in the map */
				google.maps.event.addListener( marker, 'click', ( function( marker, key ) {
					return function() {
						/* Show details of the location */
						infowindow.setContent( locations_data[key][2] );
						infowindow.open( map, marker );
					}
				} ) ( marker, key ) );
			}
		} );
	}
} ( jQuery ) );