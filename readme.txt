=== LSM User Activity Tracker ===
Contributors: Leadstart Media, Inc.
Stable tag: 1.0.0
Requires at least: 5.0
Tested up to: 6.7.1
Requires PHP: 7.0
License: GPLv2 or later
Tags: user activity, user tracking, profile changes, login tracking, purchase tracking

Tracks user activity on your WordPress site, including post views, post activity, profile changes, login/logout events, and WooCommerce purchases.

== Description ==

This plugin provides comprehensive tracking of user activity on your WordPress site. It logs post views, post creation and updates, profile changes (including display name, social metadata, bio, and website), login/logout events, and WooCommerce purchases. All data is stored in user metadata, allowing for detailed analysis and reporting.

Key Features:

* Post View Tracking: Records detailed information about posts viewed by logged-in users, including timestamp, post ID, title, URL, author, and more.
* Post Activity Tracking: Logs post creation and update events, including timestamps, post status, and author roles.
* Profile Change Tracking: Monitors changes to user profiles, including display name, social metadata, bio, and website.
* Login/Logout Tracking: Records login and logout events, including timestamps and IP addresses.
* WooCommerce Purchase Tracking: Logs WooCommerce purchases, including product names, quantities, product IDs, and post types.
* Combined Activity Display: Provides a shortcode to display all tracked activities in a single, time-sorted list.
* Eastern Time Zone: All timestamps are recorded in the Eastern Time Zone.
* Data Limiting: Limits the number of stored entries for each activity type to prevent database bloat.
* Social Login Compatibility: Works with the WordPress Social Login plugin.
* WooCommerce and WordPress.com Compatibility: Tracks logins from WooCommerce and WordPress.com.
* Privacy Focused: No order id's are shown in the purchase logs.

Shortcode:

* `[uat_combined_data]` - Displays all tracked user activity for the current user.
* `[uat_combined_data user_id="123"]` - Displays all tracked user activity for the specified user ID (replace "123" with the actual user ID).

== Installation ==

1.  Upload the `user-activity-tracker` directory to the `/wp-content/plugins/` directory.
2.  Activate the User Activity Tracker plugin through the `Plugins` menu in WordPress.
3.  Use the `[uat_combined_data]` shortcode to display the combined user activity.

== Changelog ==

= 1.0.0 =
* Initial release.
* Added post view tracking.
* Added post activity tracking.
* Added profile change tracking (display name, social metadata, bio, website).
* Added login/logout tracking.
* Added WooCommerce purchase tracking (product names and quantities).
* Added combined activity display shortcode.
* Added Eastern Time Zone timestamps.
* Added data limiting to 100 entries.
* Added compatibility with WordPress Social Login plugin.
* Added compatibility with WooCommerce and WordPress.com logins.
* Separated date and time in output.
* Added post type with <small> tag.
* Added profile change display only when changed.

