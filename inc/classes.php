<?php
/**
 * Class definitions
 *
 * @package BuddyPress Mute
 * @subpackage Classes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * BuddyPress Mute class.
 *
 * @since 1.0.0
 */
class Mute {

	/**
	 * The mute ID
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id;

	/**
	 * The ID of the user to be muted.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $muted_id;

	/**
	 * The ID of the user initiating the mute request.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $user_id;

	/**
	 * The date the mute was recorded in 'Y-m-d h:i:s' format.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $date_recorded;

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $muted_id The ID of the user to be muted.
	 * @param int $user_id The ID of the user initiating the mute request.
	 */
	function __construct( $muted_id = '', $user_id = '' ) {

		if ( empty( $muted_id ) || empty( $user_id ) )
			return;

		$this->populate( $muted_id, $user_id );
	}

	/**
	 * Populate necessary variables.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $muted_id The ID of the user to be muted.
	 * @param int $user_id The ID of the user initiating the mute request.
	 */
	protected function populate( $muted_id, $user_id ) {

		$this->muted_id = (int) $muted_id;

		$this->user_id = (int) $user_id;

		$this->date_recorded = bp_core_current_time();

		global $bp, $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->mute->table_name} WHERE muted_id = %d AND user_id = %d", $this->muted_id, $this->user_id ) );

		if ( $id ) {
			$this->id = $id;
		}
	}

	/**
	 * Save a mute request to the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int|bool The number of rows affected or false if an error occurred.
	 */
	function save() {

		global $bp, $wpdb;

		if ( $this->id ) {
			$retval = $wpdb->query( $wpdb->prepare( "UPDATE {$bp->mute->table_name} SET muted_id = %d, user_id = %d, date_recorded = %s WHERE id = %d", $this->muted_id, $this->user_id, $this->date_recorded, $this->id ) );

		} else {
			$retval = $wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->mute->table_name} ( muted_id, user_id, date_recorded ) VALUES ( %d, %d, %s )", $this->muted_id, $this->user_id, $this->date_recorded ) );
			$this->id = $wpdb->insert_id;
		}

		return $retval;
	}

	/**
	 * Delete a mute record from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int|bool The number of rows affected or false if an error occurred.
	 */
	function delete() {

		global $bp, $wpdb;

		$retval = $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->mute->table_name} WHERE id = %d", $this->id ) );

		return $retval;
	}

	/**
	 * Get the user IDs that a user is muting.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id The ID of the user doing the muting.
	 * @return array
	 */
	static function get_muting( $user_id ) {

		global $bp, $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT muted_id FROM {$bp->mute->table_name} WHERE user_id = %d", $user_id ) );
	}

	/**
	 * Get the mute count for a given user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id The user ID to fetch the count for.
	 * @return int
	 */
	static function get_count( $user_id ) {

		global $bp, $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$bp->mute->table_name} WHERE user_id = %d", $user_id ) );
	}

	/**
	 * Delete all mute records relating to a given user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id The ID of the user.
	 * @return int|bool The number of rows affected or false if an error occurred.
	 */
	static function delete_all( $user_id ) {

		global $bp, $wpdb;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$bp->mute->table_name} WHERE muted_id = %d OR user_id = %d", $user_id, $user_id ) );
	}
}
