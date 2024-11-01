<?php

/*
 * This file is all about Movie Administration.
 * The functions included here allow a user to:
 *   - have a movie admin screen, which displays recently updated movies
 *   - search for an existing movie for editing purposes
 *   - define a new movie
 *   - create sessions if manual session creation is turned on
 *
 * Appears on a top-level menu in the WP Admin menu.
 * Some functions are disabled by our global options -
 *   eg add new, create movie, manage sessions
 *
 * NB: When linked with automation, you want the automation to create the movie
 *     and to manage sessions so everything is kept in sync.
 *
 * Re permissions: would like it to be possible for a user to have movie
 * editing privileges without permission to change WP Cinema settings.
 */


require_once("movie_fields.php");

require_once("class-fields.php");

define('WPCIN_IMAGES', '/wp-cinema/images');

class WP_Cinema_Plugin_Movie_Admin extends WPCinema_fields
{
    public $movie_fields = null;         // initialized by constructor
    public $movtab = 'wpcinema_movies';     // constructor adds prefix
    public $sesstab = 'wpcinema_sessions';     // constructor adds prefix
    public $movie_nonce = 'wpcinema-movie-update';
    public $session_nonce = 'wpcinema-sessions';
    public $options = null;


    //  Initial hook setup
    //
    function __construct()
    {
	add_action('admin_menu', array(&$this, 'add_admin_menus' ) );

	global $WPCinema_movie_fields;
	$this->movie_fields = $WPCinema_movie_fields;
	global $wpdb;
	$this->movtab = $wpdb->prefix . $this->movtab;
	$this->sesstab = $wpdb->prefix . $this->sesstab;

        $this->options = get_option(WPCIN_OPTIONS);
    }


    //  Admin Menu setup
    //
    function add_admin_menus()
    {
	// main page
	//  - perms: author or below
	add_menu_page(
		'Movies',
		'Movies',
		'edit_posts', // permission - author or below
		__FILE__,
		array(&$this, 'manage_movies_page'),
		WPCIN_URL.'/images/admin16x16.png',
		4 // side menu used: 0=top/avail 2=Dashboard 5=Posts
	);

	// shortcodes doco page
	//  - perms: if they can edit pages, they should be shown this
	add_submenu_page(
	    __FILE__, 		// $parent_slug,
	    "WP Cinema Shortcodes Explanations", 	// $page_title,
	    "Shortcodes", 		// $menu_title,
	    'edit_pages', 		// $capability, - editor or above
	    'wpc_shortcodes', 		// $menu_slug,
	    array(&$this, 'shortcodes_doco')
	);

	// add movie page
	if (! empty($this->options['no-manual-movie-create']))
	    return;
	add_submenu_page(
	    __FILE__, 		// $parent_slug,
	    "WP Cinema Add Movie", 	// $page_title,
	    "Add Movie", 		// $menu_title,
	    'edit_pages', 		// $capability, - editor or above
	    'new_movie',
	    array(&$this, 'edit_movie')
	);
    }


    //  Shortcodes doco page
    //
    function shortcodes_doco()
    {
	if (! current_user_can('edit_pages'))
	    wp_die("You don't have permission.");


	// TODO need some thumbnails along with this

	echo "<div class='wrap'>";
	echo "<img style='float:left; padding: 5px;' src='"
	    . WPCIN_URL . "/images/admin32x32.png'>";

	$html = file_get_contents(WPCIN_PATH."/doc/shortcodes.html");

	// intended to prevent shortcode expansion: [-- ==> [
	$html = preg_replace('/\[--/', '[', $html);

	echo $html;
    }


    // called from Add movie menu entry
    // the code below in manage_movies_page for editing should be moved here
    function edit_movie()
    {
	$this->manage_movies_page('edit');
    }


