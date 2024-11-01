=== WP Cinema ===

Contributors: Brian Coogan
Plugin Name: WP Cinema
Tags: cinema, movie, tickets, ticketing, venue, booking, film festival, live theatre, sales, theater, programme, program, movie website, cinema website, entertainment, showtimes, google showtimes, film festival website, schedule, festival
Requires at least: 3.4
Author: Brian Coogan
Author URI: http://www.wp-cinema.com/
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 0.6.1

WP Cinema is a movie display, management and ticketing system intended for
cinema websites.


== Description ==

WP Cinema is a movie display and management system intended for cinema
websites. It is flexible, configurable, and powerful, and can be integrated
with your inhouse ticketing system.

= NOTE: WP Cinema is in beta at the present; this is a functional beta release for your testing purposes =

It provides the following functionality:

* Displays movies on home page with session times
* Clickable session times can take you straight to booking links
* Framework supporting integration with in house ticketing systems
* Manual movie and session entry if your system is not supported
* Easy movie categorization for "baby friendly" or "premium" sessions
* Commercial options and full support available

The basic version of WP Cinema provided here works for 1-3 screens
and is sufficient to run a small cinema or film festival website.

We're excited to be able to offer a starter product which helps smaller
cinemas, film festivals and occasional venues to get a functional cinema
website going at virtually no cost.


= Display of movies =

WP Cinema provides a simple framework for displaying today's session times on
the home page.  The home page format displays a thumbnail for each movie, along
with a title and session times.  The session times are clickable and lead to a
configurable booking link.

The user can also click on a movie which then displays in it's own page, with a
description of the movie (which you can edit).  The commercial version provides
default images and description, in the lite version you enter everything
yourself.

There are several different formats implemented through the use of shortcodes;
at the moment these are:

* A daily programme with ability to switch to tomorrow via week day links
[due for release July 2012]
* A summary list of all movies and the entire entered schedule
* Subset (category) pages for special session categories (see below)


= Clickable session times for booking =

WP Cinema makes it easy for customers to book; they simply click on the
session time which takes them straight to your booking page.

Booking pages/links can be provided by integration modules, or you can
enter your own booking links on the setup page.

Payment for bookings via PayPal will be included in the free version
shortly.


= Integration with in-house ticketing systems =

Integration provides the following features:

* pulls movie details from the ticketing system definitions
* pulls session times and availabilities
* easily categorizable sessions (eg baby-friendly, premium class, film festival, seniors with coffee sessions)
* easily refreshed manually or automatically
* integrates with any booking system provided

The free version does not provide any free integration modules; though it is
completely usable without them via the manual entry system.

WP Cinema integrates with a number of in-house ticketing systems through
additional licencing modules.  This provides fully automated update of
session times on an hourly (or configurable) basis, as well as
(in the near future) flagging of sessions that are full or nearly full.

If your ticketing system provides a site or link for booking,
the session links can be set to go direct to the appropriate site.

Full support for internet booking is currently under development; when
this is done you'll be able to sell tickets for most systems direct from
your site.  Currently you can only sell tickets through the configurable
third party link system as just mentioned.


= Manual movie and session entry =

As mentioned previously, the free version allows you to enter all movie
descriptions, images and session information yourself.  Provided you
enter sufficient days ahead (we recommend at least 3-5 days) the times
will rotate on the site to show the relevant times for today.

Please see the screenshots page for more information, or view our short
video.

We recommend use of the automated synchronization module for your
ticketing system to remove the need for nearly all manual entry. Note
that if no automated module for your system is available, you can choose
to use the manual system until it is ready (see FAQ).

 
= Easy movie categorization =

You can set up special programme pages for specialized movie categories.

The system provides easy and powerful categorization for special
sessions, such as "baby friendly", or premium/luxury/gold sessions, or
special showings or festivals.

This is configurable and can be controlled:

* automatically through use of a prefix in the movie title
* automatically through use of a special text flag in the description
(which can be invisible)
* automatically through detecting a special price code on the session
* manually through editing the session and assigning a type
* this is all setup through the admin options pages
* one movie can be in multiple categories

Once you have the categories setup, you can use them in the following
ways:

* have a special prefix or suffix after the session time, with a link,
eg "baby" or "luxury"
* have different ticketing links for categorized sessions
* remove categorized sessions from the home page
* list category sessions on a special page, eg have a dedicated page for
baby-friendly sessions, or a science fiction festival, or your weekly seniors and coffee discount sessions

