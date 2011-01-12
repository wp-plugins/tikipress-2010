=== TikiPress ===

Contributors: DJPaul, Mychelle, valentinas, jghazally, GetShopped.org
Tags: e-commerce, shop, cart, goldcart, buddypress, Tickets, Event Manager
Version: 1.1
Tested up to: 3.03
Requires at least: WP-e-commerce 3.7.7, The latest version of buddypress custom post types (version 0.1.2.1), EventPress Version 0.1.2.2
Stable tag: 1.1

== Description ==

This plugin allows you to create events, tickets as products, attendees everything you need to organize events.
This version is in beta mode so please report any bugs or suggestions to the getshopped.org blog.

Note: This plugin requires the latest versions of each of these other plugins to run: WP-e-Commerce, EventPress, BuddyPress, BuddyPress Custom post types.


== Installation ==

Note: This plugin requires the latest versions of each of these other plugins to run: WP-e-Commerce, EventPress, BuddyPress, BuddyPress Custom post types.

BuddyPress and EventPress can just be downloaded from WordPress.org - make sure they are the most recent versions.

All of these plugins install into the wp-content/plugins directory and should be activated like any other plugin

For best results you should install the plugins in this order: buddypress, buddypress custom post types, eventPress, wp-e-commerce, TikiPress.


== Downloading a new version ==

This plugin currently does not have automatic plugin notification, however any premium upgrades for GetShopped.org can be downloaded from:

http://getshopped.org/extend/premium-upgrades-files/

You will be required to enter your Session ID (this can be found with your API key on your purchase receipt)


== Support ==

If you have any problems with TikiPress or require more information check this out:
	
General help: http://getshopped.org/resources/docs/

Support Forum: http://www.getshopped.org/forums/

Premium Support Forum: http://getshopped.org/resources/premium-support/

Please Note: that we will do our best to assist you with any problems relating to TikiPress, however general questions relating to the other plugins eg EventPress / BuddyPress We will not be able to help you with, you would need to talk to the core developers for that plugin


==Getting started with this plugin==


== Important things to note / required settings ==
Below are the minimum required settings for all these plugins to work nicely please take the time to read and configure them.

1. For this to work you must set your permalink structure to /%category%/%postname%/

2. Go to Store->Categories and add "Tickets" category (in wp-e-commerce)


3. Go to Store->TikiPress -> Configure and select "Tickets" category (or whatever Category you made in step two, This is the category that all your ticket products created in wp-e-commerce MUST use) if you are creating other products to such as merchandise then create a different category for that.

3. The payment must be marked as accepted in the wp-e-commerce sales log in order for any redemption codes to be sent.

4. Users must be registered to your site to redeems ticket codes so it would be advised to change your wordpress settings to "anyone can register" (you do this under the main wordpress users menu)

5. Some buddy press themes (well the default one anyway) will not support Parent / child menu structure. If this is the case and you only see a products page (and not a checkout page) in your nav then please change the checkout page to have no parent.

6. If you would like to display a list of attendees on your site (so other users can see who is going to the event) then create a page with this shortcode: [bpt_attendees id='productid']
where product id is the product id for the ticket thats the event relates to. (an easy way to find this is to go to store > products and hover over the ticket in the bar at the bottom you will see the product id)

7. The Redeem page for ticket codes is also a short code so you will need to include this on a page also: [bpt_redeem_code_page]


== Setting up the events (EventPress)==
 
1. Go to Events and add a new event, this is the event that you are going to be selling a ticket for.

The Registration metaboxes on this page are not used by TikiPress. TikiPress will deal with all the registrations / attendees so you can disregard this section.


== Setting up BuddyPress and Ticket Checkout Fields ==

The BuddyPress fields determine what attendee data is collected for your event. The user buying the tickets at checkout will fill these out with their purchase. If more than one ticket is purchased then the person who gets emailed the redemption code will also be asked to fill out the same fields

1.(optional) Go to BuddyPress -> Profile Field Setup and add any fields you want. Values of Radio buttons and Drop-down lists will be shown in statistics, other information will be show per user.


== Creating a Ticket - product ==

This is the ticket that you are going to be selling for the event you have just created.

1. Go to Store -> Products -> Add product Fill out all the details about your ticket and event etc.

2. In the Event Metabox please select the event that this ticket relates to, If this is not done then the attendees and statistics will not work.

3. Category - must be the category you set for tickets

4. If you want the tickets sold / reaming stock counter to work you must set a ticket limit for your product. To do this just select the options "I have limited quantities of this stock" Under stock control metabox.

==Testing this out ==

1. Go to front-end Products page

2. Add some tickets to your cart, to test the ticket code redemption you will need to purchase more than one ticket.

3. Enter billing/contact details and fill out the ticket information fields

4. Enter email addresses for the recipients of the other tickets (for testing enter in an address you have access to), those need to be emails of registered users, if they are not registered, then they must register with the same email before Redeeming the voucher.

5. If using manual gateway go to the sales log and change the payment status to accepted.

6. Check the email for a redemption code, for the other tickets purchased.

7. You should now be able to go to the redeem page and redeem your code, you should be asked to fill out the same fields again.

8. Once you have some registered attendees you can use the statistics and attendees features under store >buddyPress TikiPress 