    // Main movie page handler
    //
    function manage_movies_page($func = null)
    {
	// Cases we have to handle:
	//  - default: search page for new or existing
	// 	    (enter movie name/part name)
	//  [- add: enter year and name fields (for finding pre-existing)]
	//  - add-search: display list of matched movies: names, cast, images
	//          choose a movie by clicking on it
	//  - edit: enter and save all fields, modify existing data
	//  - edit-search: from default, list matching movies, click to select
	//  - save: from edit --> create or modify
	//
	//  Process:
	//  * create: default (via add field/button) ->
	//  	add-search -> edit -> save
	//  * modify: default (via edit field/button)->
	//  	edit-search -> edit -> save
	//
	global $wpdb;

	if (! current_user_can('manage_options'))
	    wp_die("You don't have permission.");

        $opt = $this->options;

	if (empty($func))
	{
	    $func = '';
	    if (! empty($_REQUEST['func']))
		$func = $_REQUEST['func'];
	}

	// The plugin icon ...
	echo "<div class='wrap'>";
	echo "<img style='float:left; padding: 5px;' "
	   . "src='".WPCIN_URL."/images/admin32x32.png'>";

	// Master (overly long) functionality switch ...
	//
	switch ($func)
	{
	  case '': 
	      // show entry page with search fields
	      // search will be done by ajax eventually
	      //
	      $editurl = add_query_arg(array('func' => 'edit'));
	      echo "<h2>Manage Movies";
	      if (! $opt['no-manual-movie-create'])
		  echo "<a href='$editurl' class='add-new-h2'>Add New</a>";
	      echo "</h2></div>\n";
	      if (! $opt['no-manual-movie-create'])
	      {
		  echo "<h3><a href='$editurl'>";
		  echo "Enter new movie";
		  echo "</a></h3><br />\n";
	      }
	      $editsearch = add_query_arg(array('func' => 'edit-search'));
	      echo "<form method=POST action='$editsearch'>\n";
	      wp_nonce_field('wpcinema-search');
	      echo "Find existing movie: &nbsp;";
	      echo "<input name=searchstr type=text size=10>\n";
	      echo "<input name=search type=submit value=Search>\n";
	      echo "</form>\n";
	      echo "<p>&nbsp;</p>\n";
	      $count = $wpdb->get_var("SELECT COUNT(*) FROM $this->movtab");
	      echo "<p><font color=silver>Movies in database: $count</font></p>";
	      $movies = $wpdb->get_results("SELECT * FROM $this->movtab "
		  . "ORDER BY `lastupdated` DESC LIMIT 5");
	      echo "<strong>Most recently updated movies:";
	      echo "</strong><br><br>\n";
	      foreach ($movies as $movie)
	      {
		  $myedit = add_query_arg(
		      array(
			  'func' => 'edit',
			  'id' => $movie->id
		      )
		  );
		  if (! empty($movie->releaseyear))
		      $release = " &nbsp; ($movie->releaseyear)";
		  echo "<bold><a href='$myedit'> "
			  . "$movie->title $release</a></b>\n";
		  echo " &nbsp; <a href='$myedit'>(edit)</a>\n";
		  echo "<br>\n";

		  // Movie details in fine print
		  echo "<i>";
		  $semicolon = '';
		  $moviedetails = array("cast", "rating",
		      	"runtime", "officialurl");
		  foreach ($moviedetails as $i)
		  {
		      // only output if the column is non-empty
		      if (empty($movie->$i))
			  continue;
		      $data = $movie->$i;
		      $subhead = ucwords($i);

		      // Allow fine tuning for headings etc
		      switch ($i)
		      {
			case 'releaseyear': $subhead = ""; break;
			case 'runtime': $data .= "m";  break;
			case 'officialurl':
			    $subhead = '';
			    $data = "<a href='$data' target=_blank "
				. "title='$data'> Official URL</a>";
			    break;
		      }

		      echo $semicolon;
		      echo "<font face=Verdana size=-2>";
		      if (! empty($subhead))
			  echo "$subhead: ";
		      echo $data;
		      echo "</font>";

		      $semicolon = "; &nbsp; ";
		  }
		  echo "</i>";
		  echo "<br>\n";
		  echo "<br>\n";
	      }
	      break;

	  case 'add-search':   // (submit)
	      // show search results for adding (from shared descriptions)
	      //
	      echo "<h2>Search existing movies</h2>";
	      echo "</div>\n";
	      echo "<br>\n";
	      // ie: not yet implemented ...
	      echo "(the droids you are looking for are also not here)";
	      echo "<br>\n";
	      break;

	  case 'edit-search': //  (submit)
	      // show search results for editing (from existing local movies)
	      //
	      check_admin_referer('wpcinema-search');
	      echo "<h2>Search existing movies</h2>";
	      echo "</div>\n";
	      echo "<br />\n";
	      $editsearch = add_query_arg(array('func' => 'edit-search'));
	      echo "<form method=POST action='$editsearch'>\n";
	      wp_nonce_field('wpcinema-search');
	      echo "Find existing movie: &nbsp;";
	      echo "<input name=searchstr type=text size=10>\n";
	      echo "<input name=search type=submit value=Search>\n";
	      echo "</form>\n";
	      echo "<br>\n";

	      $like = "%" . trim($_REQUEST['searchstr']) . "%";
	      $query = "SELECT * from $this->movtab where title LIKE \"%$like%\"";
	      $movies = $wpdb->get_results($query);
	      // echo "res: "; print_r($movies); //ZZZ TODO: fix
	      if (count($movies) == 0)
	      {
		  echo "No matching movies found.<br><br>\n";
		  return;
	      }
	      echo "<strong>Click the movie you would like to edit...";
	      echo "</strong><br><br>\n";
	      foreach ($movies as $movie)
	      {
		  $myedit = add_query_arg(
		      array(
			  'func' => 'edit',
			  'id' => $movie->id
		      )
		  );
		  if (! empty($movie->releaseyear))
		      $release = " &nbsp; ($movie->releaseyear)";
		  echo "<bold><a href='$myedit'> "
			  . "$movie->title $release</a></b>\n";
		  echo " &nbsp; <a href='$myedit'>(edit)</a>\n";
		  echo "<br>\n";

		  // Movie details in fine print
		  echo "<i>";
		  $semicolon = '';
		  $moviedetails = array("cast", "rating",
		      	"runtime", "officialurl");
		  foreach ($moviedetails as $i)
		  {
		      // only output if the column is non-empty
		      if (empty($movie->$i))
			  continue;
		      $data = $movie->$i;
		      $subhead = ucwords($i);

		      // Allow fine tuning for headings etc
		      switch ($i)
		      {
			case 'releaseyear': $subhead = ""; break;
			case 'runtime': $data .= "m";  break;
			case 'officialurl':
			    $subhead = '';
			    $data = "<a href='$data' target=_blank "
				. "title='$data'> Official URL</a>";
			    break;
		      }

		      echo $semicolon;
		      echo "<font face=Verdana size=-2>";
		      if (! empty($subhead))
			  echo "$subhead: ";
		      echo $data;
		      echo "</font>";

		      $semicolon = "; &nbsp; ";
		  }
		  echo "</i>";
		  echo "<br>\n";
		  echo "<br>\n";
	      }
	      break;

	  case 'edit':
	      // show movie details form and allow saving
	      //
	      $doing = 'enter';
	      $id = '';
	      if (! empty($_REQUEST['id']))
	      {
		  $id = preg_replace('/[^\d]/', '', $_REQUEST['id']);
		  $doing = 'edit';
	      }
	      $Doing = ucwords($doing);
	      echo "<h2>$Doing Movie Details</h2>\n"; 
	      echo "<br>\n";
	      if ($opt['no-manual-movie-create'])
	      {
		  echo "Sorry; manual creation of movies is disabled";
		  return;
	      }
	      if ($id)
	      {
		  $m = $wpdb->get_row(
		      $wpdb->prepare(
			  "SELECT * from $this->movtab WHERE id = %s",
			  $id
		      ),
		      ARRAY_A
		  );
	      }
	      $movie = array();
	      if (! empty($m['title']))
		  $movie = $m;
	      echo "<b>Please $doing your movie details below</b>";
	      echo "</div>\n";
	      echo "<br>\n";
	      $url = add_query_arg(array('func' => 'save', 'id' => $id));
	      echo "<form method='POST' action='$url'>\n";
	      wp_nonce_field($this->movie_nonce);
	      echo $this->fields2form($movie, $this->movie_fields, true);

// adjust values here
$image_id = "image"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $image_id == “img1” then $_POST[“img1”] will have all the image urls
 

$updirs = wp_upload_dir();
if (! empty($movie['image']))
    $svalue = $updirs['baseurl'] . WPCIN_IMAGES . '/' . $movie['image'];
else
    $svalue = '';
// $svalue = ''; // this will be initial value of the above form field. Image urls.
 
$multiple = false; // allow multiple files upload

 
$width = 100; // If you want to automatically resize all uploaded images then provide width here (in pixels)
 
$height = 140; // If you want to automatically resize all uploaded images then provide height here (in pixels)
?>
 
<tr>
<th>
<label>Movie Thumbnail</label>
</th>
<td>
<input type="hidden" name="<?php echo $image_id; ?>" id="<?php echo $image_id; ?>" value="<?php echo $svalue; ?>" />
<div class="plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $image_id; ?>plupload-upload-ui">
    <input id="<?php echo $image_id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Image'); ?>" class="button" />
    <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($image_id . 'pluploadan'); ?>"></span>
    <?php if ($width && $height): ?>
            <span class="plupload-resize"></span><span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
            <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
    <?php endif; ?>
    <div class="filelist"></div>
</div>
<div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?>" id="<?php echo $image_id; ?>plupload-thumbs">
</div>
<td>
</tr>
</table><br />

	<?php
// <div class="clear"></div>
	      
	      submit_button();
	      echo "</form>\n";

	      if (! empty($id))
	      {
		  // SESSION MANAGEMENT
		  $this->session_form($id, $this->movie_nonce, $url);
		  $this->session_display($id);
	      }
	      break;

	  case 'save':
	      // save (as new entry, or modify, as appropriate)
	      //
	      check_admin_referer($this->movie_nonce);
	      echo "<h2>Movie Details</h2>\n"; 
	      echo "</div>\n";

	      //
	      // echo "Debug: "; print_r($_POST); // ZZZ
	      $image = '';
	      $id = '';
	      if (! empty($_REQUEST['id']))
		  $id = preg_replace('/[^\d]/', '', $_REQUEST['id']);
	      if (! isset($_REQUEST['session']))
	      {
		  $res = $this->savemovie(
		      $this->movtab,
		      $this->movie_fields,
		      $_POST,
		      $id,
		      &$image
		  );
		  if ($res !== false)
		  {
		      echo "<div id='message' class='updated'>"
		         . "Your movie saved successfully.</div>";
		      $id = $res;
		  }
		  // ERROR: Duplicate entry 'TESTING123' for key 'shortid'
		  elseif (preg_match('/Duplicate .* for key/i', $wpdb->last_error))
		  {
		      echo "<div id='message' class='error'>"
		         . "That shortid is taken, please use another.</div>";
		  }
		  else
		      echo "<div id='message' class='error'>"
		         . "We had a problem saving your movie.</div>";
	      }
	      else
	      {
		  // movie fields weren't posted so need to retrieve
		  $movie = $wpdb->get_row(
		      $wpdb->prepare(
			  "SELECT * from $this->movtab WHERE id = %s",
			  $id
		      ),
		      ARRAY_A
		  );
		  // default fields into $_POST
		  // kludge alert - sorry!
		  // TODO: we should not be modifying $_POST! :( ZZ
		  foreach ($movie as $key => $val)
		  {
		      if (empty($_POST[$key]))
			  $_POST[$key] = $val;
		  }
	      }

	      // the image path in the posted form is the temp one from
	      // Ajax ... not where it was moved to by savemovie() so this
	      // is how we work around that ...
	      if (! empty($image))
		  $_POST['image'] = $image;
	      elseif (! empty($_POST['image']))
		  $_POST['image'] = basename($_POST['image']);

	      // TODO: validate here!
	      // echo "<br><b>Please enter your movie details below</b><br>\n";
	      // $this->fields2form($_POST, $this->movie_fields);
	      /////////////////////
	      //
	      echo "<b>Please enter your movie details below</b>";
	      echo "<br>\n";
	      echo "<form method='POST'>\n";
	      wp_nonce_field($this->movie_nonce);
	      echo $this->fields2form($_POST, $this->movie_fields, true);



	      
// adjust values here
$image_id = "image"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == “img1” then $_POST[“img1”] will have all the image urls
 

$updirs = wp_upload_dir();
if (! empty($_POST['image']))
    $svalue = $updirs['baseurl'] . WPCIN_IMAGES . '/' . $_POST['image'];
else
    $svalue = '';
// $svalue = ''; // this will be initial value of the above form field. Image urls.
 
$multiple = false; // allow multiple files upload

 
$width = 100; // If you want to automatically resize all uploaded images then provide width here (in pixels)
 
$height = 140; // If you want to automatically resize all uploaded images then provide height here (in pixels)
?>
 
<tr>
<th>
<label>Movie Thumbnail</label>
</th>
<td>
<input type="hidden" name="<?php echo $image_id; ?>" id="<?php echo $image_id; ?>" value="<?php echo $svalue; ?>" />
<div class="plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $image_id; ?>plupload-upload-ui">
    <input id="<?php echo $image_id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Image'); ?>" class="button" />
    <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($image_id . 'pluploadan'); ?>"></span>
    <?php if ($width && $height): ?>
            <span class="plupload-resize"></span><span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
            <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
    <?php endif; ?>
    <div class="filelist"></div>
</div>
<div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?>" id="<?php echo $image_id; ?>plupload-thumbs">
</div>
<td>
</tr>
</table><br />

	<?php
// <div class="clear"></div>


	      submit_button();
	      echo "</form>\n";

	      // if (! empty($id) && ! empty($_REQUEST['augh']))
	      // if we don't have an Id we can't add sessions yet ...
	      if (! empty($id))
	      {
		  // SESSION MANAGEMENT
		  if (empty($url)) $url = ''; //ZZ TODO: why is url undef here?

		  $this->session_manage($id, $this->movie_nonce);
		  $this->session_form($id, $this->movie_nonce, $url);
		  $this->session_display($id);
	      }

	      break;

	  default:				// works at top!
	      echo "<h2>Unknown page requested: $func</h2>\n"; 
	      break;
	}

	echo "<p>&nbsp;</p>\n";
    }


