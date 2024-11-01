<?php
//============================================================================
//
//  WP-Cinema uninstall
//
//  Removes all traces of WP Cinema
//
//  We remove the following:
//   - our config (categories table, options entry)
//   - our transient tables (sessions)
//   - our permanent data (movies table)
//
//============================================================================

// If uninstall not called from WordPress, exit
if (! defined('WP_UNINSTALL_PLUGIN'))
    exit();

require "wp-cinema.php";  // for define()s

// Delete option from options table
delete_option(WPCIN_OPTIONS);

// remove any additional options and custom tables

global $wpdb;

// would be nicer for table names to come from the list in schema.sql
// are they uninstalling because they are going to Pro?
$wpdb->query("DROP TABLE {$wpdb->prefix}wpcinema_sessions");
$wpdb->query("DROP TABLE {$wpdb->prefix}wpcinema_movies");
$wpdb->query("DROP TABLE {$wpdb->prefix}wpcinema_categories");

// end
