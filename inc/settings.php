<?php

//============================================================================
//
//  WP Cinema Options Handling
//
//  Our very own settings class
//
//  Could not use the settings API as it made it too hard to select a
//  subset of a larger options array, which we need to do as we display
//  subsets of our larger options array in tabs.
//  - as soon as we can work out how to make it work in the settings API,
//    we should start using it!
//  - the ability to merge in changed settings and/or work with a subset
//    will probably be required before we can do that
//
//  Features:
//  - all our settings go in one wp_options entry (row)
//  - supports multiple settings tabs under menu
//  - supports child plugins adding settings tabs via wpcinema_options hook
//  - options defined in an array ("dictionary") - types, prompts, validation
//  - "unlisted" settings in options (eg db_version) are not disturbed
//  
//
//============================================================================

require_once("class-fields.php");  // form management

class WPCinema_settings extends WPCinema_fields
{
	public $mynonce = 'wpcinema-option-update';
	public $options;		// options values from WP db
	public $dict = array();		// options tab/field specs


	function __construct($key, $dict)
	{
	    $this->key = $key;
	    $this->dict = $dict;

	    add_action('init', array(&$this, 'load_settings'));
	    add_action('plugins_loaded', array(&$this, 'get_tab_fields'));
	    add_action('admin_menu', array(&$this, 'add_admin_menus'));
	}


	function load_settings()
	{
	    $this->options = (array) get_option($this->key);
	}


	function get_tab_fields()
	{
	    // Get child plugin options
	    // each child calls add_options_panel() below to send options
	    do_action_ref_array('wpcinema_options', array(&$this));
	}


	//  Settings entry callback
	//
	//  The child plugin adds settings tabs this way:
	//    add_action('wpcinema_options', array(&$this, 'add_options'));
	//    function add_settings($b) { $b->add_options_panel($this->dict); }
	// 
	function add_options_panel($tab_fields)
	{
	    global  $WPCinema_settings_dict;

	    foreach ($tab_fields as $tab => $dict)
	    {
		$WPCinema_settings_dict[$tab] = $dict;
		// $this->dict[$tab] = $dict; // should we copy it?
	    }
	}

	// Set up defaults where needed
	// We look at the default in the dictionary, but if none given,
	// we choose a generic default based on type.
	// Needs to be called after all option dictionaries are loaded
	//
	function set_defaults()
	{
	    $changed = 0;
	    foreach ($this->dict as $tabname => $tabcontents)
	    {
		foreach ($tabcontents['fields'] as $fieldname => $field)
		{
		    if (isset($this->options[$fieldname]))
			continue;
		    $changed++;
		    if (isset($field['default']))
		    {
			// there's a specific default so we use that
			$this->options[$fieldname] = $field['default'];
			continue;
		    }
		    // no specific default, so we just use a generic default
		    switch ($field['type'])
		    {
		      case 'checkbox':
		      case 'integer':
			$this->options[$fieldname] = 0;
			break;
		      default:
			$this->options[$fieldname] = '';
			break;
		    }
		}
	    }
	    // only save if we changed something
	    if ($changed)
		update_option($this->key, $this->options);
	}


	function add_admin_menus()
	{
	    add_options_page(
		'WP Cinema Settings',	// Page title
		'WP Cinema',		// Menu entry
		'manage_options',	// capability
		'wpcinema_options',	// page
		array(&$this, 'render_options_page')
	    );
	}


	// Actually display the settings page here
	//
	function render_options_page()
	{
	    global  $WPCinema_settings_dict;

	    if (! current_user_can('manage_options'))
		wp_die("No permission.");

	    $this->set_defaults();

	    $tab = empty($_GET['tab']) ? 'main' : $_GET['tab'];
	    $dict = NULL;
	    if ($tab)
		$dict = $WPCinema_settings_dict[$tab];
	    ?>
	    <div class="wrap">
	    <?php
	    if (isset($_REQUEST['submit']))
	    {
		check_admin_referer($this->mynonce);
		$changed = 0;
		foreach ($dict['fields'] as $key => $field)
		{
		    // some very basic sanitisation
		    switch ($field['type'])
		    {
		      case 'checkbox':
			  $new = empty($_REQUEST[$key]) ? 0 : 1;
			  break;

		      case 'integer':
			  $new = preg_replace('/[^0-9]/', '', $new);
			  break;

		      default:
			  $new = $_REQUEST[$key];
			  break;
		    }
		    if (isset($this->options[$key])
			&& $new == $this->options[$key])
			    continue;
		    $changed++;
		    $this->options[$key] = $new;
		}
		if ($changed)
		{
		    update_option($this->key, $this->options);
		    // nice to display whether we succeeded/failed
		    echo "<br /><div id='message' class='updated'>"
			. "Options changes saved</div>";
		    // echo "<div id='message' class='error'>"
		    //   . "We had a problem saving your movie.</div>";
		}
		else
		{
		    echo "<br /><div id='message' class='updated'>"
			. "No changes made</div>";
		}
	    }
	    ?>
		<?php $this->display_tab_heads($WPCinema_settings_dict, $tab); ?>
		<form method="post">
		    <?php wp_nonce_field($this->mynonce); ?>
		    <?php echo $this->fields2form($this->options, $dict['fields']); ?>
		    <?php submit_button(); ?>
		</form>
	    </div>
	    <?php
	}


	// Display styled tab headings at top of page.
	//
	function display_tab_heads($globaldict, $current_tab)
	{
	    screen_icon();
	    echo '<h2 class="nav-tab-wrapper">';
	    // need to sort tabs by priority
	    foreach ($globaldict as $key => $tab)
	    {
		$active = $current_tab == $key ? 'nav-tab-active' : '';
		echo '<a class="nav-tab ' . $active . '" href="?page='
		    . 'wpcinema_options' . '&tab=' . $key
		    . '">' . $tab['tabhead'] . '</a>';	
	    }
	    echo '</h2>';
	}

	// does page name matter here? page=xxx ^ ^

};


require_once('settings-dict.php');

$settings_api_tabs_demo_plugin = new WPCinema_settings(
    WPCIN_OPTIONS,
    $WPCinema_settings_dict
);


// end