    //  Save a movie in the database after editing
    //  returns 0 if there was a problem
    //
    function savemovie($dbtable, $fields, $data, $modifyid = null, &$imageret)
    {
	// TODO: not sure how to handle modifying an existing record yet

	global $wpdb;

	$types = $values = array();
	$notsaved = $fields;
	// print_r($data); //ZZZ
	foreach ($fields as $key => $field)
	{
	    // assume these will stay in the order they are assigned
	    if (! empty($data[$key]))
		$values[$key] = $data[$key];
	    else
		$values[$key] = ''; // checkbox
	    $format = '%s';
	    $hadimage = 0;
	    if (isset($field['type']))
	    {
		switch ($field['type'])
		{
		    case '':   $format = '%s'; break;
		    case 'image':
			$hadimage++;
			$values[$key] = $imageret = $this->saveimage(
			    $dbtable,
			    $modifyid,
			    $key,
			    $data
			);
			if (empty($values[$key])) // no change
			    unset($values[$key]);
			break;

		    case 'date':
			$format = '%s';
			if (! empty($data[$key]))
			{
			  $oztime = preg_replace(':/:', '-', $data[$key]);
			  $values[$key] = date('Y-m-d', strtotime($oztime));
			}
			break;
		    default:   $format = '%s'; break;
		}
	    }
	    $types[] = $format;
	    unset($notsaved[$key]);  // keep track
	}
	if (! $hadimage)
	{
	    // process uploaded image and insert it in fields to save
	    // empty image means not provided
	    $key = 'image';
	    $values[$key] = $imageret = $this->saveimage(
		$dbtable,
		$modifyid,
		$key,
		$data
	    );
	    if (empty($values[$key])) // no change
		unset($values[$key]);
	}
	if (count($notsaved) > 0)
	{
	    die("unknown posted $dbtable fields: " . implode(' ', $notsaved));
	}
	if (empty($modifyid))
	{
	    $res = $wpdb->insert($dbtable, $values, $types);
	    $id = $wpdb->insert_id;
	    if ($res == false)
		$id = $res; // return failure
	}
	else
	{
	    $where = array('id' => $modifyid);
	    $wheretypes = array('%s');
	    $res = $wpdb->update(
		$dbtable,
		$values,
		$where,
		$types,
		$wheretypes
	    );
	    $id = $modifyid;
	    if ($res == false)
		$id = $res; // return failure
	}

	return $id;
    }


