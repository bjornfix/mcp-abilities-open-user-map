# MCP Abilities – Open User Map

Move an Open User Map marker through one controlled WordPress ability while preserving the location details already stored with it.

[![GitHub release](https://img.shields.io/github/v/release/bjornfix/mcp-abilities-open-user-map)](https://github.com/bjornfix/mcp-abilities-open-user-map/releases)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.9%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net)

**Tested up to:** 7.0

**Stable tag:** 0.1.0

**License:** GPLv2 or later

**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

**Tags:** mcp, abilities-api, open-user-map, locations, maps

## What It Does

This add-on registers one authenticated WordPress ability:

| Ability | Outcome |
| --- | --- |
| `open-user-map/update-location` | Updates one native Open User Map location address and coordinates while preserving its other stored fields. |

The ability validates the target post, the caller's edit permission, and geographic coordinate ranges before saving.

## The Real Workflow

1. Discover the available abilities through your MCP client.
2. Read the location you intend to move and confirm its WordPress post ID.
3. Resolve the verified destination address and coordinates.
4. Call `open-user-map/update-location` once for that location.
5. Read the location again and verify the rendered map.

## Why This Feels Different

Open User Map locations can carry more than a pin: descriptions, zoom, geometry, votes, ratings, and custom fields may all matter. The update ability changes only the requested address and coordinates. The rest of the native location record remains intact.

## Before vs After

### Before

- open each marker in WordPress
- copy existing location details before moving it
- replace the address and coordinates manually
- check that unrelated fields survived the save

### After

- identify the native location post
- provide the new address and coordinates
- let one narrow ability preserve the remaining record
- verify the map result

## Who It Is For

- agencies maintaining Open User Map installations through MCP
- site operators who need repeatable location updates
- teams that want map maintenance to stay inside the plugin's native data model
- developers extending the WordPress Abilities API with focused operations

## Requirements

- WordPress 6.9 or newer
- PHP 8.0 or newer
- [Open User Map](https://wordpress.org/plugins/open-user-map/)
- WordPress Abilities API
- an MCP adapter that exposes registered WordPress abilities
- a WordPress user allowed to edit the target location

## Documentation

- [Devenia WordPress MCP plugins](https://devenia.com/plugins/mcp-expose-abilities/#add-ons)
- [Open User Map](https://wordpress.org/plugins/open-user-map/)
- [GitHub Releases](https://github.com/bjornfix/mcp-abilities-open-user-map/releases)
- [Stable download](https://downloads.devenia.com/mcp-abilities-open-user-map.zip)

## Start Here

1. Install the required Abilities API and MCP adapter stack.
2. Install and activate Open User Map.
3. Install and activate MCP Abilities – Open User Map.
4. Confirm `open-user-map/update-location` appears in ability discovery.
5. Test the update on a development site before changing production data.

## Usage Example

```json
{
  "ability_name": "open-user-map/update-location",
  "parameters": {
    "id": 400,
    "address": "Thorsvej 11, 6330 Padborg",
    "lat": 54.829832,
    "lng": 9.340319
  }
}
```

Successful calls return the location ID and the stored address and coordinates. Invalid post types, insufficient permissions, invalid coordinates, and failed persistence return a failure result without reporting a completed update.

## Safety and Ownership

- Open User Map remains the owner of the `oum-location` post type and location-data format.
- Existing fields not named by the ability are preserved.
- Only native Open User Map location posts may be updated.
- The caller must be allowed to edit the target post.
- Latitude must be between -90 and 90; longitude must be between -180 and 180.
- No public REST route or unauthenticated endpoint is added.
- The plugin does not edit Elementor, Toolset, templates, styles, or frontend markup.

## Installation

1. Download [mcp-abilities-open-user-map.zip](https://downloads.devenia.com/mcp-abilities-open-user-map.zip).
2. In WordPress, open **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP and activate the plugin.
4. Discover abilities through your configured MCP client.

## Development

Run the behavior test from the repository root:

```bash
php tests/update-location-interface.php
```

The installable ZIP excludes repository-only Markdown and test files.

## Changelog

### 0.1.0

- Added `open-user-map/update-location`.
- Preserved existing native Open User Map fields during location moves.
- Added validation for post type, permissions, coordinates, and persistence.

## Contributing

Open an issue with a reproducible example before proposing a broader ability. Keep new operations narrow, capability-gated, and aligned with Open User Map's native data ownership.

## License

GPLv2 or later. Read the [GNU General Public License version 2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

## Author

[basicus](https://profiles.wordpress.org/basicus/)

## Links

- [Source](https://github.com/bjornfix/mcp-abilities-open-user-map)
- [Releases](https://github.com/bjornfix/mcp-abilities-open-user-map/releases)
- [Download](https://downloads.devenia.com/mcp-abilities-open-user-map.zip)
