# Domain Context

## Open User Map Location

An `oum-location` WordPress post whose native location data is stored in `_oum_location_key` by the upstream Open User Map plugin.

## Ownership

- Open User Map owns the post type and serialized location-data shape.
- This add-on owns the narrow MCP maintenance Interface for those records.
- The Interface must preserve every existing Open User Map field it does not explicitly update.
- Elementor pages, translated page copy, and Toolset content are outside this Module.

## Interface

`open-user-map/update-location` updates one location post's address and coordinates after validating the post type, caller capability, and geographic ranges.

## Invariants

- Only `oum-location` posts may be updated.
- Callers must be allowed to edit the target post.
- Latitude is between -90 and 90; longitude is between -180 and 180.
- Existing native fields such as text, geometry, zoom, votes, ratings, and custom fields survive updates.
