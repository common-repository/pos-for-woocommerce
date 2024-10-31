=== Pos Malaysia Plugin===
Contributors: posmalaysiaberhad
Donate link: https://www.pos.com.my/
Tags: Pos4you,PosMalaysia
Requires at least: 5.1
Tested up to: 5.9
Requires PHP: 7.4
Stable tag: 1.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The Pos Malaysia WooCommerce extension helps manage  your business' shipping needs! Create shipping e-consignment notes, manage existing shipment, track and trace all your parcels, and more! 

== Description ==

Features include:
Registration
Register an account with Pos Malaysia before proceed to perform shipment. Upon registration complete, keys will be sent to your registered email.

View shipment history
View past shipment information, reprint an e-consignment note and commercial invoice, find a tracking number, or update a shipment’s information easily. 

Request pick-ups 
Request for parcel pick-ups, so you don’t need to leave your premise to post your parcels.   

Request Drop-off
Send parcel Data to Pos before Drop off at Pos Laju Counter, so you don’t take time when dropping of your item at the counter.   

Track and Trace your parcel status
Find out where your parcel is, at this very moment.  

Note: To use this plugin, you will need a Pos Malaysia Corporate Account number in order to enable integration via the app. Kindly contact your Key Account Managers for your API Key. POS Malaysia plugin is supporting 3rd Party API: https://ecom-pi-svc.pos.com.my. which share merchants order detail like billing and shipping address with POS malaysia for product delivery.
Following are the 3rd Party apis used in plugin:
/v2/account -- to get the profile detail from ecom-pi-svc
/v2/shop/register -- register the woocmmerce store with ecom-pi-svc
/v2/pos/validation -- validate the woocmmerce store with ecom-pi-svc
/v2/pickup -- generate request for the parcel pickup 
/v2/dropoff -- cancel the request for the parcel pickup 
/v2/connote-bulkv2 -- generate conginment for single or multi selected orders
/v2/auth -- generate token and authorize
/v2/tracking?connoteNo  -- track the status of orders

https://tracking.pos.com.my/tracking/tracking/connote -- update the statuses of transistion of orders


== Frequently Asked Questions ==

= Whoa, what's with all these plugins? =
The Pos Malaysia Shopify App is your best friend in managing all your business' shipping needs! We'll help you create shipping e-consignment notes, track and trace all your parcels.

== Screenshots ==

1. Registration
2. Shipping Setting
3. Order Management
4. Print Label
5. Sample of Consignment Note
6. Pickup Request
7. Pos Laju DropOff

== Changelog == 

= 1.0.6 =
* Fix incorrect weight.

= 1.0.7 =
* Pull POS order status via API.

= 1.0.8 =
* Fix syntax issue

= 1.1.0 =
* Subscribe to webhook to receive POS order status.

=1.1.1 =
* Remove pickup and drop off requests
 
=1.1.2 =
* Merge Congignment notes and enables immidiate pickuo request automatically

=1.1.3 =
* Added button in shop order page to re generate consignment note for Already "Picked up Scheduled" orders.

=1.1.4 =
* Changed the behaviour of settings under woocommece shipping and introduced new order statuses.

=1.1.5 =
* has option to generate connote and request for pickup automatically as soon as order is created.

== Upgrade Notice == 

* To Be Inform after upgrade
