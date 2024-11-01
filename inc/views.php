<?php

define('WPCIN_IMAGES', '/wp-cinema/images');

// Frontend - shortcode implementation for movie programme and movie display
//
//
class WP_Cinema_frontend_views
{
    public $options = NULL;
    public $daykey = 'wp-cinema-day';   // day query for daybar use
    public $dailyday = 0;
    public $css = null;
    public $user_css = null;
    public $css_done = 0;

    function __construct()
    {
	$this->options = get_option(WPCIN_OPTIONS);
	$opt = $this->options;
	$this->add_shortcodes();
	$this->css = WPCIN_URL.'/css/wpcinema.css';

	if (! empty($opt['cssfile']))
	    $this->user_css = ABSURL . '/' . $opt['cssfile'];

	date_default_timezone_set("Australia/Melbourne");
	if ($opt['cssinhead'])
	{
	    if (! $opt['avoidwpccss'])
	    {
		wp_register_style('wpcinema', $this->css, false, 0.5, 'screen');
		wp_enqueue_style('wpcinema');
	    }
	    if (! empty($this->user_css))
	    {
		wp_register_style('wpcinema_user', $this->user_css);
		wp_enqueue_style('wpcinema_user');
	    }
	    $this->css_done++;
	}
    }


    // setup shortcode calls
    function add_shortcodes()
    {
	add_shortcode('wpcinema_daily', array(&$this, 'daily'));
	add_shortcode('wpcinema_daily_body', array(&$this, 'daily_body'));
	add_shortcode('wpcinema_daybar', array(&$this, 'daybar'));
    }


    // Shortcode attributes processing -
    //   allow shortcode attributes to temporarily override options;
    //   called at top of all shortcode functions
    //
    // Example of arrays passed to shortcode functions:
    //  [test test_lone fred=string barney="longer string"]text[/test]
    //  $attr = array
    //  (
    //    [0] => test_lone
    //    [fred] => string
    //    [barney] => longer string
    //  )
    // text is avail as function second arg if needed
    // usage: $opt = $this->shortcode_options($attr);
    function shortcode_options($attr)
    {
	// we should probably also set defaults for shortcode args here
	// we don't use them currently other than to affect options
	$opt = $this->options;
	if (empty($attr))
	    return $opt;
	foreach ($attr as $key => $val)
	{
	    $v = trim($val);
	    if (empty($v))
		continue;
	    if ('true' == $v)
		$v = 1;
	    elseif ('false' == $v)
		$v = 0;
	    // lone keyword is in array as numeric index, ie [0] => 'keyword'
	    // so fix by translating to true boolean
	    if (ctype_digit($key))
		$opt[$v] = 1;
	    else
		$opt[$key] = $v;
	}
	return $opt;
    }


    // SHORTCODE: wpcinema_daily: display both day selection bar and movies
    // daily program.
    // Options used:
    //   none (specifically, can override gen options)
    function daily($attr)
    {
	$a = $this->daybar($attr);
	$a .= $this->daily_body($attr);
	return $a;
    }


    // day number to actual date (for use in SQL query)
    function numtodate($day)
    {
	// sanitise $day too
	if ($day > 6 || $day < 0 || ! ctype_digit($day))
		$day = 0;
	
	return date("Ymd", strtotime("+$day days"));
    }