This allows you to market to special interest groups, or to run special
"festivals" throughout the year, with their own dedicated pages.


= Commercial options =

A more advanced commercial version extends the free version with plugins
and support services:

* email support
* phone support (even in US business hours!)
* setup support
* automated pull of session and movie details from your supported
in-house ticketing system
* customer loyalty interfacing
* multi-venue cinemas (ie in different cities)
* optimised and reliable hosting
* rapid defect fixes for supported sites

WP Cinema provides an excellent basis for cinema site development.
If you are a web developer wanting to use WP Cinema in your customer
site, we have wholesale pricing and extended developer support
available, including developer training (no charge at the moment) and site setup
services.


== Installation ==

= Usual install =

WP Cinema is usually installed via the WordPress plugin
installation system:

1. Login to WordPress as the site admin
1. Navigate to Plugins -> Add New and use the Search box to find WP Cinema
1. Click on "Install Now" next to WP Cinema (the only current match), accept the prompt
1. Activate the WP Cinema plugin through the link at bottom or the 'Plugins' menu in WordPress
1. Configure the plugin by going to the `WP Cinema` menu that appears in your admin menu

= Manual Install =

If, for some special reason, you need to follow manual installation steps, they are:

1. Upload the `wp-cinema` folder to the `/wp-content/plugins/` directory
1. Activate the WP Cinema plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the `WP Cinema` menu that appears in your admin menu

Once you have installed WP Cinema, please make time to go through the
setup options carefully.

If you are unfamiliar with WordPress, visit us at www.wp-cinema.com to
find out how our various installation and setup service options work.

= Removal =

In the unlikely event WP Cinema causes problems and you can't use the
deinstallation option, simply remove the /wp-content/plugins/wp-cinema folder and WordPress will deactivate WP Cinema.

WARNING! Be aware that removing WP Cinema will delete (permanently) the
contents of your movie and sessions tables.


== Frequently Asked Questions ==

= What are the limitations on the free version of WP Cinema? =

The free version supports a maximum of 1-3 screens in a cinema site.
Everything you need to have a fully functional website is provided.

We also support limited-duration film festivals without charge, no matter
how many screens or sessions you run.

The free version does not provide automated connections with in-house ticketing
systems or synchronisation of session times - you have to enter your times
manually.  As you'd expect, the plugin does rotate your sessions as entered
from day to day and you can enter a week at a time.

The free version also does not automate movie descriptions or images,
which are added automatically when a movie is first seen when you have the
commercial version.  You can enter these manually.


= How can you afford to release a functional system like this for free? =

We make our money from support and licencing for larger sites.  This
includes additional modules for the system which make it very powerful
and extensible.

We also work with developers who wish to use our products for their own
cinema sites.

Actually, we're rather thrilled to offer this "lite" version without charge to
smaller cinemas and to film festivals as we see it as a way of giving back to
the industry, and we hope it might help smaller locations and occasional venues
get started more easily.

Additionally, the free version allows you to evaluate and experiment with the
software without restriction.  Please note that we love to hear if there
are key features missing that would make a difference for your site -
please let us know.


= How do you support commercial web developers? =

We love to see other people developing cinema sites with this software,
either for free or commercially.

The licenced addons, which would be an integral part of a fully automated
implementation, are available under wholesale pricing arrangements to
developers.  However, if the site you are developing doesn't need any
licenced modules, you don't need to talk to us or pay us - enjoy using it!
And maybe send us a note to check out your work!

We also offer developer support:

* free support being the occasional web seminar
* free developer-only support pages and videos
* free support for licenced modules
* best efforts support for free modules
* chargeable 'site setup and configure' where we configure a
base movie setup for yourselves for testing/development and you then
build the theme, possibly using one of the newer gen themes such as Headway
or Woothemes Canvas.  This is a great place to start if you haven't done
much WordPress before.

If you are a customer (eg cinema owner) looking to get a cinema website
developed, we find it works best for you if we work with your preferred
local developer and assist them as we beleive this gives you a better
experience - faster service and a point of local contact in the same time
zone, with the same language and customs.  We're happy to talk this through
with you if that helps.


= What ticketing systems do you synchronize with? =

Currently, we provide interface modules for the following systems:

 * Venue Ticketing (highly recommended)
 * Showcase (under development)
 * Manual entry

With the exception of the Manual entry module, these interface modules are
available additional to the lite version and are licensed for a small monthly
fee, which includes support.

Additional interfaces can be developed at a nominal fee if your system is
not listed and the providers are able to give us the information we
need.

