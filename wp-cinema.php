<?php
/*
 * Plugin Name: WP Cinema
 * Plugin URI: http://plugins.wp-cinema.com/wp-cinema/
 * Description: The first integrated plugin for management of movie display and booking on cinema websites; fully integrates with booking systems.
 * Version: 0.6.1
 * Author: WP Cinema
 * Author URI: http://www.wp-cinema.com/
 * License: GPL2
 */

// Licence needs to go here

// need to refuse to run in MU - we haven't been checked for it


define('WPCIN_VERSION', '0.1.0');

define('WPCIN_PHP_VERSION', '5.2');
define('WPCIN_WP_VERSION', '3.3'); // min WordPress requirement

// Dont run if called directly
if (! function_exists('add_action'))
	die("Plugins cannot be called directly.");

if (! function_exists('admin_path')) {
    // maybe WP will wake up and add this one day ...
  function admin_path($s)
  {
      $admin = ABSPATH . "/wp-admin";
      if (empty($s))
	  return $admin;
      return "$admin/$s";
  }
}
else if (! file_exists(admin_path("plugins.php"))) wp_die("bad admin_path()");

// check installed modules
// check required plugins (shouldn't be any for core)'


define('WPCIN_PATH', dirname(__FILE__));
define('WPCIN_URL', plugins_url('', __FILE__));
define('WPCIN_FILE', plugin_basename(__FILE__));
define('WPCIN_INC', WPCIN_PATH . '/inc');
define('WPCIN_OPTIONS', 'wpcinema_combined');

date_default_timezone_set('Australia/Melbourne');// shd be an option TODO:


if (defined('WP_UNINSTALL_PLUGIN'))
{
    // avoid including admin/userland funcs, just here for defines...
}
elseif (is_admin())
{
    require_once(WPCIN_INC . '/movie.php');
    require_once(WPCIN_INC . '/settings.php');
}
else
{
    require_once(WPCIN_INC . '/views.php');
}




class WPCinema_main
{
    public $db_version = 108;  // database schema version
    public $options = NULL;

    function __construct()
    {
	register_activation_hook(__FILE__, array(&$this, 'activate'));
	register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));

	add_filter('plugin_action_links',
	    	array(&$this, 'add_settings_link'), 10, 2 );

	if (empty($this->options))
	    $this->options = get_option(WPCIN_OPTIONS);

	add_action('admin_bar_menu', array(&$this, 'change_toolbar'), 999);
	add_action('plugins_loaded', array(&$this, 'update_check'));

	//$this->add_shortcodes();
    }


    function activate()
    {
	// echo "<font color=red>Activating WP Cinema<br>\n";
	$this->versioncheck();
	$this->update_check();
	// uninstall is done by uninstall.php
    }


    function deactivate()
    {
	// echo "<font color=red>Deactivating WP Cinema<br>\n";
	// nothing to be done, currently
    }


    function change_toolbar($wp_toolbar)
    {
	if (! empty($this->options['no-manual-movie-create']))
	    return;
	$wp_toolbar->add_node(array(  
	    'id' => 'add_movie',  
	    'title' => 'Movie',  
	    'parent' => 'new-content',  
	    'href' => 
	      '/wp-admin/admin.php?page=wp-cinema%2Finc%2Fmovie.php&func=edit'
	));
    }


