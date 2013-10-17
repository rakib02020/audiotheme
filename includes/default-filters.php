<?php
/**
 * Define default filters for modifying WordPress behavior.
 *
 * @package AudioTheme_Framework
 */

/**
 * Filter audiotheme_archive permalinks to match the corresponding post type's
 * archive.
 *
 * @since 1.0.0
 *
 * @param string $permalink Default permalink.
 * @param WP_Post $post Post object.
 * @param bool $leavename Optional, defaults to false. Whether to keep post name.
 * @return string Permalink.
 */
function audiotheme_archives_post_type_link( $permalink, $post, $leavename ) {
	global $wp_rewrite;

	if ( 'audiotheme_archive' == $post->post_type  ) {
		$post_type = is_audiotheme_post_type_archive_id( $post->ID );
		$post_type_object = get_post_type_object( $post_type );

		if ( get_option( 'permalink_structure' ) ) {
			$front = '/';
			if ( isset( $post_type_object->rewrite ) && $post_type_object->rewrite['with_front'] ) {
				$front = $wp_rewrite->front;
			}

			if ( $leavename ) {
				$permalink = home_url( $front . '%postname%/' );
			} else {
				$permalink = home_url( $front . $post->post_name . '/' );
			}
		} else {
			$permalink = add_query_arg( 'post_type', $post_type, home_url( '/' ) );
		}
	}

	return $permalink;
}

/**
 * Filter post type archive permalinks.
 *
 * @since 1.0.0
 *
 * @param string $link Post type archive link.
 * @param string $post_type Post type name.
 * @return string
 */
function audiotheme_archives_post_type_archive_link( $link, $post_type ) {
	if ( $archive_id = get_audiotheme_post_type_archive( $post_type ) ) {
		$link = get_permalink( $archive_id );
	}

	return $link;
}

/**
 * Filter the default post_type_archive_title() template tag and replace with
 * custom archive title.
 *
 * @since 1.0.0
 *
 * @param string $label Post type archive title.
 * @return string
 */
function audiotheme_archives_post_type_archive_title( $title ) {
	$post_type_object = get_queried_object();

	if ( $page_id = get_audiotheme_post_type_archive( $post_type_object->name ) ) {
		$page = get_post( $page_id );
		$title = $page->post_title;
	}

	return $title;
}

/**
 * Add helpful nav menu item classes.
 *
 * Adds class hooks to various nav menu items since child pseudo selectors
 * aren't supported in all browsers.
 *
 * @since 1.0.0
 *
 * @param array $items List of menu items.
 * @param array $args Menu display args.
 * @return array
 */
function audiotheme_nav_menu_classes( $items, $args ) {
	global $wp;

	$classes = array();
	$first_top = -1;

	$current_url = trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );
	$blog_page_id = get_option( 'page_for_posts' );
	$is_blog_post = is_singular( 'post' );

	$is_audiotheme_post_type = is_singular( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ) );
	$post_type_archive_id = get_audiotheme_post_type_archive( get_post_type() );
	$post_type_archive_link = get_post_type_archive_link( get_post_type() );

	foreach ( $items as $key => $item ) {
		if ( 0 == $item->menu_item_parent ) {
			$first_top = ( -1 == $first_top ) ? $key : $first_top;
			$last_top = $key;
		} else {
			if ( ! isset( $classes['first-child-items'][ $item->menu_item_parent ] ) ) {
				$classes['first-child-items'][ $item->menu_item_parent ] = $key;
				$items[ $key ]->classes[] = 'first-child-item';
			}
			$classes['last-child-items'][ $item->menu_item_parent ] = $key;
		}

		if ( ! is_404() && ! is_search() ) {
			if (
				'audiotheme_archive' == $item->object &&
				$post_type_archive_id == $item->object_id &&
				trailingslashit( $item->url ) == $current_url
			) {
				$items[ $key ]->classes[] = 'current-menu-item';
			}

			if ( $is_blog_post && $blog_page_id == $item->object_id ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}

			// Add 'current-menu-parent' class to CPT archive links when viewing a singular template.
			if ( $is_audiotheme_post_type && $post_type_archive_link == $item->url ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}
		}
	}

	$items[ $first_top ]->classes[] = 'first-item';
	$items[ $last_top ]->classes[] = 'last-item';

	if ( isset( $classes['last-child-items'] ) ) {
		foreach ( $classes['last-child-items'] as $item_id ) {
			$items[ $item_id ]->classes[] = 'last-child-item';
		}
	}

	return $items;
}

