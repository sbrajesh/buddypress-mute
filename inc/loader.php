<?php
/**
 * Component loader
 *
 * @package BuddyPress Mute
 * @subpackage Loader
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Create a BuddyPress Mute component.
 *
 * @since 1.0.0
 */
class Mute_Component extends BP_Component {

	/**
	 * Start the creation process.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	function __construct() {

		global $bp;

		parent::start( 'mute', __( 'Mute', 'buddypress-mute' ), dirname( dirname( __FILE__ ) ) );

		$this->includes();

		$bp->active_components[$this->id] = '1';
	}

	/**
	 * Set up the $includes array.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $includes See BP_Component::includes() for a description.
	 */
	function includes( $includes = array() ) {

		$includes = array(
			'inc/classes.php',
			'inc/functions.php',
			'inc/screens.php',
			'inc/ajax.php'
		);

		parent::includes( $includes );
	}

	/**
	 * Set up component global data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args See BP_Component::setup_globals() for a description.
	 */
	function setup_globals( $args = array() ) {

		if ( ! defined( 'BP_MUTE_SLUG' ) ) {
			define( 'BP_MUTE_SLUG', 'mute' );
		}

		$global_tables = array(
			'table_name' => buddypress()->table_prefix . 'bp_mute'
		);

		$globals = array(
			'slug' => BP_MUTE_SLUG,
			'notification_callback' => 'bp_mute_format_notifications',
			'global_tables' => $global_tables
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav() for a description.
	 * @param array $sub_nav Optional. See BP_Component::setup_nav() for a description.
	 */
	function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		if ( bp_is_user() && bp_user_has_access() ) {

			$count = Mute::get_count( bp_displayed_user_id() );

			$class = ( 0 === $count ) ? 'no-count' : 'count';

			$nav_name = sprintf( __( 'Mute <span class="%s">%s</span>', 'buddypress-mute' ), esc_attr( $class ), number_format_i18n( $count ) );

		} else {

			$nav_name = __( 'Mute', 'buddypress-mute' );
		}

		$main_nav = array(
			'name' => $nav_name,
			'slug' => $this->slug,
			'default_subnav_slug' => 'all',
			'position' => 80,
			'item_css_id' => $this->id,
			'show_for_displayed_user' => bp_core_can_edit_settings(),
			'screen_function' => 'bp_mute_all_screen'
		);

		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		$mute_link = trailingslashit( $user_domain . $this->slug );

		$sub_nav[] = array(
			'name' => __( 'All', 'buddypress-mute' ),
			'slug' => 'all',
			'parent_slug' => $this->slug,
			'parent_url' => $mute_link,
			'position' => 10,
			'user_has_access' => bp_core_can_edit_settings(),
			'screen_function' => 'bp_mute_all_screen'
		);

		if ( bp_is_active( 'friends' ) ) {

			$sub_nav[] = array(
				'name' => __( 'Friends', 'buddypress-mute' ),
				'slug' => 'friends',
				'parent_slug' => $this->slug,
				'parent_url' => $mute_link,
				'position' => 20,
				'user_has_access' => bp_core_can_edit_settings(),
				'screen_function' => 'bp_mute_friends_screen'
			);
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up component in WordPress admin bar.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar() for a description.
	 */
	function setup_admin_bar( $wp_admin_nav = array() ) {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$wp_admin_nav[] = array(
			'title' => $this->name,
			'id' => 'my-account-' . $this->id,
			'parent' => buddypress()->my_account_menu_id,
			'href' => trailingslashit( bp_loggedin_user_domain() . $this->slug ),
		);

		$wp_admin_nav[] = array(
			'title' => __( 'All', 'buddypress-mute' ),
			'id' => 'my-account-' . $this->id . '-all',
			'parent' => 'my-account-' . $this->id,
			'href' => trailingslashit( trailingslashit( bp_loggedin_user_domain() . $this->slug ) . 'all' ),
		);

		if ( bp_is_active( 'friends' ) ) {

			$wp_admin_nav[] = array(
				'title' => __( 'Friends', 'buddypress-mute' ),
				'id' => 'my-account-' . $this->id . '-friends',
				'parent' => 'my-account-' . $this->id,
				'href' => trailingslashit( trailingslashit( bp_loggedin_user_domain() . $this->slug ) . 'friends' ),
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}
}

/**
 * Load the component into the $bp global.
 *
 * @since 1.0.0
 */
function bp_mute_load_component() {

	buddypress()->mute = new Mute_Component;
}
add_action( 'bp_loaded', 'bp_mute_load_component' );