=== Website Carbon ===
Contributors: beleaf, wholegraindigital, josh-stopper
Tags: carbon, emissions, measure, test, performance
Requires at least: 4.6
Requires PHP: 7.1
Tested up to: 6.1
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Every web page view generates carbon emissions. The website carbon plugin monitors your site and lets you know what the emissions are.

== Description ==

The internet consumes a lot of electricity. 416.2TWh per year to be precise. To give you some perspective, that’s more than the entire United Kingdom.

From data centres to transmission networks to the devices that we hold in our hands, it is all consuming electricity, and in turn producing carbon emissions.

Only by measuring the emissions of our web pages can we be informed about the impact that they are having. This plugin, powered by the Website Carbon project, allows for measuring and reporting on the carbon emissions of your site, so you to can understand the impact, and ultimately, reduce the emissions.

== Installation ==

Upload the Website Carbon plugin to your site and activate it. You can then measure the emissions of your site.

== Frequently Asked Questions ==

= Can I measure the emissions for my site all at once? =

Yes, browse to the Website Carbon page under the tools menu. Once there, click the "test all pages" button.

= Can I export the results for my site? =

Yes, browse to the Website Carbon page under the tools menu. Once there, click the "Download" button.

= How does the Website Carbon plugin measure carbon emissions =

The Website Carbon plugin integrates with the Website Carbon API developed by Wholegrain Digital.

= How does the Website Carbon API measure carbon emissions? =

The Website Carbon API makes a request for the page it is measuring and calculates the transfer size of the page. It then checks whether the site is running on renewable energy. Finally, it uses those two details to calculate the emissions. You can learn more about it at https://www.websitecarbon.com/how-does-it-work/

= How do I reduce the carbon emissions of my site =

There are many steps that can be taken to reduce the carbon emissions of your site, conveniently, these mostly coincide with improving the performance of your site to.

The biggest impact that can normally be had is optimising the images used on your site, to be in the most efficient format, and be prepared as close to the displayed size as possible.

Ultimately, anything that reduces the size of your site, will also reduce the carbon emissions.

= Why does measuring the emissions take so long =

Every page is given 30 seconds to complete a test as this is the maximum amount of time the Website Carbon API allows for a test to complete. Therefore, the Website Carbon plugin allows for just a little more time than this to account for delays.

= Why does the test say "Sorry, something went wrong"? =

Sometimes errors occur, and simply testing again will work. But if the error message shows several times, it could be for one of a few reasons.

1. The page you are trying to test is not available to Google Page Speed Insights.

2. The page you are trying to test takes longer than 30 seconds for Google Page Speed Insights to load.

3. Something unknown occurred. In this case were not sure why, but get in touch and let us know and we will try and sort it out if its on our side.

= How does the plugin check if my site is hosted on renewable energy? =

Your domain name is checked against the Green Web Foundation API, which reports on whether or not the data center your site is hosted in is powered by renewable energy.

Please note: sites that have DNS hosted with Cloudflare, and have requests proxied can not be verified as hosted on renewable energy as Cloudflare masks the true IP address of the site.

== Changelog ==

= 1.1.3 =
* Fix: WordPress compatability check and house cleaning
* Feat: Add grams to unit to admin view of posts

= 1.1.2 =
* Fix text domain for translation
* Update stable build directory

= 1.1.1 =
* Fix: Update related media files and documentation

= 1.1.0 =
* Feature: Add WordPress dashboard best and worst boxes
* Feature: Support ordering posts in query
* Fix: Shorten administration area terminology
* Fix: Hide "test required pages" button if there are no posts to measure
* Fix: Fix error in generating reports

= 1.0.0 =
* Initial release of Website Carbon plugin

== Upgrade Notice ==

= 1.0.0 =
* Initial release of Website Carbon plugin