    // Returns an array of movie objects ready for display
    // only movies with sessions today are returned (except for below)
    // options used:
    //   dailyforcedays: display movies starting in N days
    //
    function getMoviesForDay($opt, $datewanted = NULL)
    {
	global $wpdb;

	$datewanted = 0;
	if (empty($datewanted))
	{
	    $day = empty($_REQUEST[$this->daykey]) ? 0 : $_REQUEST[$this->daykey];
	    $datewanted = $this->numtodate($day);
	}

	// option: dailyforcedays - force including movies starting in N days
	// this makes the daily view show all upcoming movies nicely,
	// even if they are a long way in the future
	// only affects "daily" view
	$show_coming_soon_sql = '';
	if (! empty($opt['dailyforcedays'])) // 0 or null = off
	{
	    // old code frag: AND startdate - INTERVAL 3 MONTH < NOW()
	    $f = (int)$opt['dailyforcedays'];
	    if ($f > 0)
	      $show_coming_soon_sql = "OR startdate - INTERVAL $f DAY < NOW()";
	}

	// Select:
	//   Movies with sessions where
	//   - not hidden on main
	//   - display forced
	//   - or sessions running today
	//   - or (possibly) startdate is regarded as "soon"
	//   - or movie past startdate
	//   owners want to show movies running, or coming up "soon" (see opts)
	//   MySQL TO_DAYS() converts date to num days since epoch
	//
	$sesstab = $wpdb->prefix."wpcinema_sessions";
	$movtab = $wpdb->prefix."wpcinema_movies";
	// $categtab = $wpdb->prefix."wpcinema_categories";
	$q = "
	    SELECT $movtab.* FROM $movtab
	    LEFT JOIN $sesstab ON movie_id = $movtab.id
	    WHERE hideonmain = 0
	    AND
	    (
		forcedisplay = 1
		OR TO_DAYS(session_datetime) = TO_DAYS($datewanted)
		$show_coming_soon_sql
		OR (startdate IS NOT NULL AND startdate > NOW())
	    )
	    GROUP BY id
	    ORDER BY displayorder ASC, title ASC";
	    // GROUP BY id -> removes duplicated
	
	$ups = wp_upload_dir();
	$baseurl = $ups['baseurl'] . WPCIN_IMAGES;
	$blankimage = WPCIN_URL . "/images/100_140_default.jpg";

	$movies = $wpdb->get_results($q, OBJECT);
	
	// Add default image, session info and first_session to each movie
	foreach ($movies as $movie)
	{
	    if (! empty($movie->image))
		$movie->image = "$baseurl/$movie->image";
		//*** YCOHelper::doThumbs($movie->image); // ZZ resize TODO
	    else
		$movie->image = $blankimage;
	
	    $q2 = "SELECT * FROM $sesstab "
		  . "WHERE movie_id = '$movie->id' "
		  . "AND TO_DAYS(session_datetime) = TO_DAYS($datewanted) "
		  . 'ORDER BY session_datetime ASC';
	    
	    $movie->sessions = $wpdb->get_results($q2, OBJECT);
	    print_r($wpdb->last_error);
	    
	    // retrieve first session ever
	    // this is done for two reasons:
	    //  - work out whether there are any scheduled sessions at all
	    //  - work out real start date
	    $q3 = "SELECT * FROM $sesstab "
		  . "WHERE movie_id = '$movie->id' "
		  . 'ORDER BY session_datetime ASC '
		  . 'LIMIT 1';
	    
	    // set pseudo-column first_session
	    $movie->first_session = $wpdb->get_row($q3, OBJECT);

	    // set pseudo-column realstart to real startdate, if we have one
	    // either the startdate or date of first session
	    // don't know if code can use this concept
	    // idea is to compute here to make code simpler later
	    $movie->realstart = 0;
	    if (empty($movie->startdate) && !empty($movie->first_session))
		$movie->realstart = $movie->first_session->session_datetime;
	    elseif (isset($movie->startdate))
		$movie->realstart = $movie->startdate;
	}

	return $movies;
    }



//============================================================================
//
//  Layout
//
//============================================================================


