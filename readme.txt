=== Ray Social Feeds For Twitter ===
Contributors: raycreations, amritray, aparna19
Tags: Twitter, Tweets, Twitter Feed, Twitter Widget, Twitter Gallery, Ray Social Feeds For Twitter
Requires at least: 3.2
Tested up to: 5.3.2
Stable tag: 1.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display beautiful twitter feeds on your website. Tweets include images & videos.

== Description ==
**Ray Social Feeds For Twitter** lets you display your Twitter feeds beautifully on your website in a list format. If a tweet includes images or videos, they will be displayed as part of the tweet. Alternatively, it will look for any external links in the tweet and fetch images from that link to display with your tweets.

Therefore, maximum number of tweets will include images or videos, which makes the display more visually appealing & engaging. 


Some highlights of the plugin:

*   Install the plugin and use the [my_custom_tweets] shortcode in any post or page to start displaying tweets instantly. 
*   No longer necessary to create Twitter Dev account to obtain your own Consumer Key & Secret to use the plugin
*   Uses the new Twitter API v 1.1 and OAuth 2.0 to fetch tweets from Twitter.
*   Use the shortcode **[my_custom_tweets]** anywhere in your content area. Or use our "widget" in your sidebars & footers to display your Twitter feed.
*   Use any Twitter "screen name" or "hashtags" to display the feed.
*   When specifying **multiple hashtags**, simply separate them using space. 
*   No need to use the hash "#" or "@" symbol with your hashtags or with Twitter screen names.
*   **Multiple shortcodes & widgets** can be used on a single page or on different site pages.
*   You can define "default options" for the feed display using our plugin settings page.
*   The default options can be overridden by parameters you specify in your shortcodes. For example, if the default screen name is **raycreations**, and in your shortcode, you specify **[my_custom_tweets screen_name='rayamrit']**. Then the shortcode screenname takes precedence.
*   There is a limit of 10 tweets for each shortcode or widget.

*   There is **no limit** to the number of instances of shortcodes or widgets you can place on your page or site.
*   The display is completely **Responsive** and adjusts to the width of the container in which the shortcode or widget is placed.
*   Displays perfectly on all screen sizes and devices.
*   The Twitter feed is "highly customizable" using parameters in your shortcode or widgets.
*   The **Tweets are cached** for lightning-fast display.
*   Each shortcode and widget tweets are separately cached.


*   So, the tweets are fetched once and then cached. Subsequently, they are fetched from the cache itself. So, you don't exceed the rate limit set by Twitter. This also makes loading the Tweets lightning fast.

*   You can control the cache duration from our plugin settings page. The minimum duration is 1 hour. You can set the maximum cache duration to your liking.
*   Whenever you save the plugin settings page, the Tweets cache is automatically cleared to reflect the changes made. And new Tweets are fetched from Twitter again.

*   The Twitter feed takes the styling from your website itself so that it looks & feels like a part of your site without you having to make any style changes in the CSS stylesheet.
*   Our Twitter plugin **supports extended tweets** and fetches the complete tweet from Twitter.
*   Include or exclude **Replies** from your Twitter feed.

*   Include or exclude **Retweets** from your Twitter feed.

See demo [here](https://www.raycreations.net/ray-social-feeds-twitter/free-demo/)


== Pro Version ==

You can opt for our [Pro Version](https://www.raycreations.net/ray-social-feeds-twitter/) which includes many more features.

*   You can display your tweets in Masonry format.
*   You can choose the number of tweets in a row for the Masonry format.
*   You can display your tweets in a 1-column or 2-column slider.
*   You can fetch up to 50 tweets for any of your feed using shortcode or widget.
*   And of course, the pro version helps us maintain the free version of the plugin.
*   You also get priority support with our pro version.

Masonry & 2-column slider display [demo](https://www.raycreations.net/ray-social-feeds-twitter/pro-demo/)
List & 1-column slider display [demo](https://www.raycreations.net/ray-social-feeds-twitter/pro-demo-list/)


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/my-custom-twitter-feed` directory, or install the plugin through the WordPress plugins screen directly.

2. Activate the plugin through the 'Plugins' screen in WordPress.

3. **Optional Step**: Use the Settings -> Ray Twitter Feeds -> Ray Social Feeds For Twitter Settings screen -> In the "Settings" tab, update the "Consumer Key" & "Consumer Secret" with your keys and hit "Save Changes". Read the section below labeled "Generating Twitter API Keys" to know more about how you can generate the API keys.

4. Navigate to **Customize** tab and choose *Feed Type* as either "User Timeline" or "Hashtags".

5. Enter a "Twitter screen name" in the **Screen Name** field. This will only be used if *User Timeline* is selected as the Feed type.

6. Enter hashtag/s in the **Hashtags** field. This will only be used if *Hashtags* was selected as the *Feed type*.

7. For the **Include Media in Search** field, provide your preference for Photos or Videos, or both, by clicking on either *Include Photos* or *Include Videos*. You can also select both.

8. Check the **Exclude Replies** checkbox to exclude Replies from fetched Tweets.

9. Check the **Include Retweets** checkbox to include Retweets in your feed.

10. In the **Number of Tweets** field, enter the number of tweets to fetch from Twitter.

11. For **Check Tweets Every** field, you need to need either *hour* or *day*.

12. For the **Tweet Checking Interval** field, enter a numeric value. 

13. Points 11 & 12 in combination decide the cache duration for fetched tweets.


= Generating Twitter API Keys (OPTIONAL) =
Note: This step is optional now. The plugin will generate a Twitter token for you automatically in the background. No need to create a Twitter Developer account to obtain your own Consumer Key & Secret to use with our site.

If you wish to use your own Twitter Consumer Key & Consumer Secret to use with our plugin, you can do that. Simply follow the steps below.


For step by step instructions with screenshots of each of the steps involved, see this article [here](https://www.raycreations.net/generating-twitter-api-keys/).


== For Support & Help ==
We want to provide the best possible user experience for our plugin users. We would like to hear your feedback and suggestions. If you need any help or support related to our plugin, please feel free to contact us through [this form here](https://www.raycreations.net/contact/).


== Screenshots ==

1. The demo feed created by our plugin. The tweets displaying in the content area is the user_timeline from the Twitter "screen name" @raycreations. On the sidebar is shown the hashtags timeline created using two hashtags, #nature & #mountain.

2. This is the screen where you put your API keys & secret (Optional)

3. This screen displays the options you can use to set the default values for customizing your Twitter feeds.

4. The settings you can specify for each widget to customize the feed display.


== Upgrade Notice ==
= 1.1 =
* New: no longer necessary to create your own twitter developer account to obtain keys
* New: added button to delete cached tweets
* Fix: loading twitter profile image over https instead of http


== Changelog ==

= 1.1 =
* New: no longer necessary to create your own twitter developer account to obtain keys
* New: added button to delete cached tweets
* Fix: loading twitter profile image over https instead of http

= 1.0 =
* First published version

