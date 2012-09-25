=== Plugin Name ===
Contributors: NLPCaptcha
Tags: comments, registration, nlpcaptcha, antispam, mailhide, captcha
Requires at least: 2.7
Tested up to: 2.9.1
Stable tag: 3.1.6

Integrates NLPCaptcha anti-spam methods with WordPress including comment, registration, and email spam protection.

== Description ==

= What is NLPCaptcha? =

[NLPCaptcha](http://nlpcaptcha.in/ "NLPCaptcha") is an anti-spam method

== Installation ==

To install in regular WordPress and [WordPress MultiSite](http://codex.wordpress.org/Create_A_Network):

1. Upload the `wp-nlpcaptcha` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Get the NLPCaptcha API keys [here](http://nlpcaptcha.in/index.php/login "NLPCaptcha API keys") 

== Requirements ==

* You need the NLPCaptcha API keys [here](http://nlpcaptcha.in/index.php/login "NLPCaptcha API keys") 

== ChangeLog ==

= Version 3.1.6 =
* Initial Release


== Frequently Asked Questions ==

= HELP, I'm still getting spam! =
There are four common issues that make NLPCaptcha appear to be broken:

1. **Moderation Emails**: NLPCaptcha marks comments as spam, so even though the comments don't actually get posted, you will be notified of what is supposedly new spam. It is recommended to turn off moderation emails with NLPCaptcha.
1. **Akismet Spam Queue**: Again, because NLPCaptcha marks comments with a wrongly entered CAPTCHA as spam, they are added to the spam queue. These comments however weren't posted to the blog so NLPCaptcha is still doing it's job. It is recommended to either ignore the Spam Queue and clear it regularly or disable Akismet completely. NLPCaptcha takes care of all of the spam created by bots, which is the usual type of spam. The only other type of spam that would get through is human spam, where humans are hired to manually solve CAPTCHAs. If you still get spam while only having NLPCaptcha enabled, you could be a victim of the latter practice. If this is the case, then turning on Akismet will most likely solve your problem. Again, just because it shows up in the Spam Queue does NOT mean that spam is being posted to your blog, it's more of a 'comments that have been caught as spam by NLPCaptcha' queue.
1. **Trackbacks and Pingbacks**: NLPCaptcha can't do anything about pingbacks and trackbacks. You can disable pingbacks and trackbacks in Options > Discussion > Allow notifications from other Weblogs (Pingbacks and trackbacks).
1. **Human Spammers**: Believe it or not, there are people who are paid (or maybe slave labor?) to solve CAPTCHAs all over the internet and spam. This is the last and rarest reason for which it might appear that NLPCaptcha is not working, but it does happen. A combination of NLPCaptcha and Akismet might help to solve this problem, and if spam still gets through for this reason, it would be very minimal and easy to manually take care of.

= Are CAPTCHAs secure? I heard spammers are using porn sites to solve them: the CAPTCHAs are sent to a porn site, and the porn site users are asked to solve the CAPTCHA before being able to see a pornographic image. =

CAPTCHAs offer great protection against abuse from automated programs. While it might be the case that some spammers have started using porn sites to attack CAPTCHAs (although there is no recorded evidence of this), the amount of damage this can inflict is tiny (so tiny that we haven't even seen this happen!). Whereas it is trivial to write a bot that abuses an unprotected site millions of times a day, redirecting CAPTCHAs to be solved by humans viewing pornography would only allow spammers to abuse systems a few thousand times per day. The economics of this attack just don't add up: every time a porn site shows a CAPTCHA before a porn image, they risk losing a customer to another site that doesn't do this.

== Screenshots ==

1. The NLPCaptcha Settings