/**
 * Add class to nav menu items based on their title.
 *
 * Adds a class to a nav menu item generated from the item's title, so
 * individual items can be targeted by name.
 *
 * @since 1.0.0
 *
 * @param array $classes CSS classes.
 * @param object $item Menu item.
 * @return array
 */
function audiotheme_nav_menu_name_class( $classes, $item ) {
	$new_classes[] = sanitize_html_class( 'menu-item-' . sanitize_title_with_dashes( $item->title ) );

	return array_merge( $classes, $new_classes );
}

/**
 * Page list CSS class helper.
 *
 * Stores information about the order of pages in a global variable to be
 * accessed by audiotheme_page_list_classes().
 *
 * @since 1.0.0
 * @see audiotheme_page_list_classes()
 *
 * @param array $pages List of pages.
 * @return array
 */
function audiotheme_page_list( $pages ) {
	global $audiotheme_page_depth_classes;

	$classes = array();
	foreach ( $pages as $page ) {
		if ( 0 === $page->post_parent ) {
			if ( ! isset($classes['first-top-level-page'] ) ) {
				$classes['first-top-level-page'] = $page->ID;
			}
			$classes['last-top-level-page'] = $page->ID;
		} else {
			if ( ! isset( $classes['first-child-pages'][ $page->post_parent ] ) ) {
				$classes['first-child-pages'][ $page->post_parent ] = $page->ID;
			}
			$classes['last-child-pages'][ $page->post_parent ] = $page->ID;
		}
	}
	$audiotheme_page_depth_classes = $classes;

	return $pages;
}

/**
 * Add classes to items in a page list.
 *
 * Adds a classes to items in wp_list_pages(), which serves as a fallback
 * when nav menus haven't been assigned. Mimics the classes added to nav menus
 * for consistent behavior.
 *
 * @since 1.0.0
 *
 * @param array $classes CSS classes.
 * @param WP_Post $page Page object.
 * @return array
 */
function audiotheme_page_list_classes( $classes, $page ) {
	global $audiotheme_page_depth_classes;

	$depth = $audiotheme_page_depth_classes;

	if ( 0 === $page->post_parent ) { $class[] = 'top-level-item'; }
	if ( isset( $depth['first-top-level-page'] ) && $page->ID == $depth['first-top-level-page'] ) { $classes[] = 'first-item'; }
	if ( isset( $depth['last-top-level-page'] ) && $page->ID == $depth['last-top-level-page'] ) { $classes[] = 'last-item'; }
	if ( isset( $depth['first-child-pages'] ) && in_array( $page->ID, $depth['first-child-pages'] ) ) { $classes[] = 'first-child-item'; }
	if ( isset( $depth['last-child-pages'] ) && in_array( $page->ID, $depth['last-child-pages'] ) ) { $classes[] = 'last-child-item'; }

	return $classes;
}

/**
 * Add widget count classes so they can be targeted based on their position.
 *
 * Adds a class to widgets containing it's position in the sidebar it belongs
 * to and adds a special class to the last widget.
 *
 * @since 1.0.0
 *
 * @param array $params Wiget registration args.
 * @return array
 */
function audiotheme_widget_count_class( $params ) {
	$class = '';
	$sidebar_widgets = wp_get_sidebars_widgets();
	$order = array_search( $params[0]['widget_id'], $sidebar_widgets[ $params[0]['id'] ] ) + 1;
	if ( $order == count( $sidebar_widgets[ $params[0]['id'] ] ) ) {
		$class = ' widget-last';
	}

	$params[0]['before_widget'] = preg_replace( '/class="(.*?)"/i', 'class="$1 widget-' . $order . $class . '"', $params[0]['before_widget'] );

	return $params;
}

/**
 * Set up AudioTheme templates when they're loaded.
 *
 * Limits default scripts and styles to load only for AudioTheme templates.
 *
 * @since 1.2.0
 */
function audiotheme_template_setup( $template ) {
	if ( is_audiotheme_default_template( $template ) ) {
		add_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );
	}
}

/**
 * Enqueue default frontend scripts and styles.
 *
 * Themes can remove default styles and scripts by removing this hook:
 * <code>remove_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );</code>
 *
 * @since 1.2.0
 */
function audiotheme_enqueue_scripts() {
	wp_enqueue_script( 'audiotheme' );
	wp_enqueue_style( 'audiotheme' );
}

/**
 * Add wrapper open tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_before_main_content() {
	echo '<div class="audiotheme">';
}

/**
 * Add wrapper close tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_after_main_content() {
	echo '</div>';
}
