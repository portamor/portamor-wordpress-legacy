<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


	// advanced settings class. adds some advanced features
	class UniteServicesUC{
		
		
		/**
		 * include instagram api
		 */
		public function includeInstagramAPI(){
			
			$pathAPI = GlobalsUC::$pathPlugin."inc_php/framework/instagram/include_insta_api.php";
			require_once($pathAPI);
		}
		
		/**
		 * get instagram data array
		 */
		public function getInstagramSavedDataArray(){
			
			$this->includeInstagramAPI();
			
			$arrData = HelperInstaUC::getInstagramSavedAccessData();
			
			return($arrData);
		}
		
		/**
		 * get instagram data
		 */
		public function getInstagramData($user, $maxItems = null, $isDebug = false){
			
			$arrData = $this->getInstagramSavedDataArray();
			
			$accessToken = UniteFunctionsUC::getVal($arrData, "access_token");
			
			if(empty($accessToken)){
				$api = new InstagramAPINewUC();
			}
			else
				$api = new InstagramAPIOfficialUC();
			
			$response = $api->getItemsData($user,null,null,$maxItems);
						
			return($response);
		}
		
		
	}