    //
    // Handle saving new image in filesystem
    // we get handed back a URL from the Ajax uploader
    // If the URL contains /temp/, a new image was uploaded
    // If $id is set, we're modifying movie with that id.
    // Returns the image name (basename) for saving in the movie database entry
    //
    function saveimage($dbtable, $id, $key, $data)
    {
	global $wpdb;

	$image = $data[$key];
	$ext = preg_replace('/^.*\./', '', $image);
	$ext = strtolower($ext);

	// if /temp/ occurs in name, an image has been uploaded
	if (! preg_match('/temp/', $image))
	    return '';

	// get official upload dir
	$updirs = wp_upload_dir();
	$basedir = $updirs['basedir'] . WPCIN_IMAGES;
	$tempdir = $updirs['basedir'] . "/wp-cinema/temp";

	// if image contains '/temp/', they have uploaded a new image
	// (naming note: oiname = old image name, nipath = new image path, etc)
	// "old" means "in the official wpcinema image area" not just "old"!
	$oiname = '';
        if (! empty($_REQUEST['id']))
	{
	    $id = preg_replace('/[^\d]/', '', $_REQUEST['id']); // sanitise
	    $lastname = $wpdb->get_var("SELECT $key FROM $dbtable "
		. "where id = $id");
	    $oiname = $lastname;
	}
	if (! empty($oiname))
	{
	    // we have an old image in the database - delete the file
	    $oipath = "$basedir/$oiname";
	    if (file_exists($oipath))
		@unlink($oipath);
	    if (file_exists($oipath)) // should have been unlinked
		wp_die("wpcinema: could not remove old image file ${oipath}");
	    
	    // rejig extension for new upload
	    $oistem = preg_replace('/\.[^.]+$/', '', $oiname);
	    $oiname = $oistem .'.'. $ext;
	}
	else
	{
	    // no image yet - make a filename out of movie name
	    // ensure we are not overwriting anything here
	    $i = '';
	    $smushed = $this->moviefilename($data);
	    do
	    {
		$oiname = $smushed . $i++ .'.'. $ext;
		$oipath = "$basedir/$oiname";
	    } while (file_exists($oipath));
	}

	$niname = basename($image);
	$nipath = "$tempdir/$niname";
	if (! file_exists($nipath))
	    wp_die("wp-cinema: uploaded image not found at $nipath");

	// create final images folder if doesn't exist
	$dir = dirname($oipath);
	if (! is_dir($dir) && ! wp_mkdir_p($dir))
	    wp_die("wp-cinema: could not create folder $dir");

	if (! rename($nipath, $oipath))
	    wp_die("wp-cinema: failed to rename image to $oipath");

	// the stem is stored in the database
	return $oiname;
    }


