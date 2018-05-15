=== PacPayments for WooCommerce ===
Contributors: nmarley
Tags: PAC, PAC wordpress plugin, PAC plugin, PAC payments, accept PAC, PAC, pay-with-PAC
Requires at least: 4.4
Tested up to: 4.4
Stable tag: release
License: MIT
License URI: https://opensource.org/licenses/MIT

== Description ==

PacPayments for WooCommerce is a Wordpress plugin that enables WooCommerce merchants to accept PAC directly, without the need for a third-party payment processor.

= Benefits =

* Accept PAC payments directly to your Electrum-PAC wallet.
* No payment processors or fees.
* Recycles addresses from expired, unpaid orders

== Installation ==

1. Install WooCommerce and configure your store.
2. Install and activate "PacPayments for WooCommerce" just like any other Wordpress plugin.
3. Download and install Electrum-PAC wallet from here: https://wallets.paccoin.net/
4. Run the Electrum-PAC program and setup your wallet for the first time.
5. Find your Electrum-PAC wallet's Extended Public Key ("xpub") by navigating to:
      Wallet -> Master Public Key
6. Within your site's Wordpress admin panel, navigate to:
       WooCommerce -> Settings -> Checkout -> PAC
7. Paste your xpub key from step #5 into the proper field and press [Save changes]
8. If you see the message stating "PAC payment gateway is operational.", then your store is ready to accept payments in PAC.
9. It is **HIGHLY RECOMMEND** that you set up hard-cron for your WP/WooCommerce site and schedule it to run every minute.
10. We also highly caution against using shared hosting, such as HostGator. The benefits of using a VPN service and setting up your own virtual server are well worth the $5-10/month cost.

== Frequently Asked Questions ==

None yet, but we'll answer what we predict to be FAQs:

= Is this secure? =

Short answer, yes.

Because this plugin only accepts [BIP32 **public** keys](https://github.com/bitcoin/bips/blob/master/bip-0032.mediawiki "BIP0032") to generate addresses, your PAC can never be stolen (because the private keys won't ever exist on your server). If private keys are entered, they are detected and marked as invalid.

Read the "external dependencies" question for the longer answer.

= Does this support InstantX transactions? =

We do fully intend do support InstantX transactions in the future, as that's one of the most attractive features available with PAC. However, at the moment, the priority is getting this into the hands of as many online businesses as possible who want to start accepting PAC. It will take a bit of time and effort to get InstantX receive support built into the plugin.

= Does this support Darksend? =

As long as the funds get sent to the customer address, yes. It aggregates payments from multiple transactions, and only marks an order as paid (or overpaid) once the address balance meets (or exceeds) the order total.

= Does this support refunds? =

Nope. The plugin has no access to spend any funds whatsoever. The site owner is responsible for any customer refunds. They will have to be sent from a wallet outside of the scope of this plugin. (*Ahem*, the same Electrum wallet used to setup the gateway?)

= Are there any external dependencies? =

Yes. One is mandatory and one is optional.

The *mandatory dependency* is an up-to-date Insight-API server, accessible over the network by your server. You can specify your own instance in the gateway settings, or use the default server, which is hosted by the author of this plugin. This is required in order to know if payment has been sent to the addresses given to your customers.

In order to remove this third-party dependencies, you can host your own version of Insight-API and use that URL. If you host it on the same server as WordPress, you can use http://localhost:&lt;port&gt;/ as the URL and use a firewall to prevent anyone else from accessing your Insight-API instance. This should reduce network latency (obviously) and will remove your dependency on a trusted third party.

The *optional dependency* is an exchange rate API server, which is currently served by me, the author of this plugin. **In order to disable this, simply denominate your store in PAC.**

In the future, this will either be delegated to something like coinmarketcap (not ideal), or an open-source webservice URL which can be specified by the admin, just like the Insight-API service described above.

== Screenshots ==

1. Checkout with option for PAC payment.
2. Order received screen, including QR code of PAC address and payment amount.
3. PAC Gateway settings screen.

== Remove plugin ==

1. Deactivate plugin through the 'Plugins' menu in WordPress
2. Delete plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.0.5 =

* Use Insight-API provided by PAC

= 0.0.4 =

* Bugfix: Account for differences in sync-status output of Insight-API.

= 0.0.3 =

* Allow for older versions of PHP.

= 0.0.2 =

* bugfix: Warn if required PHP extensions not enabled.

= 0.0.1 =

* New