    function include_css($opt = null)
    {
	// to allow per-page future overrides
	if (empty($opt))
	    $opt = $this->options;

	if ($this->css_done)
	    return;

	if ($opt['cssinhead'])
	    return;

	// Include style here and now as we've lost the chance to do 
	// wp_enqueue_style as the header has been sent
	$typec = "type='text/css'";
	if (! $opt['avoidwpccss'])
	    echo "<link rel='stylesheet' $typec href='$this->css' />\n";
	if (! empty($this->user_css))
	    echo "<link rel='stylesheet' $typec href='$this->user_css' />\n";
	$this->css_done++;
    }


// SHORTCODE: wpcinema_daybar: display Heading and Day selection bar
// options used:
//   none
//
function daybar($attr)
{

    $opt = $this->shortcode_options($attr);
    $this->include_css($opt);

    $day = empty($_REQUEST[$this->daykey]) ? 0 : $_REQUEST[$this->daykey];
    $today = date("l jS F", strtotime("+$day days"));
    $out = '';
    $out .= "<h2 class='wpcinema'>Movies for $today</h2>\n";
    $out .= "<div class='days'>";

    // Display the day bar, eg if today is Monday:
    //     Mon | Tue | Wed | Thu | Fri | Sat | Sun
    for ($i = 0; $i <= 6; $i++)
    {
	$sep = '';
	$timestamp = strtotime("+$i days");
        $url = add_query_arg(array($this->daykey => $i));
	$url = preg_replace(':/\?:', '?', $url);
	$title = "Click to see sessions on " . date("l jS F Y", $timestamp);
	$weekday = date('D', $timestamp);
	$out .= "$sep<a href='$url' title='$title'>$weekday</a>\n";
	$sep = ' | ';
    }
    $out .= "</div>\n";
    $out .= "<br class='clear' />\n";
    return $out;
}


// SHORTCODE: wpcinema_daily: daily programme 2-column display
// Displays the main session summary (usually on home page)
// Sample: (graphic) www.wpcinema.com/daily_image.jpg
//
// options used:
//   minsessions: less than this sessions on a day gives warning
//   hidecomingsoon: hide movies with no sessions that haven't started
//   toolate: mins before a session start to close session booking
//   titlestocaps: capitalize movie titles (unless movie->leavetitle true)
//   nftdefault: "NFT" string
//
function daily_body($attr)
{

    $opt = $this->shortcode_options($attr);
    $this->include_css($opt);
    $out = '';

    $day = 0;
    $datewanted = 0;
    if (! empty($_REQUEST[$this->daykey]))
	$day = $_REQUEST[$this->daykey];
    $today = date("l jS F", strtotime("+$day days"));
    $datewanted = strtotime("+$day days");

    // Get movie list  - will use $_REQUEST['day'] by default
    $movies = $this->getMoviesForDay($opt, $datewanted);

    //
    //  Display customer warning if it looks like we don't have session info
    //   option: min_sessions: min num of sessions before warning
    //   0=disabled
    //
    if (! empty($opt['minsessions']))
    {
	$totalSessions = 0;
	$min_sessions = $opt['minsessions'];
	foreach ($movies as $movie)
	{
	    $totalSessions += count($movie->sessions);
	}
	if ($totalSessions < $min_sessions)
	{
	    $out .= "<h3><font color=red>NOTE: Complete programme information "
		. "is not yet published for $today.</font></h3>\n";
	}
    }

    // No Free Tickets tag for title
    $nft = empty($opt['nftdefault']) ? 'NFT' : $opt['nftdefault'];
    $nft = trim($nft);
    if (empty($nft) || ' ' == $nft)
	$nft = 'NFT';

    //
    //   Now actually display the movie thumbnails and basics
    //
    foreach ($movies as $movie)
    {
	$session_count = count($movie->sessions);
	
	// option: hide movies with no sessions today that haven't started yet
	// this (mostly??) countermands forcedisplay and dailyforcedays
	if ($opt['hidecomingsoon'])
	{
	    if ($session_count == 0 && ! $this->movie_has_started($movie))
		continue;
	}

	// SEO-friendly movie info link - embed sanitized movie title in url
	// ie: mycinema.com/wp-cinema/movie/SHORTID/TITLE
	$safetitle = trim($movie->title);
	$safetitle = preg_replace("/'s/", 's', $safetitle); 
	$safetitle = preg_replace('/[\s_]+/', '-', $safetitle); 
	$safetitle = preg_replace('/[^-_A-Za-z0-9]/', '', $safetitle); 
	$movieinfolink = ''.
	$movieinfolink = "/wp-cinema/movie/$movie->shortid/$safetitle";

	$bookinglink = $this->moviebookingurl('MOVIE', $movie);
	$movietip = "Click for movie details";

	$out .= "<div class='wpcinema_movie'>\n";

	/***********
	if (empty($movie->image))
	{
	    $imagepath = "$base/images/100_140_default.jpg";
	}
	else
	{
	    $imagepath = "$base/wpcinema/images/thumbs/100_140_$movie->image";
	}
	***********/
	    $imagepath = $movie->image;
	    $image = "<img src='$imagepath' title='$movie->title' "
		. "width=100 height=140>\n";
	    $imagelink = "<a href='$movieinfolink' tip='$movietip'>$image</a>";

	    $out .= "<div class='moviethumb'>\n";
	    $out .= "$imagelink\n";
	    $out .= "</div>\n";

	    //  Top heading for the movie box, ie:
	    //    Now Showing
	    //    Coming Soon
	    //    From 30 Mar
	    //    Preview 30 Mar
	    //    On 30 Mar
	    //
	    $out .= "<div class='movieinfo'>\n";
	    $out .= "<h3 class='movieshowing'>\n";
	    if (empty($movie->startdate))
		$movie->startdate = 0;
	    if (empty($movie->enddate))
		$movie->enddate = 0;
	    if (empty($movie->previewdate))
		$movie->previewdate = 0;
	    $showing = '';
	    if ($session_count > 0 || $this->movie_has_started($movie))
	    {
		$showing = 'Now showing';
	    }
	    elseif ($movie->startdate == 0 && $movie->first_session == 0)
	    {
		// show something generic
		$showing = 'Coming Soon';
	    }
	    elseif (
		! empty($movie->hidestart)
		&& $movie->startdate != 0
		&& ! $this->movie_has_started($movie))
	    {
		// show something generic - they are hiding startdate
		$showing = 'Coming Soon';
	    }
	    else
	    {
		// Choose between From, On and Preview
		$from = 'From ';
		if ($movie->enddate != 0
		    && $movie->startdate == $movie->enddate)
		        $from = 'On ';

		if ($movie->previewdate != 0
		    && strtotime($movie->previewdate) > time())
			$from = "Preview ";

		$showing = $from . date("F j", strtotime(
			($movie->startdate != 0) ?
				$movie->startdate :
				$movie->first_session->session_datetime
		));
	    }
	    $out .= "<a href='$movieinfolink' title='$movietip'>$showing</a>\n";

	$out .= "</h3>\n";
	$out .= "<h3 class='movietitle'>\n";

	    // Display title
	    $title = $movie->title;
	    if ($opt['titlestocaps'] && empty($movie->leavetitle))
		$title = strtoupper($title);

	    // Display rating, or default rating, or nothing if default unset
	    $rating = '';
	    if (! empty($movie->rating))
		$rating = $movie->rating;
	    elseif (! empty($opt['defaultrating']))
		$rating = $opt['defaultrating'];
	    if (! empty($rating))
		$title .= "<span class='rating'> ($rating)</span>";
		
	    // Display NFT tag until the last day of NFT has ended
	    // NFT = No Free Tickets
	    if (! empty($movie->nftdate)
		&& (strtotime($movie->nftdate) + 24*3600) > time())
	    {
		$title .= " <span class='rating'>$nft</span>";
	    }

	    $out .= "<a href='$movieinfolink' title='$movietip'>$title</a>\n";

	    $out .= "</h3>\n";
	    $out .= "<div class='sessions'>\n";

	    //  Display sessions
	    //
	    $sep = '';
	    $day_printed = 0;
	    $j = $session_count;
	    foreach ($movie->sessions as $session)
	    {
		$sesstime = strtotime($session->session_datetime);

		// we only do one day's sessions here at the moment
		// (no code here yet for handling day change)
		if (! $day_printed++)
		    $out .= date("D", strtotime($sesstime)). ": ";

		$out .= $sep;

		$sessionbookinglink = $this->moviebookingurl(
		    'TICKET',
		    $movie,
		    $session
		);
		
		$linktext = date('g:ia', $sesstime);
		
		// option toolate: mins before session start to close booking
		if ($sesstime < (time() - $opt['toolate']*60))
		{
		    // bookings closed - display a non-linked time
		    $tip = Array("title"=>"Session closed");
		    $out .= '<span title="Session closed">'.$linktext.'</span>';
		}
		else
		{
		    // bookings open
		    $tip = "Click to book for $linktext session";
		    $out .= "<a href='$sessionbookinglink' title='$tip'>"
			. "$linktext</a>\n";
		}
		$sep = ', ';
		if (1 == --$j)
		    $sep = ' & ';
	    }

	$out .= "</div>\n";
	$out .= "<div class='links'>\n";

	    // if we have any sessions and a booking link, output booking link
	    if (! empty($movie->first_session) && ! empty($bookinglink))
	    {
		$booktip = "Click to see all session times";
		$out .= "<a href='$bookinglink' title='$booktip'>"
		    . "Book Online</a><br />";
	    }

	    // Display movie info link, if we have one
	    if (! empty($movieinfolink))
	    {
		$out .= "<a href='$movieinfolink' title='$movietip'>"
		    . "More Information</a><br />";
	    }

	    // Display Official URL if we have one
	    if (! empty($movie->officialurl))
	    {
		$ourl = $movie->officialurl;
		$ourl = urlencode($ourl);
		if (! preg_match("/^https?:/", $ourl))
		    $ourl = "http://" . $ourl;
		$out .= "<a href='$ourl' target=_blank "
		    . "title='Click to see movie website'>"
		    . "Official Website & Trailer</a><br />"; 
	    }

	    $out .= "</div>\n</div>\n<br class='clr' />\n</div>\n";

    } // foreach

    // A shortcode must return the result
    $out .= "<div style='clear: both;'>\n</div>\n";

    return $out;

} // daily()


