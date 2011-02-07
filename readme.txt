=== One Sport - Event Calendar ===
Contributors: http://www.onesportevent.com/about-us
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JKKRLSSDU7KY6
Tags: event calendar, event, calendar, upcoming events, sidebar, events, sport, date, time, event scheduling, promote event, displaying events, event widget, events wordpress, simple events, widget, event page, event post, club, ajax
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 2.8.0

OneSport Event Calendar gives you an instant event calendar with popular events.  Can also be used to list and promote certain types of club-only events

== Description ==

Easily get event listings for sporting events and athletic or multisport type races (e.g. half marathons, swimming, cycling, orienteering, kayaking, ironman and triathlon etc) from your local region.

First see http://www.onesportevent.com/events or http://www.wmc.org.nz/ (events tab) for a couple of live examples.  If you'd like this functionality too then install the plug-in.  

Creates instant content for your web visitors to browse so they spend more time on your website and are more likely to come back.

The event calendars default style is defined by css, and you can use your own stylsheet of course; post a forum message on www.onesportevent.com if you need help or if you have improvement suggestions.

Events come from a free shared database which you can add and edit, and the plug-in allows you to specify your own CSS so you can have full control over the styling, branding and functionality.  The administration options allows you to customise which events are displayed and to enable/disable different user filtering functionality.

Can also be used to list your members club-only events **if** you have an event that is one of the activities in the FAQ section.  It's not intended as a full on event management system!  But the nice thing is you can optionally promote your club events for free on an entire network of websites so more people are likely to see your club event and come along.

Features are

* Easy and instantly available.  Install the plug-in and events appear on your website instantly
* Flexible - configure which areas, event types and activities you want to see
* Always updated - as event organizers have one place to maintain events for multiple websites
* Event details - also drill through to specific event details, still on your website!
* Multiple filtering options - onroad, offroad, cycling, running, triathlon, you name it!
* Valid XHTML - Valid CSS based output works with all web browsers
* Club events - promote your club events for free on multiple web sites
* No data charges - content is served directly from the shared web server to your customer
* High performance - your website renders first and multiple connections allow parallel downloads
* Internet, MSN and telephone support, just drop me a line for my personal MSN
* Map integration - Coming soon! - drill through to see an events actual course route on the map

Information on events plug-in is now available from my website - 
http://www.onesportevent.com/get-free-event-calendar-widget-on-your-website/

Same goes for my mapping plug-in, which is very cool!
http://wordpress.org/extend/plugins/one-sport-route-mapper/

== Installation ==

1. Upload the entire 'onesportevent' directory to `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. From Settings, choose One Sport Event, then choose 'create new event page' and configure the settings how you want them.  Be sure to get an API get also, the link is on that page.
4. Get an API key from http://www.onesportevent.com/get-widget-key

== Frequently Asked Questions ==

= How to make this work with my theme? = 
Obviously I can't create a version for all themes out of the box, but I will create some different theme options.  Once you have it installed, Email a link to your page with the event installed and your theme, and I'll look at it for you.

= How do events get into the database? = 

Event organisers and clubs maintain the list of events in the shared database from http://www.onesportevent.com/promote-event.  If you want to add your own public or private event you can (although the adding event functionality is designed for event organisers and clubs and is not a full on event management system!).  None the less, and you have lot of flexibility including a full editor allowing you to use bolding, tables, layout, colours and pictures or maps in your event descriptions.

= What about my clubs events? = 

It can also be used for club-only race calendars so your members know information about your events - the best thing for clubs is it allow clubs to promote their events on other sites sharing the database, and thus gain more attention.

= Is there advertising in the plug in? = 

Nope.  But donations are welcomed.. encouraged even!

= Why do I need an API key? = 

Initially I didn't have one, however I realised I then have no way of stopping a website if they are abusing the system in some way, or leeching more bandwidth than my mortgate can afford!

= What activities are currently supported? =

* Running
* Swimming
* Walking
* Mountain Biking
* Kayaking
* Rowing
* Orienteering
* Cycling

= Place to get your own API key is here =
http://www.onesportevent.com/get-widget-key

= All API Documentation is now maintained online =
http://www.onesportevent.com/event-api-documentation/

= Terms and conditions are here =
http://www.onesportevent.com/api-terms-and-conditions/

= Features about the event widget are here =
http://www.onesportevent.com/get-free-event-calendar-widget-on-your-website/

= Calendar download page for HTML and CMS's is here =
http://www.onesportevent.com/event-calendar-download/

== Changelog ==
= Version 2.8 =
Complete overall of the styling and layout system.  Now has automatic flexible layout so it fits in most themese and I've included some extra options instant styling of key elements (you can still define your own look and feel completly by using css)

= Version 2.7 =
Added new date filter, fixed bug where ampersands in event description not displayed correctly, added new Aquathlon event type, added year to date display

= Version 2.6 =
Improved API performance.

= Version 2.5 =
As identified by Will Chapman (thanks), didn't work if your installations didn't use the default wp_ prefix for tables.  Fixed up errors in linking on the admin screen, and installation problems.  Added cleanup of settings on uninstall.  Improved shared stylesheet.

= Version 2.0 =
Realised most people were stuggling to get version 1 working as you had to configure the an HTML placeholder in the right place on your site, and apologies, I didn't document how to do that well ;-)

Decided to automate the process so it is simple, let me know if your experience is anything less than perfect.

* Removed need to manually configure placeholders
* Add "create new event page" to automatically create a default page with your events
* Allow multiple event pages to be created
* Complely rebuilt onesportevent.com from ground up and improved documentation
* Improved performance of API several times, now very fast
* Adjusted API to allow more control over how events are displayed

= Version 1.0 =

* Created basic plug-in with configuration options.
* Pretty hard to use unless you know HTML - limited documentation but functional.

== Screenshots ==

1. Event List
2. Administration Screen

== Upgrade Notice ==
OneSport - Massive changes to allow fluid layout and better styling.