/******
    function add_shortcodes()
    {
	add_shortcode('wpcinema_test', array(&$this, 'shortcode_test'));
    }


    function shortcode_test($attr, $content)
    {
	$ret = 'TEST SHORTCODE<br>';
	if (! empty($attr))
	    $ret .= nl2br(print_r($attr, true));
	if (! empty($content))
	    $ret .= "<br><pre>CONTENT:\n$content\n</pre>\n";
	// print "value: " . $attr[''] . "<br>\n";
	return $ret;
    }
******/


    // want to move this into healthcheck, and call healthcheck from here
    function versioncheck()
    {
	if (version_compare(get_bloginfo('version'), WPCIN_WP_VERSION, '<'))
	{
	    // Deactivate ourself.  We should die more nicely. :)
	    deactivate_plugins(basename(__FILE__));
	    wp_die("WP Cinema needs at least WordPress " . WPCIN_WP_VERSION);
	}
	if (version_compare(phpversion(), WPCIN_PHP_VERSION,'<'))
	{
	    deactivate_plugins(basename(__FILE__));
	    wp_die("WP Cinema needs at least PHP " . WPCIN_PHP_VERSION);
	}

	
    }


    function update_check()
    {
	// We don't do MU at all, currently
	// Every plugin upgrade does deactivate/activate I think?
	// (we check db version every load, regardless)

	$this->db_upgrade();
    }
	    

    //  Upgrade the database if required
    //  This is a cheap check as options are cached by WP
    //
    function db_upgrade()
    {
	if (file_exists(WPCIN_PATH."/force_db_upgrade"))
	    delete_option(WPCIN_OPTIONS);

        global $wpdb;
	if (empty($this->options))
	    $this->options = get_option(WPCIN_OPTIONS);
	if (false === $this->options)
	{
	    $this->options = array();
	    $this->options['db_version'] = 0;
	}
	$installed_ver = $this->options['db_version'];

	if ($installed_ver == $this->db_version)
	    return;

	$sql = file_get_contents(WPCIN_PATH . "/schema.sql");
	if (empty($sql))
	    die("Could not read schema.sql file");

	// Remove comments and localize wp_ prefix
    	$sql = preg_replace('/^#.*\n/m', '', $sql);
	$sql = preg_replace(
		'/^(CREATE TABLE .*`)wp_/m',
		'\1' . $wpdb->prefix,
		$sql
	);

	// Auto-magically create/update mysql table structure
	require_once(admin_path('includes/upgrade.php'));
	// echo "<br>".nl2br($sql)."<br>\n"; //ZZZ
	dbDelta($sql);

	// Version specific data upgrades required, if any...
	// switch on installed version
	//
	$wpp = $wpdb->prefix . "wpcinema";
	/*
	if ($installed_ver < 101)
	    $wpdb->query("SELECT 1");
	if ($installed_ver < 102)
	    $wpdb->query("ALTER TABLE ${wpp}_movies DROP nft_date");
	if ($installed_ver < 107)
	    $wpdb->query("ALTER TABLE ${wpp}_movies CHANGE lastupdated "
		. "lastupdated TIMESTAMP on update CURRENT_TIMESTAMP "
		. "NOT NULL DEFAULT CURRENT_TIMESTAMP");
	* problem: we redo these each time!
	 */
	// ^ ^ changes required for new versions go here at bottom ^ ^

	// Save new DB version
	$this->options['db_version'] = $this->db_version;
	update_option(WPCIN_OPTIONS, $this->options);
    }

	
    // Add a link to the settings page to the plugins list
    // courtesy: Yoast
    //
    function add_settings_link($links, $file)
    {
	static $this_plugin;
	if (empty($this_plugin)) $this_plugin = 'wp-cinema/wp-cinema.php';
	if ($file == $this_plugin)
	{
	    $settings_link = '<a href="' . $this->plugin_options_url() . '">'
		. 'Settings' . '</a>';
	    array_unshift($links, $settings_link);
	}
	return $links;
    }


    function plugin_options_url()
    {
	$url = 'options-general.php?page=wpcinema_options';
	return admin_url($url);
    }





}


$WP_Cinema_main = new WPCinema_main();
    

// end


/*
 *  http://www.sitepoint.com/change-wordpress-33-toolbar/
 *  Adding add movie to admin toolbar
 *
add_action('admin_bar_menu', 'change_toolbar', 999);
function change_toolbar($wp_toolbar)
{
    $wp_toolbar->add_node(array(  
	'id' => 'add_movie',  
	'title' => 'New Movie',  
	'parent' => 'new-content',  
	'href' => 'blah'  
    ));
}
 */

