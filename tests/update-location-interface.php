<?php
/**
 * Behaviour test for the Open User Map update-location ability.
 *
 * Run: php tests/update-location-interface.php
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['mcp_oum_actions']      = array();
$GLOBALS['mcp_oum_abilities']    = array();
$GLOBALS['mcp_oum_updated_meta'] = array();
$GLOBALS['mcp_oum_cleaned_posts'] = array();
$GLOBALS['mcp_oum_can_edit']      = true;
$GLOBALS['mcp_oum_post_type']     = 'oum-location';
$GLOBALS['mcp_oum_write_result']  = true;
$GLOBALS['mcp_oum_location_meta'] = array(
	'address'      => '',
	'lat'          => 54.852851,
	'lng'          => 9.359261,
	'text'         => '<p>Padborg</p>',
	'author_name'  => '',
	'author_email' => '',
	'votes'        => 7,
);

function add_action( string $hook, callable $callback ): void {
	$GLOBALS['mcp_oum_actions'][ $hook ][] = $callback;
}

function wp_register_ability( string $name, array $definition ): void {
	$GLOBALS['mcp_oum_abilities'][ $name ] = $definition;
}

function __( string $text, string $domain = '' ): string {
	return $text;
}

function esc_html__( string $text, string $domain = '' ): string {
	return $text;
}

function sanitize_text_field( string $value ): string {
	return trim( strip_tags( $value ) );
}

function current_user_can( string $capability, ...$args ): bool {
	return 'edit_post' !== $capability || $GLOBALS['mcp_oum_can_edit'];
}

function get_post( int $post_id ): object {
	return (object) array( 'ID' => $post_id, 'post_type' => $GLOBALS['mcp_oum_post_type'] );
}

function get_post_meta( int $post_id, string $key, bool $single = false ): array {
	return $GLOBALS['mcp_oum_location_meta'];
}

function update_post_meta( int $post_id, string $key, $value ): bool {
	$GLOBALS['mcp_oum_updated_meta'] = array(
		'post_id' => $post_id,
		'key'     => $key,
		'value'   => $value,
	);
	return $GLOBALS['mcp_oum_write_result'];
}

function clean_post_cache( int $post_id ): void {
	$GLOBALS['mcp_oum_cleaned_posts'][] = $post_id;
}

function mcp_oum_assert_same( $expected, $actual, string $message ): void {
	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

require dirname( __DIR__ ) . '/mcp-abilities-open-user-map.php';

foreach ( $GLOBALS['mcp_oum_actions']['wp_abilities_api_init'] ?? array() as $callback ) {
	$callback();
}

$ability = $GLOBALS['mcp_oum_abilities']['open-user-map/update-location'] ?? null;
mcp_oum_assert_same( true, is_array( $ability ), 'The public update-location ability is registered.' );
mcp_oum_assert_same( true, $ability['meta']['mcp']['public'] ?? false, 'The update-location ability is public to the MCP adapter.' );
mcp_oum_assert_same( 'tool', $ability['meta']['mcp']['type'] ?? '', 'The update-location ability is exposed as an MCP tool.' );

$result = $ability['execute_callback'](
	array(
		'id'      => 400,
		'address' => 'Thorsvej 11, 6330 Padborg',
		'lat'     => 54.829832,
		'lng'     => 9.340319,
	)
);

mcp_oum_assert_same( true, $result['success'] ?? false, 'A valid Open User Map location update succeeds.' );
mcp_oum_assert_same( 400, $GLOBALS['mcp_oum_updated_meta']['post_id'], 'The requested location post is updated.' );
mcp_oum_assert_same( '_oum_location_key', $GLOBALS['mcp_oum_updated_meta']['key'], 'The native Open User Map meta key is updated.' );
mcp_oum_assert_same( 'Thorsvej 11, 6330 Padborg', $GLOBALS['mcp_oum_updated_meta']['value']['address'], 'The address is stored.' );
mcp_oum_assert_same( 54.829832, $GLOBALS['mcp_oum_updated_meta']['value']['lat'], 'The latitude is stored.' );
mcp_oum_assert_same( 9.340319, $GLOBALS['mcp_oum_updated_meta']['value']['lng'], 'The longitude is stored.' );
mcp_oum_assert_same( '<p>Padborg</p>', $GLOBALS['mcp_oum_updated_meta']['value']['text'], 'Existing location content is preserved.' );
mcp_oum_assert_same( 7, $GLOBALS['mcp_oum_updated_meta']['value']['votes'], 'Existing plugin-owned fields are preserved.' );
mcp_oum_assert_same( array( 400 ), $GLOBALS['mcp_oum_cleaned_posts'], 'The updated location post cache is cleared.' );

$GLOBALS['mcp_oum_updated_meta'] = array();
$invalid_result = $ability['execute_callback'](
	array(
		'id'  => 400,
		'lat' => 91.0,
		'lng' => 9.340319,
	)
);
mcp_oum_assert_same( false, $invalid_result['success'] ?? true, 'Coordinates outside the geographic range are rejected.' );
mcp_oum_assert_same( array(), $GLOBALS['mcp_oum_updated_meta'], 'Invalid coordinates cannot mutate location data.' );

$GLOBALS['mcp_oum_updated_meta'] = array();
$GLOBALS['mcp_oum_write_result'] = false;
$failed_write_result = $ability['execute_callback'](
	array(
		'id'      => 400,
		'address' => 'Thorsvej 11, 6330 Padborg',
		'lat'     => 54.829832,
		'lng'     => 9.340319,
	)
);
mcp_oum_assert_same( false, $failed_write_result['success'] ?? true, 'A failed Open User Map metadata write is reported as a failure.' );

$GLOBALS['mcp_oum_write_result'] = true;
$GLOBALS['mcp_oum_can_edit'] = false;
$GLOBALS['mcp_oum_updated_meta'] = array();
$permission_result = $ability['execute_callback']( array( 'id' => 400, 'lat' => 54.829832, 'lng' => 9.340319 ) );
mcp_oum_assert_same( false, $permission_result['success'] ?? true, 'A caller who cannot edit the location is rejected.' );
mcp_oum_assert_same( array(), $GLOBALS['mcp_oum_updated_meta'], 'Permission failure cannot mutate location data.' );

$GLOBALS['mcp_oum_can_edit'] = true;
$GLOBALS['mcp_oum_post_type'] = 'post';
$wrong_type_result = $ability['execute_callback']( array( 'id' => 400, 'lat' => 54.829832, 'lng' => 9.340319 ) );
mcp_oum_assert_same( false, $wrong_type_result['success'] ?? true, 'A non-Open User Map post is rejected.' );
mcp_oum_assert_same( array(), $GLOBALS['mcp_oum_updated_meta'], 'Wrong post type cannot mutate location data.' );

$GLOBALS['mcp_oum_post_type'] = 'oum-location';
$GLOBALS['mcp_oum_write_result'] = false;
$GLOBALS['mcp_oum_location_meta'] = array(
	'address'      => 'Thorsvej 11, 6330 Padborg',
	'lat'          => 54.829832,
	'lng'          => 9.340319,
	'text'         => '<p>Padborg</p>',
	'author_name'  => '',
	'author_email' => '',
	'votes'        => 7,
);
$no_change_result = $ability['execute_callback'](
	array(
		'id'      => 400,
		'address' => 'Thorsvej 11, 6330 Padborg',
		'lat'     => 54.829832,
		'lng'     => 9.340319,
	)
);
mcp_oum_assert_same( true, $no_change_result['success'] ?? false, 'An idempotent no-change update remains successful.' );

fwrite( STDOUT, "PASS: open-user-map/update-location preserves native location data while updating address and coordinates.\n" );