We recommend Venue Ticketing due to their excellent support
and the incredibly configurable and flexible system they provide
(see www.venueticketing.com.au).  Venue also provide an internet payment
gateway which works closely with WP Cinema.

Note that the system doesn't require a ticketing interface module; you
can enter all the session time and movie information yourself.
Sessions will automatically rotate on the site and you should ensure you
enter at least 2-3 days (and preferably a week) ahead to ensure your
listing in Google showtimes works well.

Please visit www.wp-cinema.com/ticketsystems for further information as
it becomes available.


= Who are the people behind WP Cinema? =

White Dog Green Frog, an Australian Web Development and web hosting
company, is behind WP Cinema.  WDGF has been developing Cinema sites
for 6 years supporting a small number of Australian sites.

One of the founders had a background in cinema technology fitouts, and we love
movies, so we decided to create a new way of building cinema sites
with active programme content.


= Why did you choose WordPress for WP Cinema? Why not XXX? =

We'd had two versions of our cinema management software - an old PHP version
(circa 2004), and a Joomla module (circa 2008).

The reasons we chose WordPress:

* a complete, powerful, yet easy to use CMS ready to use out of the box
* very regular WordPress core updates and feature additions (3 monthly compared to 12-18 months for Joomla and Drupal)
* quick security releases - within hours/days
* self-updating website and plugin code
* well documented systems for our developers to work with
* a flexible and powerful plugin and hook system
* 55,000,000 sites worldwide (est) using WordPress with nearly 20,000 plugins [April 2012]


= What plans do you have for other language support? =

We plan to do full customer-side internationalisation to allow support for
other languages, but it isn't included in the first version.  We anticipate it
will become available late in 2012 under current plans.

If you would like an internationalized version which supports customers
in your own language, please contact us to discuss - obviously the more
demand the higher we will prioritize internationalization!

There are no short term plans for admin internationalization; again, if
this would make a difference for yourselves please contact us.


= What is the roadmap for future WP Cinema development? =

WP Cinema is being actively developed and we are continuing to invest in
development for both the free and commercial platforms.

Some of our planned extensions and additional plugins:

* Paypal booking support (will be in free version as soon as we can)
* Flagging of full, or nearly full, sessions
* Optional display prioritization of categorized sessions/movies
* (full) Internet booking support
* Seat allocation and maps
* Internationalization (see above)
* Full multi-location support (cinemas at multiple locations on one+ site(s))

If there is a feature you would like to see in WP Cinema that is not currently
available, please let us know in detail what you are looking for.  While we
can't make promises, if we get a number of requests for the same feature it
will be prioritised highly.


= Do you work with other developers?  Or accept bugfixes? =

Yes and Yes!  We're always happy to collaborate with other open sourcers
and WP Cinema plugins will utilise the WordPress plugin system to make the
system extensible.  We are committed to retaining a GPL-licenced usable
version of WP Cinema well into the future, and updating as much as is
humanly possible.

Bug fixes are welcome - please include a clear summary of what you're
fixing - we've found Jing.com very helpful here - a short before-after
movie is usually worth a thousand words.  We also need the original file
before you edit it, as well as the fixed file or patch diffs.


== Screenshots ==

1. An example of a WP Cinema site - home page - click here for more
examples www.wp-cinema.com/samples - live sample site is www.suntheatre.com.au
2. The main WP Cinema page, showing search and the last 5 edited movies
3. The WP Cinema movie entry page.  Sessions are entered at the bottom, if they are being manually entered.
4. The WP Cinema options page (first pane)


== Changelog ==

* No changes since initial release (19 Jun 2012).

See also our website at www.wp-cinema.com/releasenotes

Note that the commercial components will sometimes have more frequent updates
than the free version.


== Other Notes ==

If you're a developer, please ask us for access to the developer only
resources.

There will be a series of short movies at www.wp-cinema.com/free-setup
which will take you through setup of the free version.  Some of this is
obvious but if you haven't done it before please watch them as there are
some invaluable tweaks you'll miss out on otherwise.


== Upgrade Notice ==

There are no active urgent upgrade notices.  In the event of an urgent need for upgrade, we will notify you through:

* our email list (if you subscribe!)
* the WordPress upgrade system (when you login to the admin area)
* a notice here
* a notice on our website

If an upgrade is offered through the WordPress update system, please
backup your system and upgrade quickly - don't just ignore the notice -
some upgrades contain important security fixes!