    // Make a file name out of a movie name
    // we 'smush' - case-smash and remove extraneous chars to enable "loose"
    // comparison of strings that may not exactly match due to
    // punctuation, abbreviation, spacing etc
    // Not using year at present, but we may start doing so as a dir name
    //
    function moviefilename($movie)
    {
	$fullname = $movie['title'];
	$year = $movie['releaseyear'];

        // A Movie Name --> moviename
        $b = strtolower($fullname);
        $b = trim($b);

        // remove generic words
	// possibly this should be done just at start or end of line?
        $b = preg_replace('/\ba\b/i', '', $b);
        $b = preg_replace('/\bthe\b/i', '', $b);

	// idea is to add to the above words to remove over time

        $b = trim($b);
        $b = preg_replace('/[^a-z0-9]+/', '', $b);

	if (empty($b))  $b = "movie_was_blank";
        return $b;
    }


    // display session form to allow cinema sessions to be entered
    function session_form($id, $nonce = null, $url = null)
    {
	$action = '';
	if (! empty($url))
	{
	    // $url = add_query_arg(array('func' => 'sessions'));
	    $action=" action='$url'";
	}
	if (empty($nonce))
	    $nonce = $this->session_nonce;

	if (! empty($this->session_note))
	{
	    // need to change colour here etc
	    // this appears at page top instead of above session form
	    echo "<div id='message1' class='updated'>"
		. $this->session_note. "</div><br>\n";
	    $this->session_note = '';
	}

	echo "<form method=POST$action>\n";
	wp_nonce_field($nonce);
	echo "Session add - enter date and time: &nbsp;";
	echo "<input name=id type=hidden value='$id'>\n";
	echo "<input name=sessiondate type=text size=10>\n";
	// echo "<input name=session type=submit value='Add Session'>\n";
	echo " &nbsp; ";
	echo " &nbsp; ";
        submit_button('Add Session', 'primary', 'session', false);
	echo "</form>\n";
	// echo "<p>&nbsp;</p>\n";
	echo "<br>\n";
	echo "<small>Enter session date and time; ranges are OK. "
	    	. "eg: 19-20/7 3:45pm, 1 April 7:15pm.</small>\n";
    }


