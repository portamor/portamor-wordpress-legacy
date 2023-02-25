<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('UNLIMITED_ELEMENTS_INC') or die ('restricted aceess');

class UniteCreatorWooIntegrate{
	
	const POST_TYPE_PRODUCT = "product";
	const PRODUCT_TYPE_VARIABLE = "variable";
	
	private $currency;
	private $currencySymbol;
	private $urlCheckout;
	private $urlCart;
	private $urlSite;
	private $urlCurrentPage;
	private $optionIncludingTax;
	
	private $isInited = false;
	
	private static $instance;
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->init();
	}

	/**
	 * this action should be run inside the product from the widget editor
	 */
	public static function onInsideEditorWooProduct($productID){
		
		if(self::isWooActive() == false)
			return(false);
			
		if(is_numeric($productID) == false)
			return(false);
		
		if(empty($productID))
			return(false);
		
		//run advanced product labels
		if(class_exists("BeRocket_products_label")){
			do_action('berocket_apl_set_label', true, $productID);
		}
			
		
	}
	
	/**
	 * init actions on start, run on "plugins_loaded" filter
	 */
	public static function initActions(){
		
		add_action("ue_woocommerce_product_integrations", array("UniteCreatorWooIntegrate", "onInsideEditorWooProduct"), 10, 1);
		
	}
	
	
	/**
	 * init if not inited
	 */
	private function init(){
		
		if(self::isWooActive() == false)
			return(false);
		
		if($this->isInited == true)
			return(false);
			
		//init
		$this->optionIncludingTax = get_option( 'woocommerce_tax_display_shop' );
		$this->currency = get_woocommerce_currency();
    	$this->currencySymbol = get_woocommerce_currency_symbol($this->currency);
    	$this->urlCheckout = wc_get_checkout_url();
    	$this->urlCart = wc_get_cart_url();
    	$this->urlSite = home_url();
		$this->urlCurrentPage = UniteFunctionsWPUC::getUrlCurrentPage();
    	    	
    	$this->isInited = true;
		
    	/*
		global $wp;
		echo home_url($wp->request);
    	*/    	
    	
	}
	
	
	/**
	 * return if acf plugin activated
	 */
	public static function isWooActive(){
		
		if(class_exists('WooCommerce'))
			return(true);
		
		return(false);
	}
	
	/**
	 * check and init instance
	 */
	public static function getInstance(){
		
		if(empty(self::$instance))
			self::$instance = new UniteCreatorWooIntegrate();
		
		
		return(self::$instance);
	}
	
	/**
	 * add to cart for variation
	 */
	private function addAddToCartForVariation($arrVariation){
		
		$variationID = UniteFunctionsUC::getVal($arrVariation, "variation_id");
		$sku = UniteFunctionsUC::getVal($arrVariation, "sku");
		
		$params = "add-to-cart={$variationID}";
		
		$urlAddCart = UniteFunctionsUC::addUrlParams($this->urlCurrentPage, $params);
		
    	$arrVariation["link_addcart_cart"] = UniteFunctionsUC::addUrlParams($this->urlCart, $params);
    	$arrVariation["link_addcart_checkout"] = UniteFunctionsUC::addUrlParams($this->urlCheckout, $params);
		
    	//add html ajax add to cart
    	$addCartAttributes = "href=\"{$urlAddCart}\" data-quantity=\"1\" class=\"uc-button-addcart product_type_simple add_to_cart_button ajax_add_to_cart\" data-product_id=\"{$variationID}\" data-product_sku=\"{$sku}\" rel=\"nofollow\"";
    	
    	$arrVariation["addcart_ajax_attributes"] = $addCartAttributes;
    	
    	return($arrVariation);
	}
	
	/**
	 * add add to cart data
	 */
	private function addAddToCartData($arrProduct, $productID, $productSku){
		
		$params = "add-to-cart={$productID}";
		
		$urlAddCart = UniteFunctionsUC::addUrlParams($this->urlCurrentPage, $params);
    	$type = UniteFunctionsUC::getVal($arrProduct, "woo_type");
		
    	$arrProduct["woo_link_addcart_cart"] = UniteFunctionsUC::addUrlParams($this->urlCart, $params);
    	$arrProduct["woo_link_addcart_checkout"] = UniteFunctionsUC::addUrlParams($this->urlCheckout, $params);
    	    	
    	//add html ajax add to cart
    	$addCartAttributes = "href=\"{$urlAddCart}\" data-quantity=\"1\" class=\"uc-button-addcart product_type_simple add_to_cart_button ajax_add_to_cart\" data-product_id=\"{$productID}\" data-product_sku=\"{$productSku}\" rel=\"nofollow\"";
		
    	if($type == self::PRODUCT_TYPE_VARIABLE){
    		
    		$urlProduct = get_permalink($productID);
    		
    		$addCartAttributes = "href=\"{$urlProduct}\" class=\"uc-button-addcart\" ";
    	}
    	
    	$arrProduct["woo_addcart_ajax_attributes"] = $addCartAttributes;
    	
		return($arrProduct);
	}
	
	/**
	 * get child product
	 */
	public function getChildProducts($productID){
		
		$productID = (int)$productID;
		
		if(empty($productID))
			return(array());
		
    	$objInfo = wc_get_product($productID);
    	if(empty($objInfo))
    		return(array());
    	
		$type = $objInfo->get_type();
    	    	
		if($type !== "grouped")
			return(array());
    	
		$arrChildren = $objInfo->get_children();
		
		
		if(empty($arrChildren))
			return(array());
			
		return($arrChildren);
	}
	
	
	/**
	 * add from/to prices to variable product
	 */
	private function addPricesFromTo($arrProduct, $arrPrices, $objProduct){
		
		if(empty($arrPrices))
			return($arrProduct);
		
		foreach($arrPrices as $key=>$arrPriceNumbers){
			
			if(empty($arrPriceNumbers)){
				$arrProduct["woo_".$key."_from"] = 0;
				$arrProduct["woo_".$key."_to"] = 0;				
				continue;
			}
			
			$from = array_shift($arrPriceNumbers);
			if(empty($arrPriceNumbers))
				$to = $from;
			else
				$to = $arrPriceNumbers[count($arrPriceNumbers) - 1];
			
			$from = (float)$from;
			$to = (float)$to;
						
			$from = $this->modifyPrice($from, $objProduct);
			$to = $this->modifyPrice($to, $objProduct);
			
			$arrProduct["woo_".$key."_from"] = $from;
			$arrProduct["woo_".$key."_to"] = $to;
		}
		
		//check and clear sale prices
		
		$regularPriceFrom = UniteFunctionsUC::getVal($arrProduct, "woo_regular_price_from");
		$regularPriceTo = UniteFunctionsUC::getVal($arrProduct, "woo_regular_price_to");
		
		$salePriceFrom = UniteFunctionsUC::getVal($arrProduct, "woo_sale_price_from");
		
		if($regularPriceFrom === $salePriceFrom){
			$arrProduct["woo_sale_price_from"] = null;
			$arrProduct["woo_sale_price_to"] = null;
		}else{
			
			$regularPriceFrom = $this->modifyPrice($regularPriceFrom, $objProduct);
			$regularPriceTo = $this->modifyPrice($regularPriceTo, $objProduct);
			
			$arrProduct["woo_regular_price_from"] = $regularPriceFrom;
			$arrProduct["woo_regular_price_to"] = $regularPriceTo;
		}
				
		return($arrProduct);
	}
	
	/**
	 * get array of property names
	 */
	private function getArrPropertyNames($prefix = "", $isAddVariableData = false){
		
    	$arrProperties = array(
    		$prefix."sku",
    		$prefix."price",
    		$prefix."regular_price",
    		$prefix."sale_price",
    		$prefix."stock_quantity",
    		$prefix."stock_status",
    		$prefix."weight",
    		$prefix."length",
    		$prefix."width",
    		$prefix."height",
    		$prefix."average_rating",
    		$prefix."review_count"
    	);
		
    	if($isAddVariableData == false)
    		return($arrProperties);
    	
    	$arrVariable = array(
    		$prefix."regular_price_from",
    		$prefix."regular_price_to",
    		$prefix."sale_price_from",
    		$prefix."sale_price_to",
    		$prefix."price_from",
    		$prefix."price_to"
    	);
		
    	array_splice($arrProperties, 4, 0, $arrVariable);
    	
    	return($arrProperties);
	}
	
	/**
	 * get attribute simple title
	 */
	private function getVariationAttributeTitle($slug, $value, $arrTitles){
		
		$key = $slug."_".$value;
		
		$title = UniteFunctionsUC::getVal($arrTitles, $key);
		
		if(empty($title))
			$title = UniteFunctionsUC::getVal($arrTitles, $slug);
		
		//if not found - return simple text
		if(empty($title)){
			
			$slug = str_replace("attribute_", "", $slug);
			
			$title = array("attr"=>$slug,"title"=>$value);
			
			return($title);
		}
			
		if(is_array($title))
			return($title);
			
		//string type
		
		$slug = str_replace("attribute_", "", $slug);
		
		$arrTitle = array("attr"=>$title,"title"=>$value);
		
		return($arrTitle);
	}
	
	/**
	 * add titles for variation
	 */
	private function addTitlesForVariation($arrVariation, $arrAttributeTitles){
		
		$simpleTitle = "";
		$titleKey = "";
		$arrTitleParts = array();
		
		$arrAttributes = UniteFunctionsUC::getVal($arrVariation, "attributes");
		
		if(empty($arrAttributes))
			return($arrVariation);
		
		foreach($arrAttributes as $slug=>$value){
			
			$arrTitle = $this->getVariationAttributeTitle($slug, $value, $arrAttributeTitles);
			
			$attrTitle = UniteFunctionsUC::getVal($arrTitle, "attr");
			$valueTitle = UniteFunctionsUC::getVal($arrTitle, "title");
			
			if(!empty($simpleTitle))
				$simpleTitle .= ", ";
			
			$arrTitleParts[] = $arrTitle;
			
			$simpleTitle .= $attrTitle." - ".$valueTitle;
						
			//make the key
			$value = strtolower($value);
			$slug = str_replace("attribute_", "", $slug);
			
			if(!empty($titleKey))
				$titleKey .= "_";
			
			$titleKey .= $slug."_".$value;
		}
		
		$arrVariation["title"] = $simpleTitle;
		$arrVariation["title_key"] = $titleKey;
		$arrVariation["title_parts"] = $arrTitleParts;
		
		return($arrVariation);
	}
	
	
	/**
	 * modify variation for output
	 */
	private function modifyVariationForOutput($arrVariation, $arrAttributeTitles){

		//add links
		
		$arrVariation = $this->addAddToCartForVariation($arrVariation);
		
		//add titles
		
		$arrVariation = $this->addTitlesForVariation($arrVariation, $arrAttributeTitles);
		
		return($arrVariation);
	}
	
	
	/**
	 * get attributes titles - in case that they are taxonomies
	 */
	private function getAttributeTitles_simple($arrOutput, $objAttribute){
				
		$name = $objAttribute->get_name();
		
		$slug = "attribute_".strtolower($name);
		
		$arrOutput[$slug] = $name;
		
		return($arrOutput);
	}
	
	
	/**
	 * get attributes titles - in case that they are taxonomies
	 */
	private function getAttributeTitles_tax($arrOutput, $objAttribute, $product){
			
			$attribute_taxonomy = $objAttribute->get_taxonomy_object();
			$attribute_values   = wc_get_product_terms( $product->get_id(), $objAttribute->get_name(), array( 'fields' => 'all' ) );
			
			$attribute_taxonomy = (array)$attribute_taxonomy;
			
			if(empty($attribute_values))
				return($arrOutput);
			
			$attributeLabel = UniteFunctionsUC::getVal($attribute_taxonomy, "attribute_label");
						
			foreach($attribute_values as $term){
				
				$slug = $term->slug;
				$taxonomy = $term->taxonomy;
				
				$name = "attribute_".$taxonomy."_".$slug;
				
				$termTitle = $term->name;
				
				$title = array("attr"=>$attributeLabel,"title"=>$termTitle);
				
				$arrOutput[$name] = $title;
				
			}	//foreach terms
		
		return($arrOutput);
	}
	
	/**
	 * combine by name
	 */
	private function combineAttributesByName($arrAttributes){
		
		if(empty($arrAttributes))
			return($arrAttributes);
		
		$arrCombined = array();
			
		foreach($arrAttributes as $name=>$arr){
			
			$name = UniteFunctionsUC::getVal($arr, "attr");
			$value = UniteFunctionsUC::getVal($arr, "title");
			
			$arrAttribute = UniteFunctionsUC::getVal($arrCombined, $name);
			
			if(empty($arrAttribute))
				$arrAttribute = array();
				
			$arrAttribute[] = $value;
			
			$arrCombined[$name] = $arrAttribute;
		}
		
		return($arrCombined);
	}
	
	
	
	/**
	 * get product attribute names
	 * 
	 */
	private function getProductAttributeNames($product){
		
		$arrAttributes = $product->get_attributes();
		
		$arrOutput = array();
		
		foreach($arrAttributes as $objAttribute){
			
			$isTax = $objAttribute->is_taxonomy();
			
			if($isTax == true)
				$arrOutput = $this->getAttributeTitles_tax($arrOutput, $objAttribute, $product);
			else
				$arrOutput = $this->getAttributeTitles_simple($arrOutput, $objAttribute, $product);
			
		}	//foreach attributes
				
		return($arrOutput);
	}
	
	/**
	 * convert combined attributes array to text
	 */
	private function convertCombinedAttributesToText($arrCombined, $sap = ":", $sapValues = ","){
		
		$arrText = array();
		
		foreach($arrCombined as $name=>$arrValues){
				
			$strValues = implode($sapValues, $arrValues);
			
			$text = "{$name}{$sap} $strValues";
			
			$arrText[] = $text;
		}
		
		return($arrText);
	}
	
	
	/**
	 * get product attributes
	 */
	public function getProductAttributes($productID){
		
		if(function_exists("wc_get_product") == false)
			return(array());
		
		$product = wc_get_product($productID);
		
		if(empty($product))
			return(array());
		
		$arrAttributeTitles = $this->getProductAttributeNames($product);
		
		$arrCombined = $this->combineAttributesByName($arrAttributeTitles);
		
		if(empty($arrCombined))
			return($arrCombined);
		
		$arrText = $this->convertCombinedAttributesToText($arrCombined);

		return($arrText);
	}
	
	
	/**
	 * get variations array
	 */
	public function getProductVariations($productID){
		
		if(function_exists("wc_get_product") == false)
			return(array());
		
		$product = wc_get_product($productID);
		
		if(empty($product))
			return(array());
		
		$type = $product->get_type();
			
		if($type != "variable")
			return(array());

		$variations = $product->get_available_variations();			
				
		if(empty($variations) || is_array($variations) == false)
			return(array());
		
		$arrAttributeTitles = $this->getProductAttributeNames($product);
		
		//add add to cart links
		foreach($variations as $key=>$arrVariation){
			
			$arrVariation = $this->modifyVariationForOutput($arrVariation, $arrAttributeTitles);
			
			$variations[$key] = $arrVariation;
		}
					
		return($variations);
	}
	
	/**
	 * get first gallery image id
	 */
	public function getFirstGalleryImageID($productID){
		
		if(function_exists("wc_get_product") == false)
			return(array());
		
		$product = wc_get_product($productID);
		
		if(empty($product))
			return(array());
				
		$arrAttachmentIDs = $product->get_gallery_image_ids();
		
		if(empty($arrAttachmentIDs))
			return(null);

		$firstID = $arrAttachmentIDs[0];
		
		return($firstID);
	}
	
	
	/**
	 * 
	 * get product gallery
	 */
	public function getProductGallery($productID){
		
		if(function_exists("wc_get_product") == false)
			return(array());
		
		$product = wc_get_product($productID);
		
		if(empty($product))
			return(array());
				
		$arrAttachmentIDs = $product->get_gallery_image_ids();
		
		if(empty($arrAttachmentIDs))
			return(array());
			
		$arrImages = array();
		
		foreach($arrAttachmentIDs as $id){
			
			$data = UniteFunctionsWPUC::getAttachmentData($id);
			
			$arrImages[] = $data;
		}
		
		return($arrImages);		
	}
	
	/**
	 * modify price
	 */
	private function modifyPrice($price, $product){
		
		if(empty($price))
			return($price);
		
		if($this->optionIncludingTax == "incl"){
			$price = wc_get_price_including_tax(
			$product,
			array(
				'qty'   => 1,
				'price' => $price,
			));
		}
		else{
			$price = wc_get_price_excluding_tax(
			$product,
			array(
				'qty'   => 1,
				'price' => $price,
			));
		}
		
		
		return($price);
	}
	
	
	/**
	 * get product data
	 */
	private function getProductData($productID){
		
		if(function_exists("wc_get_product") == false)
			return(null);
		
		//wc_get_ac
    	$objInfo = wc_get_product($productID);
		
    	if(empty($objInfo))
    		return(null);
				
    	$arrData = $objInfo->get_data();
		$type = $objInfo->get_type();
    	
		//dmp($arrData);
		
    	$arrProperties = $this->getArrPropertyNames();
    	
    	$productSku = UniteFunctionsUC::getVal($arrData, "sku");
    	
    	$salePrice = UniteFunctionsUC::getVal($arrData, "sale_price");
    	$regularPrice = UniteFunctionsUC::getVal($arrData, "regular_price");
    	$price = UniteFunctionsUC::getVal($arrData, "price");
    	
    	$price = apply_filters("woocommerce_product_get_price", $price, $objInfo);
    	$salePrice = apply_filters("woocommerce_product_get_sale_price", $salePrice, $objInfo);
    	$regularPrice = apply_filters("woocommerce_product_get_regular_price", $regularPrice, $objInfo);
    	
    	$salePrice = $this->modifyPrice($salePrice, $objInfo);
    	$regularPrice = $this->modifyPrice($regularPrice, $objInfo);
    	$price = $this->modifyPrice($price, $objInfo);
    	
    	$priceNoTax = wc_get_price_excluding_tax($objInfo);
    	$priceWithTax = wc_get_price_including_tax($objInfo);
    	    	
    	if(empty($regularPrice) && !empty($price))
    		$regularPrice = $price;
    	
    	$arrData["regular_price"] = $regularPrice;
    	$arrData["price"] = $price;
    	$arrData["sale_price"] = $salePrice;
    	
    	
    	$arrProduct = array();
    	
    	$arrProduct["woo_type"] = $type;
    	    	
    	foreach($arrProperties as $propertyName){
    		
    		$value = UniteFunctionsUC::getVal($arrData, $propertyName);
    		if(is_array($value) == true)
    			continue;
    		    		
    		$arrProduct["woo_".$propertyName] = $value;    		
    	}
		
    	//add the tax related variables
    	$arrProduct["woo_price_notax"] = $priceNoTax;    		
    	$arrProduct["woo_price_withtax"] = $priceWithTax;    		
    	
    	
    	//make the rating stars array
    	$arrWooStars = array();
    	$rating = UniteFunctionsUC::getVal($arrData, "average_rating");
    	$rating = floatval($rating);
    	
    	$arrWooStars = HelperHtmlUC::getRatingArray($rating);
    	$arrProduct["woo_rating_stars"] = $arrWooStars;
    	
    	//add prices of variations
    	
    	if($type == self::PRODUCT_TYPE_VARIABLE){
    		
    		$arrPrices = $objInfo->get_variation_prices();
    		
    		$arrProduct = $this->addPricesFromTo($arrProduct, $arrPrices, $objInfo);
    		
    		$arrProduct["woo_price"] = $arrProduct["woo_price_from"];
    		$arrProduct["woo_regular_price"] = $arrProduct["woo_regular_price_from"];
    		$arrProduct["woo_sale_price"] = $arrProduct["woo_sale_price_from"];
    	}
    	
    	$finalPrice = UniteFunctionsUC::getVal($arrProduct, "woo_price");
    	$regularPrice = UniteFunctionsUC::getVal($arrProduct, "woo_regular_price");
    	
    	//empty the sale price, if the final price equal regular price
    	
    	if($finalPrice == $regularPrice)
    		$arrProduct["woo_sale_price"] = "";
    	    	
    	//count the discout price
    	
    	$discountPercent = 0;
    	if(!empty($finalPrice) && $finalPrice < $regularPrice){
    		
    		$discountPercent = ($regularPrice-$finalPrice)/$regularPrice*100;
    		$discountPercent = round($discountPercent);
    	}
    	
    	$arrProduct["woo_discount_percent"] = $discountPercent;
    	
    	//add currency
    	$arrProduct["woo_currency"] = $this->currency;
    	$arrProduct["woo_currency_symbol"] = $this->currencySymbol;
		
    	//put add to cart link
    	$arrProduct = $this->addAddToCartData($arrProduct, $productID, $productSku);
    	
    	    	
    	return($arrProduct);
	}
	
	
	/**
	 * get woocommerce keys without post
	 */
	private function getWooProductKeysNoPost(){
		
		$arrProperties = $this->getArrPropertyNames("woo_", true);
		
		$arrKeys = array();
		$arrKeys[] = "woo_type";
		
		$arrKeys += $arrProperties;
		
    	$arrKeys[] = "woo_price_notax";
    	$arrKeys[] = "woo_price_withtax";
    	$arrKeys[] = "woo_rating_stars";
		$arrKeys[] = "woo_discount_percent";
    	$arrKeys[] = "woo_currency";
    	$arrKeys[] = "woo_currency_symbol";
    	$arrKeys[] = "woo_link_addcart_cart";
    	$arrKeys[] = "woo_link_addcart_checkout";
    	$arrKeys[] = "woo_addcart_ajax_attributes";
    	
		return($arrKeys);
	}
	
	
	/**
	 * get woo data by type
	 */
	private function getWooData($postType, $postID){
		
		if(self::isWooActive() == false)
			return(null);
		
		switch($postType){
			case self::POST_TYPE_PRODUCT:
				$arrData = $this->getProductData($postID);
				
				return($arrData);
			break;
			default:
				return(null);
			break;
		}
		
	}
	
	/**
	 * get the endpoints - url's
	 */
	public static function getWooEndpoint($type){
		
		switch($type){
			case "cart":
				$url = wc_get_cart_url();
			break;
			case "checkout":
				$url = wc_get_checkout_url();
			break;
			case "myaccount":
				
				$myAccountID = get_option( 'woocommerce_myaccount_page_id' );		
				$url = "";
				if(!empty($myAccountID))
					$url = get_permalink($myAccountID);
			break;
			case "shop":
				
				$url = "";
				$shopPageID = wc_get_page_id($type);
				
				if(!empty($shopPageID) && $shopPageID != -1)
					$url = get_permalink($shopPageID);
				
			break;
			default:
				$type = esc_html($type);
				UniteFunctionsUC::throwError("get_woo_endpoints error: wrong endpoint type: $type, 
				allowed endpoints are: cart, checkout, myaccount, shop");
			break;
		}
		
		return($url);
	}
	
	/**
	 * get woo commerce data by type
	 */
	public static function getWooDataByType($postType, $postID){
		
		$objInstance = self::getInstance();
		
		$response = $objInstance->getWooData($postType, $postID);
		
		return($response);
	}
	
	/**
	 * get keys by post id
	 */
	private function getWooKeys($postID){
		
		if(self::isWooActive() == false)
			return(null);
		
		$post = get_post($postID);
		if(empty($post))
			return(null);
		
		$postType = $post->post_type;
		
		$arrData = self::getWooDataByType($postType, $postID);
		if(empty($arrData))
			return(false);
		
		$arrKeys = array_keys($arrData);
		
		
		return($arrKeys);
		
	}
	
	
	/**
	 * get woo keys by post id
	 */
	public static function getWooKeysByPostID($postID){
		
		$instance = self::getInstance();
		
		$response = $instance->getWooKeys($postID);
		
		return($response);
	}
	
	/**
	 * get woo keys without post id
	 */
	public static function getWooKeysNoPost(){
		
		$instance = self::getInstance();
		
		$response = $instance->getWooProductKeysNoPost();
		
		return($response);
	}
	
	
	/**
	 * get default number of posts in catalog
	 */
	public static function getDefaultCatalogNumPosts(){
		
		if(function_exists("wc_get_default_products_per_row") == false)
			return(16);
		
		$numProducts = wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();
		
		return($numProducts);
	}
	
	
	/**
	 * put filters js
	 */
	private function putHtmlFiltersJS(){
		
		UniteProviderFunctionsUC::addjQueryInclude();
		
		$urlScriptFile = GlobalsUC::$url_assets_internal."js/uc_woocommerce.js";
		
		HelperUC::addScriptAbsoluteUrl($urlScriptFile, "uc_woo_integrate");
	}
	
	
	/**
	 * put html filter - order
	 */
	private function putHtmlFilter_order($params){
				
		$arrOptions = array();
		$arrOptions["name"] = __("Product Name","unlimited-elements-for-elementor");
		$arrOptions["price"] = __("Price","unlimited-elements-for-elementor");
				
		$name = "uc_order";
		
		$value = UniteFunctionsUC::getPostGetVariable($name, "", UniteFunctionsUC::SANITIZE_KEY);
		
		
		$htmlSelect = HelperHtmlUC::getHTMLSelect($arrOptions, $value, "name='{$name}' class='uc-woo-filter uc-woo-filter-order'", true);
		
		?>
		<form class="uc-woocommerce-ordering" method="get">
			
			<?php echo $htmlSelect?>
			
		</form>
		
		<?php 
		
		$this->putHtmlFiltersJS();
	}
	
	
	/**
	 * put html filter
	 */
	public function putHtmlFilter($filterName, $params = null){
		
		switch($filterName){
			case "order":
				$this->putHtmlFilter_order($params);
			break;
			default:
				UniteFunctionsUC::throwError("putWooFilter error: filter $filterName not exists");
			break;
		}
		
	}
	
	
	
	/**
	 * get product ids from current post content
	 */
	public function getProductIDsFromCurrentPostContent(){
		
		if(is_singular() == false)
			return(false);
		
		$post = get_post();
		
		if(empty($post))
			return(false);
		
		$content = $post->post_content;
		
		if(empty($content))
			return(false);
		
		$arrLinks = UniteFunctionsUC::parseHTMLGetLinks($content);
		
		if(empty($arrLinks))
			return(false);
		
		$arrPostIDs = array();
		
		foreach($arrLinks as $link){
			
			$postID = url_to_postid($link);
			
			if(empty($postID))
				continue;
				
			$arrPostIDs[] = $postID;
		}
		
		return($arrPostIDs);
	}

	/**
	 * get product id's in cart
	 */
	public function getCartProductIDs(){
		
		$isActive = self::isWooActive();
		if($isActive == false)
			return(array());
		
		$arrCartItems = WC()->cart->get_cart();

		$arrIDsAssoc = array();
		
		foreach($arrCartItems as $item){
		
			$productID = UniteFunctionsUC::getVal($item, "product_id");
			
			if(empty($productID))
				continue;
			
			$arrIDsAssoc[$productID] = true;			
		}
		
		$arrIDs = array_keys($arrIDsAssoc);
		
		return($arrIDs);
	}
	
	
	/**
	 * get related product id's from selected to cart products
	 */
	public function getRelatedProductsFromCart($limit,$arrPostsNotIn = array()){
				
		$isActive = self::isWooActive();
		if($isActive == false)
			return(array());
			
		$arrIDs = $this->getCartProductIDs();
		
		if(empty($arrIDs))
			return(array());
		
		$arrRelatedTotal = array();
		
		$arrPostsNotIn = array_merge($arrPostsNotIn,$arrIDs);
				
		foreach($arrIDs as $productID){
			
			$arrRelated = wc_get_related_products($productID, $limit, $arrPostsNotIn);
			
			$arrRelatedTotal = array_merge($arrRelatedTotal, $arrRelated);
		}
		
		if(empty($arrRelatedTotal))
			return(array());
		
		$arrRelatedTotal = array_unique($arrRelatedTotal);			
		
		array_rand($arrRelatedTotal);
		
		if(count($arrRelatedTotal) > $limit)
			$arrRelatedTotal = array_slice($arrRelatedTotal, 0, $limit);
		
		return($arrRelatedTotal);
	}
	
	
}