    // returns true if movie has started by date
    // idea of "started" is "past start date"
    // exists solely to make logic a little simpler to read
    //
    function movie_has_started($movie, $date = NULL)
    {
	// "$date" is unix time of start if given
	if (empty($movie->startdate))
	    return false;
	if (empty($date))
	    $date = time();
	// returns false if no movie startdate
	if (strtotime($movie->startdate) > $date)
	    return true;
	return false;
    }


    // called to get URL for booking movie
    function moviebookingurl($type, $movie = NULL, $session = NULL)
    {
	// use a filter here to get the URL from other plugins
	$url = '';
	$url = apply_filters('wpcinema_bookurl', $url, $type, $movie, $session);
	return $url;

	// add filters like:
	//  add_filter('wpcinema_bookurl', array(&$this, 'bookurl'), 20, 4);
    }


} // class


// PRO START
class wpcin_notyet_notyet
{

    function doThumbs($movie_image)
    {
        // get image width config
        $thumbWidth = $yc->thumbWidth;
        $thumbHeight = $yc->thumbHeight;
        $largeThumbWidth = $yc->largeThumbWidth;
        $largeThumbHeight = $yc->largeThumbHeight;
        
	$image_path = JPATH_BASE.DS."images".DS."wpcinema".DS.$movie_image;
	$thumb_image_path = JPATH_BASE.DS."images".DS."wpcinema".DS."thumbs".DS."${thumbWidth}_${thumbHeight}_".$movie_image;
	$large_thumb_image_path = JPATH_BASE.DS."images".DS."wpcinema".DS."thumbs".DS."${largeThumbWidth}_${largeThumbHeight}_".$movie_image;
	if (file_exists($image_path))
	{
	    if (! file_exists($thumb_image_path))
	    {
                self::__do_single_thumb($image_path, $thumbWidth, $thumbHeight, $thumb_image_path);
            }
	    if (! file_exists($large_thumb_image_path))
	    {
                self::__do_single_thumb($image_path, $largeThumbWidth, $largeThumbHeight, $large_thumb_image_path);
            }
	}
    }
    
