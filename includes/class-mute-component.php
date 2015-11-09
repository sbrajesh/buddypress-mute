<?php
/**
 * Mute_Component class definition
 *
 * @package BuddyPress Mute
 * @subpackage Classes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Create a component.
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
	 * Include the required files.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $includes See BP_Component::includes().
	 */
	function includes( $includes = array() ) {

		$dir = trailingslashit( 'includes' );

		$includes = array(
			$dir . 'class-mute.php',
			$dir . 'functions.php',
			$dir . 'screens.php'
		);
		parent::includes( $includes );
	}

	/**
	 * Set up global data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args See BP_Component::setup_globals().
	 */
	function setup_globals( $args = array() ) {

		if ( ! defined( 'BP_MUTE_SLUG' ) ) {
			define( 'BP_MUTE_SLUG', 'mute' );
		}

		$args = array(
			'slug'                  => BP_MUTE_SLUG,
			'global_tables'         => array( 'table_name' => buddypress()->table_prefix . 'bp_mute' ),
			'notification_callback' => 'bp_mute_format_notifications'
		);
		parent::setup_globals( $args );
	}

	/**
	 * Set up the navigation.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav().
	 * @param array $sub_nav Optional. See BP_Component::setup_nav().
	 */
	function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		$count = Mute::get_count( bp_displayed_user_id() );

		$class = ( $count === 0 ) ? 'no-count' : 'count';

		$main_nav = array(
			'name'                    => sprintf( __( 'Mute <span class="%s">%s</span>', 'buddypress-mute' ), esc_attr( $class ), number_format_i18n( $count ) ),
			'position'                => 80,
			'default_subnav_slug'     => 'all',
			'slug'                    => $this->slug,
			'item_css_id'             => $this->id,
			'show_for_displayed_user' => bp_core_can_edit_settings(),
			'screen_function'         => 'bp_mute_all_screen'
		);

		$sub_nav[] = array(
			'name'            => __( 'All', 'buddypress-mute' ),
			'slug'            => 'all',
			'position'        => 10,
			'parent_slug'     => $this->slug,
			'parent_url'      => trailingslashit( bp_displayed_user_domain() . $this->slug ),
			'user_has_access' => bp_core_can_edit_settings(),
			'screen_function' => 'bp_mute_all_screen'
		);

		if ( bp_is_active( 'friends' ) ) {

			$sub_nav[] = array(
				'name'            => __( 'Friends', 'buddypress-mute' ),
				'slug'            => 'friends',
				'position'        => 20,
				'parent_slug'     => $this->slug,
				'parent_url'      => trailingslashit( bp_displayed_user_domain() . $this->slug ),
				'user_has_access' => bp_core_can_edit_settings(),
				'screen_function' => 'bp_mute_friends_screen'
			);
		}
		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up component in admin bar.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar().
	 */
	function setup_admin_bar( $wp_admin_nav = array() ) {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$wp_admin_nav[] = array(
			'title'  => $this->name,
			'id'     => 'my-account-' . $this->id,
			'parent' => buddypress()->my_account_menu_id,
			'href'   => trailingslashit( bp_loggedin_user_domain() . $this->slug ),
		);

		$wp_admin_nav[] = array(
			'title'  => __( 'All', 'buddypress-mute' ),
			'id'     => 'my-account-' . $this->id . '-all',
			'parent' => 'my-account-' . $this->id,
			'href'   => trailingslashit( trailingslashit( bp_loggedin_user_domain() . $this->slug ) . 'all' ),
		);

		if ( bp_is_active( 'friends' ) ) {

			$wp_admin_nav[] = array(
				'title'  => __( 'Friends', 'buddypress-mute' ),
				'id'     => 'my-account-' . $this->id . '-friends',
				'parent' => 'my-account-' . $this->id,
				'href'   => trailingslashit( trailingslashit( bp_loggedin_user_domain() . $this->slug ) . 'friends' ),
			);
		}
		parent::setup_admin_bar( $wp_admin_nav );
	}
}