    // execute session requests from form when submitted
    // id is the movie_id
    // TODO: add 'cancel' request, eg 'cancel 9-11/7 7:30pm'
    //
    function session_manage($id, $nonce)
    {
	if  (! isset($_REQUEST['sessiondate']))
	    return;

	if (empty($nonce))
	    $nonce = $this->session_nonce;
	check_admin_referer($nonce);

	if (empty($id))
	{
	    $this->session_note = 'empty id in session_manage';
	    return;
	}
	$str = trim($_REQUEST['sessiondate']);
	if (empty($str))
	{
	    $this->session_note = "empty value? did you enter anything?";
	    return;
	}

	$sessions = $this->session_parse($str);
	if (empty($sessions) || count($sessions) == 0)
	{
	    $this->session_note = "Could not parse that session, sorry: '$str'";
	    return;
	}
	$res = $this->session_save($id, $sessions);
	if (! empty($res))
	    $this->session_note = $res;
    }


    // parse an entered string into session times (unix times)
    // this involves more than a little acrobatics
    //
    // possibilities for date:
    //   case 1:  2-19 April
    //   	  17-19/2
    //   case 2:  17/2 - 19/2
    //   case 3:  2 Apr - 2 May
    //   case 4: would like to but don't: 3,7,9 May
    //   case 5: would like to but don't: 3,7,9/4  and 3/4,7/4,9/4
    // possibilities for time:
    //   9:00
    //   9pm
    //   0900
    //   would like to but not yet: comma separated list of the above
    // returns an array of unix times
    //
    function session_parse($str)
    {
	list($start, $end) = $this->parse_one($str);
	$sessions = array();
	if ($start == 0)
	    return $sessions;
	if ($end == 0)
	    $end = $start; // default to single sessions

	// build up list of sessions
	for ($t = $start; $t <= $end; $t += 86400)
	{
	    $sessions[] = $t;
	}

	return $sessions;
    }


    // parse session into a range. if end is 0, it's a single session
    // we return start and end unix times
    // assumption is sessions are 24 hours apart
    function parse_one($s)
    {
	// case 1:  dd-dd April  and   dd-dd/mm
	$t1 = preg_replace(':^\s*(\d+)\s*-\s*\d+:', '$1', $s);
	if ($t1 != $s)
	{
	    $t2 = preg_replace(':^\s*\d+\s*-\s*(\d+):', '$1', $s);
	    $d1 = $this->my_strtotime($t1);
	    $d2 = $this->my_strtotime($t2);
	    return array($d1, $d2);
	}

	// case 2:  dd/mm - dd/mm   eg 2/4-5/4
	$t1 = preg_replace(':^\s*(\d+/\d+)\s*-\s*\d+/\d+:', '$1', $s);
	if ($t1 != $s)
	{
	    $t2 = preg_replace(':^\s*\d+/\d+\s*-\s*(\d+/\d+):', '$1', $s);
	    $d1 = $this->my_strtotime($t1);
	    $d2 = $this->my_strtotime($t2);
	    return array($d1, $d2);
	}

	// case 3:  eg: 2 Apr - 2 May
	$t1 = preg_replace(':^\s*(\d+\s*\S+)\s*-\s*\d+\s*\S+\s*:', '$1 ', $s);
	if ($t1 != $s)
	{
	    $t2 = preg_replace(':^\s*\d+\s*\S+\s*-\s*(\d+\s*\S+):', '$1', $s);
	    // echo "GOT HERE: $t1 ----- $t2<br>\n"; //ZZ
	    $d1 = $this->my_strtotime($t1);
	    $d2 = $this->my_strtotime($t2);
	    return array($d1, $d2);
	}

	// case 4: 3,7,9 May

	// case 5: 3,7,9/5  and 3/5,7/5,9/5

	// case: just a single date and time
	$d = $this->my_strtotime($s);
	return array($d, 0);

	return 0;
    }


