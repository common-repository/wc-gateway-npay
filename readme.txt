=== WC Gateway nPay ===
Contributors: axisthemes, shivapoudel
Tags: woocommerce, npay
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds nPay as payment gateway in WooCommerce plugin.

== Description ==

= Add nPay gateway to WooCommerce =

This plugin adds nPay gateway to WooCommerce.

Please notice that [WooCommerce](http://wordpress.org/plugins/woocommerce/) must be installed and active.

= Introduction =

Add nPay as a payment method with SCT Cards support in your WooCommerce store.

[nPay](http://npay.com.np/) is a Nepali Digital Payment Portal developed by inficare. This means that if your store doesn't accept payment in NPR, you really do not need this plugin!!!

The plugin WooCommerce nPay was developed without any incentive or nPay Company. None of the developers of this plugin have ties to any of these two companies.

= Installation =

Check out our installation guide and configuration of WC Gateway nPay tab [Installation](http://wordpress.org/extend/plugins/wc-gateway-npay/installation/).

= Questions? =

You can answer your questions using:

* Our Session [FAQ](http://wordpress.org/extend/plugins/wc-gateway-npay/faq/).
* Creating a topic in the [WordPress support forum](http://wordpress.org/support/plugin/wc-gateway-npay) (English only).

= Contribute =

You can contribute to the source code in our [GitHub](https://github.com/axisthemes/wc-gateway-npay/) page.

== Installation ==

= Minimum Requirements =

* WordPress 4.0 or greater.
* WooCommerce 2.3 or greater.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of woocommerce npay, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “WC Gateway nPay” and click Search Plugins. Once you’ve found our payment gateway plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our woocommerce npay plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* WordPress 4.0 or later.
* WooCommerce 2.3 or later.
* Merchant Credentials from nPay.

= nPay receives payments from which countries? =

At the moment the nPay receives payments only from Nepal.

Configure the plugin to receive payments only users who select Nepal in payment information during checkout.

= Where is the nPay payment option during checkout? =

You forgot to select the Nepal during registration at checkout. The nPay payment option works only with Nepal.

= The request was paid and got the status of "processing" and not as "complete", that is right? =

Yes, this is absolutely right and means that the plugin is working as it should.

All payment gateway in WooCommerce must change the order status to "processing" when the payment is confirmed and should never be changed alone to "complete" because the request should go only to the status "finished" after it has been delivered.

For downloadable products to WooCommerce default setting is to allow access only when the request has the status "completed", however in WooCommerce settings tab Products you can enable the option "Grant access to download the product after payment" and thus release download when the order status is as "processing."

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in our support forum or preferably on the [WooCommerce nPay GitHub repository](https://github.com/axisthemes/wc-gateway-npay/issues).

= WooCommerce nPay is awesome! Can I contribute? =

Yes you can! Join in on our [GitHub repository](http://github.com/axisthemes/wc-gateway-npay/) :)

== Screenshots ==

1. Settings page.
2. Checkout page.

== Changelog ==

= 1.0.0 =
* First stable release.

== Upgrade Notice ==

= 1.0.0 =
1.0.0 is a major update so it is important that you make backups, and ensure themes and extensions are 1.0 compatible.
