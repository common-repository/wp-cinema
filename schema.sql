#==============================================================================
#  WP Cinema Database Version 100
#
#  This file is used to auto-create and auto-update our private mysql tables.
#
#  If you change wpcinema_movies columns you need to change movie_fields.php
#  as well.
#
#  This file is pre-processed to adjust the 'wp_' prefix and remove comments.
#
#  See our db_upgrade() code as well for any version specific data updates,
#  as opposed to the schema updates which are handled automatically.
#
#  Note that uninstall currently removes all these tables to ensure clean state.
#
#
#==============================================================================
CREATE TABLE wp_wpcinema_movies (
  id int(11) NOT NULL AUTO_INCREMENT,
  shortid varchar(32) NOT NULL,
  title varchar(128) NOT NULL,
  releaseyear year(4) DEFAULT NULL,
  description text,
  rating varchar(10) NOT NULL DEFAULT '',
  cast tinytext,
  runtime int(11) DEFAULT NULL,
  image varchar(64) DEFAULT NULL,
  previewdate date DEFAULT NULL,
  startdate date DEFAULT NULL,
  enddate date DEFAULT NULL,
  nftdate date DEFAULT NULL,
  officialurl text,
  booking_code int(10) DEFAULT NULL,
  displayorder int(10) NOT NULL DEFAULT '999',
  hideonmain int(1) NOT NULL DEFAULT '0',
  hidestart int(1) NOT NULL DEFAULT '0',
  forcedisplay int(1) NOT NULL DEFAULT '0',
  leavetitle int(1) NOT NULL DEFAULT '0',
  lastupdated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY `shortid` (`shortid`),
  KEY `lastupdated` (`lastupdated`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



CREATE TABLE wp_wpcinema_sessions (
  id int(10) unsigned NOT NULL auto_increment,
  movie_id varchar(32) NOT NULL default '',
  session_datetime datetime NOT NULL default '0000-00-00 00:00:00',
  venue_number int(10) NOT NULL default '0',
  session_number int(10) NOT NULL default '0',
  PRIMARY KEY  (movie_id,session_datetime),
  KEY id (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

# end
