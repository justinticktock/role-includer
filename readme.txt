=== Plugin Name ===
Contributors: justinticktock
Tags: roles, user, cms, groups, teams
Requires at least: 3.5
Tested up to: 4.6.1
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple settings page to allocate multiple roles to a user.

== Description ==

There are lots of plugins to handle roles and capabilities, however, none provide one simple interface for allocating multiple roles to a user.

This plugin expects the complexity of capabilities to be handled by the Administrator through another plugin, I'm not reproducing what is already available.  What 'Role Includer' allows is for any user with the 'promote_users' capability a simple way to handle allocation of roles and only that.

So by simply creating a new role, for example "staff" and adding the 'promote_users' capability to this role, all staff members will be able to handle role assignment.  
 

If you wish to hide/mask-out a particular role from "staff" ( such as "Administrator" ) so that staff cannot allocate the higher access level then you can exclude higher roles by using the "Role Excluder" plugin available over at [justinandco.com](https://justinandco.com/plugins/)

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Goto the admin Users menu..All Users, hover over a user name in the listing and select “Roles”
4. Select/De-select the roles required for the user.

== Frequently Asked Questions ==


== Screenshots ==

1. The Settings Screen.

== Changelog ==

Change log is maintained on [the plugin website]( https://justinandco.com/plugins/role-includer-change-log/ "Role Includer Plugin Changelog" )

== Upgrade Notice ==