    // more robust strtotime() version
    // parses a lot more date versions - see inline comments for details
    // does not cope with US date format yet  (eg 4/22 == 22 April)
    // should default to pm, doesn't yet
    function my_strtotime($str)
    {
	$t = trim($str);

	$monthyear = date("F Y");
	$year = date("Y");

	// 15 Jun -> 15 Jun 2012
	$t = preg_replace('/\s+([a-z]\S+)\s+(?!20\d\d)/i', ' $1 '."$year ", $t);

	// 935[pm] -> 9:35[pm]
	$t = preg_replace('/\s+(\d)(\d\d)(am|pm)?$/i', ' $1:$2$3', $t);
	$t = preg_replace('/^(\d)(\d\d)(am|pm)?$/i', '$1:$2$3', $t);

	$t = preg_replace('/^(\d+)(th|nd|rd)?\s+(?=\d+:)/i',
	    '$1$2 '.$monthyear.' ',
	    $t);
	
	// 2012 at end fails - they left off the time! without this,
	// it would match below... as 8:12pm!
	if (preg_match('/20[12]\d$/', $t))
	    return null;

	// 1235pm -> 12:35pm
	$t = preg_replace('/\s+(\d\d)(\d\d)(am|pm)?$/i', ' $1:$2$3', $t);
	$t = preg_replace('/^(\d\d)(\d\d)(am|pm)?$/i', '$1:$2$3', $t);

	// 2/4 -> 2-4-2012
	$t = preg_replace(',^(\d+)/(\d+)\s,i', '$1-$2-'.$year.' ', $t);

	// Jun 15 -> Jun 15 2012  (this is our only US concession so far)
	$t = preg_replace('/^([a-z](?!day)\S+)\s+(\d+)\s(?!20\d\d)/i',
	    '$1 $2 '.$year.' ',
	    $t);

	// expand common day of the week abbreviations
	// this obviously needs to be internationalized
	$t = preg_replace('/\bsun\b/i', 'sunday', $t);
	$t = preg_replace('/\bmon\b/i', 'monday', $t);
	$t = preg_replace('/\btues?\b/i', 'tuesday', $t);
	$t = preg_replace('/\bweds?\b/i', 'wednesday', $t);
	$t = preg_replace('/\bthurs?\b/i', 'thursday', $t);
	$t = preg_replace('/\bfrid?\b/i', 'friday', $t);
	$t = preg_replace('/\bsat\b/i', 'saturday', $t);

	return strtotime($t);
    }


    // Display sessions in compact tabular form
    // should use <table> but that can go in next release
    function session_display($id)
    {
	global $wpdb;

	$sesstab = $this->sesstab;
	echo "<h3 title='$id'>Sessions:</h3>\n";
	$existing = $wpdb->get_col("SELECT session_datetime FROM $sesstab "
	    . "WHERE movie_id = '$id'");
	$day = '';
	$year = date('Y');
	foreach ($existing as $ex)
	{
	    $d = date('D j M Y', strtotime($ex));
	    if ($d != $day)
	    {
		// if date changed, output it
		$fmt = 'D j M';
		$y = date('Y', strtotime($ex));
		// if year not this year, output it every line
		if ($year != $y)
		    $fmt .= ' Y';
		$fmt .= ': ';
		if ($day != '')
		    echo "<br>"; // start a new day
		echo date($fmt, strtotime($ex));
	    	$day = $d;
		$comma = '';
	    }
	    $t = date('g:ia', strtotime($ex));

	    echo  "$comma$t";
	    $comma = ', ';
	}
	if ($day)
	    echo "<br>\n";
    	else
	    echo "No sessions yet.<br>\n"; // prefer "... for Movie Title"
    }


    // save a list of unix time sessions in the database
    // we are passed an array of sessions
    function session_save($id, $sessions)
    {
	$sesstab = $this->sesstab;
	$debug = ! empty($this->debug);
	if ($debug) { $i = 0; $id = 999; }
	if (empty($sessions) || count($sessions) == 0)
	    return '[ierr: mt_sess7]'; // imposs error

	// read in existing times from sessions table
	global $wpdb;
	$id = preg_replace('/[^0-9]+/', '', $id);
	$existing = array();
	if (! $debug)
	{
	    $existing = $wpdb->get_col("SELECT session_datetime FROM $sesstab "
		. "WHERE movie_id = '$id'");
	}

	$already_count = 0; // already had at least one session defined
	$saved = 0;

	// check each new time against existing sessions and skip if exists
	foreach ($sessions as $ss)
	{
	    $already_had = 0;
	    foreach ($existing as $ex)
	    {
		if ($ss == strtotime($ex))
		{
		    $already_had = 1;
		    break;
		}
	    }
	    if ($already_had)
	    {
		// have this session, don't save
		$already_count++;
		continue;
	    }

	    $values = array();
	    $values['movie_id'] = $id;
	    $values['session_datetime'] = date(DATE_ATOM, $ss);

	    if ($debug)
	    {
		$i++;
		echo "SAVED-$i: ".$values['session_datetime']."<br>\n";
	    }
	    else
	    {
		$wpdb->insert($sesstab, $values);
		// $sessid = $wpdb->insert_id;
	    }
	    $saved++;
	}

	$msg = '';
	$s = ($saved > 1) ? 's' : '';
	if ($already_count)
	{
	    if ($saved == 0)
	    {
		if ($already_count > 1)
		    return "Error: All those sessions already existed";
		return "Error: That session already existed";
	    }

	    // if there were some sessions already existing, we still attempted
	    // to create any that weren't there yet
	    $as = ($already_count > 1) ? 's' : '';
	    $msg = "Warning: $already_count session$as already existed; ";
	    // Fall through ...
	}
	$msg .= "Created $saved session$s successfully";
	return $msg;
    }

};


