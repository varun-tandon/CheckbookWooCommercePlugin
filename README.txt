=== WooCommerce Checkbook.io Payments ===
Contributors: checkbook
Tags: checkbook, woocommerce, checkbook.io,
Requires at least: 3.8
Tested up to: 4.9.6
Stable tag: trunk
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin enables Checkbook.io Payments through WooCommerce.

== Description ==

This plugin allows you to accept digital checks through Checkbook.io as payments through WooCommerce.

Simply install the plugin, configure the settings to fit your needs, and Checkbook.io will appear as a payment option in your WooCommerce cart.

== Installation ==

First you will need to setup your Checkbook.io account and receive your API credentials.

1. Navigate to [checkbook.io](https://checkbook.io/ "Checkbook.io") and create an account if you have not already. If you have, login to your account.
2. From your account dashboard, go to Settings > Developer. On this page you can see the API credentials necessary for plugin installation.

Plugin Installation:

1. Upload the plugin files to the `/wp-content/plugins/checkbook-io-payment` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Configure screen to configure the plugin settings with your API Secret Key and Client ID.
4. On the Configure screen, a default value for the Redirect URL will be filled in. Set this link as the Callback URL on the Checkbook Developer page.

== Frequently Asked Questions ==

= Why do I receive an error that I cannot send checks to myself? =

While testing the plugin, create a separate Checkbook.io account to make payments from. You cannot send a check to your account from your own account.

= I am facing another issue. =

Please contact our support at [support@checkbook.io](mailto://support@checkbook.io "Checkbook Support").

== Screenshots ==

1. Example Checkbook.io Payment Gateway
2. Example Checkbook.io Authentication Screen

== Changelog ==

= 0.0.2 =
* Initial upload to Wordpress.org

== Upgrade Notice ==

= 0.0.2 =
* Most recent version
