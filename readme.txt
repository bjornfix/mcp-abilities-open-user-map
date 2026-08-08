=== MCP Abilities - Open User Map ===
Contributors: basicus
Tags: mcp, abilities-api, open-user-map, locations, maps
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 8.0
Requires Plugins: open-user-map
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Narrow MCP maintenance abilities for native Open User Map location records.

== Description ==

Registers `open-user-map/update-location`, which updates one Open User Map location address and coordinates while preserving all other native location fields.

== Requirements ==

* WordPress 6.9 or newer.
* PHP 8.0 or newer.
* Open User Map installed and active.
* WordPress Abilities API and an MCP adapter installed and active.
* A WordPress user allowed to edit the target location.

== Safety and Ownership ==

* Open User Map remains the owner of the `oum-location` post type and location-data format.
* Only native Open User Map location posts may be updated.
* Existing location fields outside the requested address and coordinates remain unchanged.
* Latitude must be between -90 and 90, and longitude must be between -180 and 180.
* No public REST route or unauthenticated endpoint is added.

== Download ==

Download the stable plugin ZIP from https://downloads.devenia.com/mcp-abilities-open-user-map.zip.

== Installation ==

1. Install and activate Open User Map and the WordPress Abilities API stack.
2. Upload and activate this plugin.
3. Discover the `open-user-map/update-location` ability through the configured MCP adapter.

== Changelog ==

= 0.1.0 =
* Added the validated `open-user-map/update-location` ability.
