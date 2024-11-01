<?php

//
// Movie fields
//
//  These field definitions are used in two places:
//  
//  1. The Admin Movies page (inc/movies.php)
//  2. (By implication) the schema file, schema.php
//
//  NB: These must match the fields in the schema file by name and type
//      If you change name or type, you must change the schema as well
//

$WPCinema_movie_fields = array(

'title' => array(
    'prompt' => 'Movie Name',
    'desc' => 'Name of Movie for display throughout',
    'type' => 'text',
    'length' => 70,
    // validation: nonblank
),

'releaseyear' => array(
    'prompt' => 'Release Year',
    'desc' => 'Year movie released',
    'type' => 'text',
    'length' => 5,
    // validation: integer, optional
),

'shortid' => array(
    'prompt' => 'Movie Short ID',
    'desc' => 'Short ID name for movie - must be unique A-Z0-9 and dash only',
    'type' => 'text',
    'length' => 20,
    // validation: only a-z 0-9 - and _, convert to upper, mandatory
),

'description' => array(
    'prompt' => 'Movie Description',
    'desc' => 'Description of Movie',
    'type' => 'textarea',
    'length' => 10 // lines
    // allow as much HTML as possible here
    // would be nice for this to be tinymce etc
),

'rating' => array(
    'prompt' => 'Movie Rating',
    'desc' => '',
    'type' => 'text',
    'length' => 4
    // must be a valid aussie rating or blank --> CTC
    // perhaps this should be radio buttons or dropdown?
),

'displayorder' => array(
    'prompt' => 'Display order',
    'desc' => 'Display order for daily page; higher numbers displayed first - '
            . 'auto if blank',
    'type' => 'numeric',
    'length' => 5
    // validation: integer, optional
),
    
'cast' => array(
    'prompt' => 'Cast',
    'desc' => 'Key actors',
    'type' => 'text',
    'length' => 70
    // validation: text, optional (warn if integer entered)
),
    
'runtime' => array(
    'prompt' => 'Runtime (mins)',
    'desc' => '',
    'type' => 'numeric',
    'length' => 5
    // validation: integer, optional
),
    
// need to be able to upload images here
// perhaps this should be an ajax select field
/*
'image' => array(
    'prompt' => 'Image',
    'desc' => 'Image for movie thumbnail (not yet implemented)',
    'type' => 'text',
    'length' => 80,
),
 */

'previewdate' => array(
    'prompt' => 'Preview date',
    'desc' => 'Previews will appear in programme marked as preview (opt)',
    'type' => 'date',
    'length' => 10
    // validation: valid date, optional
),
    
'startdate' => array(
    'prompt' => 'Start date',
    'desc' => 'Date of first session',
    'type' => 'date',
    'length' => 10,
    // validation: valid date, optional
),
    
'hidestart' => array(
    'prompt' => 'Hide start date',
    'desc' => "Hide start date and don't display it until start",
    'type' => 'checkbox',
),
    
'enddate' => array(
    'prompt' => 'End date',
    'desc' => 'Date of last session (opt). '
            . 'Use same as Start for one-off event',
    'type' => 'date',
    'length' => 10
    // validation: valid date, optional
),
    
'nftdate' => array(
    'prompt' => 'No Free Tickets ends',
    'desc' => 'Last date for NFT marker to appear next to title (opt)',
    'type' => 'date',
    'length' => 10
    // validation: valid date, optional
),

'officialurl' => array(
    'prompt' => 'Official URL',
    'desc' => 'Official movie site URL',
    'type' => 'text',
    'length' => 100,
    // validation - URL (assume http and add), optional
),
    
'hideonmain' => array(
    'prompt' => 'Hide on Home',
    'desc' => 'Hide on daily page (overrides everything else)',
    'type' => 'checkbox',
),
    
'forcedisplay' => array(
    'prompt' => 'Force display',
    'desc' => 'Always display on daily page',
    'type' => 'checkbox',
),

'leavetitle' => array(
    'desc' => 'Force this title not to be capitalized or otherwise altered',
    'prompt' => 'Leave title alone',
    'type' => 'checkbox',
),


);


// end
