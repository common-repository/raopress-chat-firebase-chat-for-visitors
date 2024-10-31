<?php
namespace RCFV\Admin\Settings;

class RCFV_SubMenu extends RCFV_Menu {

	function __construct( $options, RCFV_Menu $parent ){
		parent::__construct( $options );

		$this->parent_id = $parent->settings_id;
	}

}