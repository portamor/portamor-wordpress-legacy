<?php

//no direct accees
defined ('UNLIMITED_ELEMENTS_INC') or die ('restricted aceess');

require HelperUC::getPathViewObject("addons_view.class");


class UniteCreatorAddonsElementorView extends UniteCreatorAddonsView{
	
	protected $showButtons = true;
	protected $showHeader = false;
	protected $pluginTitle = null;
	
	
	/**
	 * get header text
	 * @return unknown
	 */
	protected function getHeaderText(){
				
		$headerTitle = esc_html__("Manage Widgets for Elementor", "unlimited-elements-for-elementor");
		
		return($headerTitle);
	}
	
	
	/**
	 * addons view provider
	 */
	public function __construct(){
		
		$this->addonType = GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR;
		$this->product = GlobalsUnlimitedElements::PLUGIN_NAME;
		$this->pluginTitle = GlobalsUnlimitedElements::PLUGIN_TITLE;
		
		
		parent::__construct();
	}
	
	
}


new UniteCreatorAddonsElementorView();
