<?php
/*
   Plugin Name: ASMAC Cornerstone Groups Condition
   Description: Adds itthinx Groups group membership to themeco's Cornerstone conditions
   Version: 1.0
   Author: Jeff Kellem
   Author URI: https://slantedhall.com/
   License: BSD-2-Clause
	License URI: http://opensource.org/licenses/BSD-2-Clause

   	Copyright 2023 Jeff Kellem.
*/


class ASMAC_Cornerstone_Groups_Condition {

	protected static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new ASMAC_Cornerstone_Groups_Condition();
		}
 		return self::$instance;
	}

	public function setup() {
		add_filter( 'cs_condition_contexts', [$this, 'condition_contexts'] );
		// need to change cs_assignment_contexts if need to be different, later
		// right now, just using user is member of. Similar to built-in global conditions and assignments in Cornerstone.
		add_filter( 'cs_assignment_contexts', [$this, 'condition_contexts'] );
		add_filter( 'cs_condition_rule_groups_is_member', [ $this, 'condition_rule_groups_is_member' ], 10, 2 );
	}

	public function condition_contexts( $contexts ) {
		$contexts['labels']['groups'] = __( 'Groups', 'groups' );
		$contexts['controls']['groups'] = $this->assignment_and_condition_contexts_groups();

		return $contexts;
	}

	public function assignment_and_condition_contexts_groups() {
		return [
			[
				'key'	 => 'groups:is-member',
				'label'	 => __('Current User Groups Membership', 'groups'),	// different __() FIXME:reword and use different group
				'toggle' => ['type' => 'boolean'],
				'criteria' => [
					'type'	=> 'select',
					'choices' => $this::get_groups_options()
				]
			]
		];
	}

	public function get_groups_options() {
		$groups  = Groups_Group::get_groups(
			array(
				'order_by' => 'name',
				'order'    => 'ASC',
			)
		);
		foreach ( $groups as $group ) {
			$groups_options[] = array(
				'value' => $group->group_id,
				'label' => $group->name ? stripslashes( wp_filter_nohtml_kses( $group->name ) ) : '',
			);
		}
		return $groups_options;
	}

	public function condition_rule_groups_is_member( $result, $args ) {
		list($group_id) = $args;

		$is_a_member = false;
		require_once( ABSPATH . 'wp-includes/pluggable.php' );
		$is_a_member = Groups_User_Group::read( get_current_user_id() , $group_id );

		return $is_a_member;
	}

}

ASMAC_Cornerstone_Groups_Condition::instance()->setup();
