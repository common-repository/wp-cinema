<?php

//===========================================================================
//
//  WP Cinema
//
//  Main settings fields
//
//  To add a settings field, just add it here in the appropriate tab
//
//  Probably easiest just to duplicate an existing field.
//
//  Default value is null/false if not specified here.
//
//  These settings are all saved in the one large WP settings row.
//
//===========================================================================

$WPCinema_settings_dict = array(

//===========================================================================
// Tab one: Main
//
'main' => array(
    'tab' => 'main',
    'tabhead' => 'Main',
    'priority' => 1,
    'fields' => array(

	'dailyforcedays' => array(
	    'prompt' => 'Include in daily within days',
	    'desc' => 'Display in daily programme if starts within N days',
	    'type' => 'text',
	    'length' => 3,
	    'default' => 90,
	    // 'validation' => ''
	),
	'titlestocaps' => array(
	    'prompt' => 'Capitalize titles',
	    'desc' => 'Capitalize all movie titles by default',
	    'type' => 'checkbox',
	    'default' => 0
	),
	'hidecomingsoon' => array(
	    'prompt' => 'Hide Coming Soon',
	    'desc' => 'In Daily view, hide movies coming soon '
	    	    . 'with no sessions today',
	    'type' => 'checkbox',
	    'default' => 0,
	),
	'minsessions' => array(
	    'prompt' => 'Minimum sessions before warning',
	    'desc' => 'In Daily view, warn if there are less sessions '
	    	    . 'than this',
	    'type' => 'integer',
	    'length' => 3,
	    'default' => 5,
	),
	'toolate' => array(
	    'prompt' => 'Mins before start to close',
	    'desc' => 'Close sessions for booking this many minutes before they start',
	    'type' => 'integer',
	    'default' => 5,
	),
	'defaultrating' => array(
	    'prompt' => 'Default rating',
	    'desc' => 'Rating to display if none assigned',
	    'type' => 'text',
	    'length' => 10,
	    'default' => 'CTC',
	),
	'nftdefault' => array(
	    'prompt' => 'Default NFT text',
	    'desc' => '"NFT" tag to display after movie titles if before '
		    . 'nft period ends.  Usually this is "NFT" for No Free '
		    . 'Tickets',
	    'type' => 'text',
	    'length' => 10,
	    'default' => 'NFT',
	),
	'cssfile' => array(
	    'prompt' => 'CSS file',
	    'desc' => 'On public site, '
	    	   .  'include this CSS file; '
	    	   .  'path is relative to WordPress base',
	    'type' => 'text',
	    'length' => 80,
	    'default' => '',
	),
	'cssinhead' => array(
	    'prompt' => 'CSS in head always',
	    'desc' => 'On public website, always include our CSS in head. '
		    . 'Used to avoid problems with inline CSS includes',
	    'type' => 'checkbox',
	    'default' => 0,
	),
	'avoidwpccss' => array(
	    'prompt' => 'Avoid plugin CSS',
	    'desc' => 'On public website, never include plugin CSS. '
		    . 'Used to replace plugin CSS with your own CSS code',
	    'type' => 'checkbox',
	    'default' => 0,
	),
	'no-manual-movie-create' => array(
	    'prompt' => 'Prevent manual movie creation',
	    'desc' => 'Stop manual movie creation '
	    	    . '(to stop conflict with automated ticket server import)',
	    'type' => 'checkbox',
	    'default' => 1,
	),
	'no-manual-session-create' => array(
	    'prompt' => 'Prevent manual session creation',
	    'desc' => 'Stop manual session creation '
	    	    . '(to stop conflict with automated ticket server import)',
	    'type' => 'checkbox',
	    'default' => 1,
	),

    ),
), // end tab


//===========================================================================
// Tab two: Secondary tab
//
/*
 *
'secondary' => array(
    'tab' => 'secondary',
    'tabhead' => 'Secondary',
    'priority' => 4,
    'fields' => array(

// should this be an assoc array or ordered?
	'field2' => array(
	    'prompt' => 'Test field 2',
	    'type' => 'checkbox',
	),
	'field3' => array(
	    'prompt' => 'Test field 3',
	    'type' => 'checkbox',
	),
	'someboolean' => array(
	    'prompt' => 'Shared field',
	    'type' => 'checkbox',
	),


    ),
), // end tab
*/

);  // end options array


// end