    function __do_single_thumb($original, $width, $height, $dest)
    {
        $image_canvas = imagecreatetruecolor($width, $height);
    
        list($width_orig, $height_orig) = getimagesize($original);
        $ratio_orig = $width_orig / $height_orig;

	if (preg_match('/\.jpe?g$/i', $original))
	{
            $image = imagecreatefromjpeg($original);
	}
	elseif (preg_match('/\.png$/i', $original))
	{
            $image = imagecreatefrompng($original);
	}
	elseif (preg_match('/\.gif$/i', $original))
	{
            $image = imagecreatefromgif($original);
	}
	else
	{
            $image = imagecreatefromwbmp($original);
        }
        
        $new_width = $width;
        $new_height = $height;
	if (($width / ($height * 1.0)) > $ratio_orig)
	{
           $new_width = $height * $ratio_orig;
	}
	else
	{
           $new_height = $width / $ratio_orig;
        }
        
        $x_offset = ($width - $new_width) / 2.0;
        $y_offset = ($height - $new_height) / 2.0;

	imagecopyresampled($image_canvas, $image, $x_offset, $y_offset,
	    0, 0, $new_width, $new_height, $width_orig, $height_orig);

        // Output
        imagejpeg($image_canvas, $dest);
    }
}
// PRO END




$WP_Cinema_Plugin_Views = new WP_Cinema_frontend_views();

// end
