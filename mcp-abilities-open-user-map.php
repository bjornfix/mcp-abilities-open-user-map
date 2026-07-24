<?php
/**
 * Plugin Name: MCP Abilities - Open User Map
 * Plugin URI: https://devenia.com/plugins/mcp-expose-abilities/#add-ons
 * Description: Narrow MCP abilities for maintaining Open User Map location records.
 * Version: 0.1.0
 * Author: basicus
 * Author URI: https://profiles.wordpress.org/basicus/
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.9
 * Requires PHP: 8.0
 * Text Domain: mcp-abilities-open-user-map
 *
 * @package MCP_Abilities_Open_User_Map
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Open User Map maintenance abilities.
 */
function mcp_oum_register_abilities(): void {
	if ( ! function_exists( 'wp_register_ability' ) ) {
		return;
	}

	wp_register_ability(
		'open-user-map/update-location',
		array(
			'label'               => __( 'Update Open User Map Location', 'mcp-abilities-open-user-map' ),
			'description'         => __( 'Update the address and coordinates of one Open User Map location while preserving its other native fields.', 'mcp-abilities-open-user-map' ),
			'category'            => 'site',
			'input_schema'        => array(
				'type'                 => 'object',
				'required'             => array( 'id', 'lat', 'lng' ),
				'properties'           => array(
					'id'      => array(
						'type'        => 'integer',
						'minimum'     => 1,
						'description' => 'Open User Map location post ID.',
					),
					'address' => array(
						'type'        => 'string',
						'description' => 'Optional human-readable address stored with the map marker.',
					),
					'lat'     => array(
						'type'        => 'number',
						'minimum'     => -90,
						'maximum'     => 90,
						'description' => 'Latitude in decimal degrees.',
					),
					'lng'     => array(
						'type'        => 'number',
						'minimum'     => -180,
						'maximum'     => 180,
						'description' => 'Longitude in decimal degrees.',
					),
				),
				'additionalProperties' => false,
			),
			'output_schema'       => array(
				'type'       => 'object',
				'properties' => array(
					'success'  => array( 'type' => 'boolean' ),
					'id'       => array( 'type' => 'integer' ),
					'address'  => array( 'type' => 'string' ),
					'lat'      => array( 'type' => 'number' ),
					'lng'      => array( 'type' => 'number' ),
					'message'  => array( 'type' => 'string' ),
				),
			),
			'execute_callback'    => 'mcp_oum_update_location',
			'permission_callback' => static function (): bool {
				return current_user_can( 'edit_posts' );
			},
			'meta'                => array(
				'annotations' => array(
					'readonly'    => false,
					'destructive' => false,
					'idempotent'  => true,
				),
				'mcp'         => array(
					'public' => true,
					'type'   => 'tool',
				),
			),
		)
	);
}

/**
 * Update one native Open User Map location record.
 *
 * @param mixed $input Ability input.
 * @return array<string,mixed>
 */
function mcp_oum_update_location( $input = array() ): array {
	$input   = is_array( $input ) ? $input : array();
	$post_id = isset( $input['id'] ) ? (int) $input['id'] : 0;
	$post    = $post_id > 0 ? get_post( $post_id ) : null;

	if ( ! $post || 'oum-location' !== $post->post_type ) {
		return array( 'success' => false, 'message' => esc_html__( 'Open User Map location not found.', 'mcp-abilities-open-user-map' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return array( 'success' => false, 'message' => esc_html__( 'Permission denied to edit this Open User Map location.', 'mcp-abilities-open-user-map' ) );
	}

	$lat = $input['lat'] ?? null;
	$lng = $input['lng'] ?? null;
	if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
		return array( 'success' => false, 'message' => esc_html__( 'Latitude and longitude must be numeric.', 'mcp-abilities-open-user-map' ) );
	}

	$lat = (float) $lat;
	$lng = (float) $lng;
	if ( ! is_finite( $lat ) || ! is_finite( $lng ) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
		return array( 'success' => false, 'message' => esc_html__( 'Latitude or longitude is outside the valid geographic range.', 'mcp-abilities-open-user-map' ) );
	}

	$location = get_post_meta( $post_id, '_oum_location_key', true );
	$location = is_array( $location ) ? $location : array();

	if ( array_key_exists( 'address', $input ) ) {
		$location['address'] = sanitize_text_field( (string) $input['address'] );
	}
	$location['lat'] = $lat;
	$location['lng'] = $lng;

	$updated = update_post_meta( $post_id, '_oum_location_key', $location );
	if ( false === $updated ) {
		$stored = get_post_meta( $post_id, '_oum_location_key', true );
		if ( ! is_array( $stored ) || $stored !== $location ) {
			return array( 'success' => false, 'message' => esc_html__( 'Open User Map location could not be updated.', 'mcp-abilities-open-user-map' ) );
		}
	}
	clean_post_cache( $post_id );

	return array(
		'success' => true,
		'id'      => $post_id,
		'address' => (string) ( $location['address'] ?? '' ),
		'lat'     => $location['lat'],
		'lng'     => $location['lng'],
		'message' => esc_html__( 'Open User Map location updated successfully.', 'mcp-abilities-open-user-map' ),
	);
}

add_action( 'wp_abilities_api_init', 'mcp_oum_register_abilities' );
