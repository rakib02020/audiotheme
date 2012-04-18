<?php
/**
 * Load Discography Admin
 *
 * @since 1.0
 */
add_action( 'init', 'audiotheme_load_discography_admin' );
function audiotheme_load_discography_admin() {
	if ( isset( $_POST['audiotheme_discography_rewrite_base'] ) ) {
		update_option( 'audiotheme_discography_rewrite_base', $_POST['audiotheme_discography_rewrite_base'] );
	}
	
	add_action( 'admin_menu', 'audiotheme_discography_admin_menu' );
	add_action( 'admin_init', 'audiotheme_discography_admin_init' );
	add_action( 'load-themes.php', 'audiotheme_discography_setup' );
	add_filter( 'post_updated_messages', 'audiotheme_discography_post_updated_messages' );
	
	/* Records */
	require( AUDIOTHEME_DIR . 'discography/admin/record.php' );
	
	add_action( 'save_post', 'audiotheme_record_save_hook' );
	// All Records Screen
	add_filter( 'parse_query', 'audiotheme_records_admin_query' );
	add_filter( 'manage_edit-audiotheme_record_columns', 'audiotheme_record_columns' );
	add_action( 'manage_edit-audiotheme_record_sortable_columns', 'audiotheme_record_sortable_columns' );
	add_action( 'manage_pages_custom_column', 'audiotheme_record_display_column', 10, 2 );
	
	/* Tracks */
	require( AUDIOTHEME_DIR . 'discography/admin/track.php' );
	
	add_action( 'save_post', 'audiotheme_track_save_hook' );
	add_action( 'wp_unique_post_slug', 'audiotheme_track_unique_slug', 10, 5 );
	// All Tracks Screen
	add_filter( 'parse_query', 'audiotheme_tracks_admin_query' );
	add_action( 'restrict_manage_posts', 'audiotheme_tracks_filters' );
	add_filter( 'manage_edit-audiotheme_track_columns', 'audiotheme_track_columns' );
	add_action( 'manage_edit-audiotheme_track_sortable_columns', 'audiotheme_track_sortable_columns' );
	add_action( 'manage_posts_custom_column', 'audiotheme_track_display_column', 10, 2 );
}

/**
 * Add Discography Data
 *
 * Runs anytime themes.php is visited to ensure record types exist.
 *
 * @since 1.0
 */
function audiotheme_discography_setup() {
	if ( taxonomy_exists( 'audiotheme_record_type' ) ) {
		$record_types = get_audiotheme_record_type_slugs();
		if ( $record_types ) {
			foreach( $record_types as $type_slug ) {
				if ( ! term_exists( $type_slug, 'audiotheme_record_type' ) ) {
					wp_insert_term( $type_slug, 'audiotheme_record_type', array( 'slug' => $type_slug ) );
				}
			}
		}
	}
}

/**
 * Discography Admin Menu
 *
 * @since 1.0
 */
function audiotheme_discography_admin_menu() {
	add_menu_page( __( 'Discography', 'audiotheme-i18n' ), __( 'Discography', 'audiotheme-i18n' ), 'edit_posts', 'edit.php?post_type=audiotheme_record', NULL, NULL, 7 );
}

/**
 * Discography Post Type Update Messages
 *
 * @since 1.0
 */
function audiotheme_discography_post_updated_messages( $messages ) {
	global $post, $post_ID;
	
	$messages['audiotheme_record'] = array(
		0 => '',
		1 => sprintf( __( 'Record updated. <a href="%s">View Record</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3 => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4 => __( 'Record updated.', 'audiotheme-i18n' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Record published. <a href="%s">View Record</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Record saved.', 'audiotheme-i18n' ),
		8 => sprintf( __( 'Record submitted. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Record</a>', 'audiotheme-i18n' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Record draft updated. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);
	
	$messages['audiotheme_track'] = array(
		0 => '',
		1 => sprintf( __( 'Track updated. <a href="%s">View Track</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3 => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4 => __( 'Track updated.', 'audiotheme-i18n' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Track restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Track published. <a href="%s">View Track</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Track saved.', 'audiotheme-i18n' ),
		8 => sprintf( __( 'Track submitted. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Track scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Track</a>', 'audiotheme-i18n' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Track draft updated. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}

/**
 * Register Discography Rewrite Base Setting
 *
 * @since 1.0
 */
function audiotheme_discography_admin_init() {
	add_settings_field(
		'audiotheme_discography_rewrite_base',
		'<label for="audiotheme-discography-rewrite-base">' . __( 'Discography base', 'audiotheme-i18n' ) . '</label>',
		'audiotheme_discography_rewrite_base_settings_field',
		'permalink',
		'optional'
	);
}

/**
 * Callback for Displaying Discography Rewrite Base
 *
 * @since 1.0
 */
function audiotheme_discography_rewrite_base_settings_field() {
	$discography_base = get_option( 'audiotheme_discography_rewrite_base' );
	?>
	<input type="text" name="audiotheme_discography_rewrite_base" id="audiotheme-discography-rewrite-base" value="<?php echo esc_attr( $discography_base ); ?>" class="regular-text code">
	<span class="description"><?php _e( 'Default is <code>record</code>.', 'audiotheme-i18n' ); ?></span>
	<?php
}
?>