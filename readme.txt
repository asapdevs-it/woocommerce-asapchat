=== AsapChat: AI Chat and Product Search ===
Contributors: asapcommerce
Tags: live chat, ai chatbot, product search, woocommerce, customer support
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your site to AsapChat in one click: AI chat that answers customers and a fast product search for your store.

== Description ==

AsapChat adds two things to your WordPress site:

* **AI chat** that answers customer questions using your own knowledge base, products and order statuses, and hands the conversation to a human when needed.
* **AsapSearch**, a fast, typo-tolerant product search for WooCommerce stores.

This plugin is the official connector. It links your site to your AsapChat account and adds the AsapChat scripts to your pages. No theme editing, no code to paste.

**How it works**

1. Install and activate the plugin.
2. Go to Settings > AsapChat and click "Connect to AsapChat".
3. Log in, or create a free account and finish the short setup wizard. You are sent back to WordPress automatically.
4. The chat appears on your site.

**What you can set in the plugin**

* **Widgets**: turn the chat, the product search and "add to cart from search" on and off with separate switches.
* **Look**: assistant name, main color, position (bottom right or bottom left) and two greeting messages. These are the same settings as in the AsapChat panel, so a change in one place shows up in the other.
* **Where to show**: delay the chat by 0 to 60 seconds and hide the widgets on selected pages, selected products or any address that contains a given text.
* **Status**: see whether the chat and the search have actually been seen on your site.
* **Orders and products**: see whether the assistant has access to your products and orders, and connect your WooCommerce store in one click from the AsapChat panel.

**What you need**

A free AsapChat account. The Free plan includes 50 AI conversations per month. The knowledge base, conversations and team inbox are managed in the AsapChat panel.

**Good to know**

* Works with or without WooCommerce.
* Compatible with WooCommerce High-Performance Order Storage (HPOS).
* The plugin does not contact AsapChat until you click Connect.
* The plugin does not read or send your orders or products. AsapChat reads them through the WooCommerce REST API, only after you approve it.
* The settings screen loads no remote fonts or assets.
* Removing the plugin removes all its data from your database.

== Installation ==

1. Install the plugin from the Plugins screen and activate it.
2. Go to Settings > AsapChat.
3. Click "Connect to AsapChat", log in or sign up, and choose your shop.
4. Alternatively, paste an install key from your AsapChat panel under "I have an install key".
5. Optional: set the look, the delay and the excluded pages, and connect your orders and products.

== Frequently Asked Questions ==

= Do I need an AsapChat account? =

Yes. You can create a free one during the connection step. After the setup wizard you are sent back to WordPress automatically.

= Is it free? =

The plugin is free. AsapChat has a Free plan (50 AI conversations per month) and paid plans. See asapchat.io for current pricing.

= Does it work without WooCommerce? =

Yes. The chat works on any WordPress site. The product search needs a store with imported products.

= Where do I change the chat name, color, position and greetings? =

In Settings > AsapChat, section "Look", or in the AsapChat panel. Both places edit the same settings.

= Can I hide the chat on some pages? =

Yes. In "Where to show" pick pages or products, or enter a part of the address (for example `/checkout`). The chat and the search are not added there.

= Can the chat appear a few seconds after the page loads? =

Yes. Set the delay from 0 to 60 seconds in "Where to show".

= How does the assistant know my products and order statuses? =

Click "Connect in AsapChat" in the "Orders and products" section. In the AsapChat panel choose "Connect with WooCommerce" and approve the WooCommerce authorization screen. You do not copy any keys.

= The one-click connection did not work. How do I create API keys manually? =

Go to WooCommerce > Settings > Advanced > REST API, click "Add key", choose "Read" permissions, generate the key and paste the Consumer key and Consumer secret in the AsapChat panel.

= Why is the search switch inactive? =

AsapChat has not imported your products yet. Connect your store in the "Orders and products" section. The search turns on by itself after the import.

= What does "Add to cart from search" do? =

It shows an add to cart button in the search results. Turn it off if you want customers to open the product page first.

= The chat does not show up. What should I check? =

Check that the Chat switch is on, that the page is not on the exclusion list, and clear your cache plugin. The "Status" section tells you whether the chat has been seen on your site.

= I use a cache or optimization plugin. =

The scripts are marked to be skipped by JS optimization. If your plugin still combines them, exclude the AsapChat scripts from JS optimization.

= What happens when I disconnect or delete the plugin? =

The scripts are removed from your pages immediately. Deleting the plugin removes its options. Your AsapChat account and data stay intact.

= How is the plugin updated? =

Through WordPress.org, like any other plugin from the directory.

== External services ==

This plugin connects your site to AsapChat (https://asapchat.io), a service provided by AsapCommerce. It is required for the plugin to work: the chat and the product search are delivered by this service.

Nothing is sent before you click "Connect to AsapChat" or submit an install key.

1. Connecting (when you click Connect or submit an install key, and when you disconnect or delete the plugin): the plugin sends your site URL, a one-time code and a code verifier to https://panel.asapchat.io, and receives a connection token.
2. Configuration refresh (when you open the plugin settings page, and once a day): the plugin sends the connection token and your site URL to https://panel.asapchat.io/api/plugin/v1/config and receives the script addresses, their attributes, the look settings and the connection status.
3. Saving the look (when you click Save and a look field has changed): the plugin sends the connection token, your site URL and the changed values (assistant name, color, position, greetings) to https://panel.asapchat.io/api/plugin/v1/settings.
4. On your storefront (on every page where the widgets are enabled and not excluded): the visitor's browser loads the chat script and the search script from https://asapchat.io. These scripts communicate with https://panel.asapchat.io to deliver chat messages and search results. Messages typed by the visitor and search queries are sent to the service.

The plugin does not send your orders, products or WooCommerce API keys anywhere.

Terms of service: https://asapchat.io/regulamin
Privacy policy: https://asapchat.io/polityka-prywatnosci

== Screenshots ==

1. Not connected: one button to connect your site.
2. Connected: shop name, plan, status and switches for chat, search and add to cart.
3. Look and "Where to show": name, color, position, greetings, delay and exclusions.
4. Orders and products: connection status and WooCommerce API key instructions.
5. The AsapChat widget on a storefront.
6. AsapSearch results on a WooCommerce store.

== Changelog ==

= 1.0.0 =
* First release.

== Credits ==

Open Sans, SIL Open Font License 1.1, bundled in admin/fonts/.