$WP_Cinema_Plugin_Movie_Admin = new WP_Cinema_Plugin_Movie_Admin();


// ==== WARNING WARNING =====================================================
//
// Ajax image upload stuff is defined globally here which is somewhat of a
// mess; will be encapsulated into class-fields.php shortly!		ZZ
//
// ==== WARNING WARNING =====================================================

// Ajax image upload initialize/actions/styles/scripts
function wpcinema_plu_admin_enqueue()
{
    // if(!($condition_to_check_your_page))// adjust this if-condition according to your theme/plugin entry
       // return;

    wp_enqueue_script('plupload-all');
 
    wp_register_script('myplupload', WPCIN_URL.'/inc/myplupload.js',
	array('jquery'));
    wp_enqueue_script('myplupload');
 
    wp_register_style('myplupload', WPCIN_URL.'/inc/myplupload.css');
    wp_enqueue_style('myplupload');
}
add_action('admin_enqueue_scripts', 'wpcinema_plu_admin_enqueue');


// Ajax image upload settings
function wpcinema_plupload_admin_head()
{
// place js config array for plupload
    $plupload_init = array(
        'runtimes' => 'html5,silverlight,flash,html4',
        'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
        'container' => 'plupload-upload-ui', // will be adjusted per uploader
        'drop_element' => 'drag-drop-area', // will be adjusted per uploader
        'file_data_name' => 'async-upload', // will be adjusted per uploader
        'multiple_queues' => true,
        'max_file_size' => wp_max_upload_size() . 'b',
        'url' => admin_url('admin-ajax.php'),
        'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
        'filters' => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
        'multipart' => true,
        'urlstream_upload' => true,
        'multi_selection' => false, // will be added per uploader
         // additional post data to send to our ajax hook
        'multipart_params' => array(
            '_ajax_nonce' => "", // will be added per uploader
            'action' => 'plupload_action', // the ajax action name
            'imgid' => 0 // will be added per uploader
        )
    );
?>
<script type="text/javascript">
    var base_plupload_config=<?php echo json_encode($plupload_init); ?>;
</script>
<?php
}
add_action("admin_head", "wpcinema_plupload_admin_head");




// Ajax image upload: called when an image is uploaded
// We upload to a temp area and when the form is submitted the temp
// path "/temp/" causes the image to overwrite any existing image
// we don't know the movie here so we can't do that now, even if it was right
// note: images are uploaded to our own temp area, not the usual uploads.
function wpcinema_g_plupload_action()
{
 
    // check ajax noonce
    $imgid = $_POST['imgid'];
    check_ajax_referer($imgid . 'pluploadan');
 
    // handle file upload
    // we upload to a temp dir till we know the movie name
    // give it any name at this stage, wp will make it unique
    add_filter('upload_dir', 'wpcinema_upload_to_wpcinema');
    $status = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'plupload_action'));
    remove_filter('upload_dir', 'wpcinema_upload_to_wpcinema');
 
    // send the uploaded file url in response
    // if image thumbnail displays after upload, this is all working
    // file_put_contents("IMAGEURL", print_r($status, 1)); //ZZ
    echo $status['url'];

    exit;
}

add_action('wp_ajax_plupload_action', "wpcinema_g_plupload_action");




// added as a filter before we allow upload so we can ensure uploads go to
// our temp folder in the uploads area.
// We use the presence of the /temp/ string in the submit field to show
// a new movie has been uploaded
function wpcinema_upload_to_wpcinema($upload)
{
    // print_r($upload);
    // $upload['subdir']	= '/sub-dir-to-use' . $upload['subdir'];
    $upload['subdir']	= '/wp-cinema/temp';
    $upload['path']	= $upload['basedir'] . $upload['subdir'];
    $upload['url']	= $upload['baseurl'] . $upload['subdir'];
    return $upload;
}

/*
// results from the above:
Array
(
    [path] => C:\Users\Brian\Dropbox\wdgf\wp-yco\wordpress/wp-content/uploads/2012/06
    [url] => http://wci.local/wp-content/uploads/2012/06
    [subdir] => /2012/06
    [basedir] => C:\Users\Brian\Dropbox\wdgf\wp-yco\wordpress/wp-content/uploads
    [baseurl] => http://wci.local/wp-content/uploads
    [error] => 
)
*/





// end
