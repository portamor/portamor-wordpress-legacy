<?php

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');
	
   class UniteProviderAdminUC extends UniteCreatorAdmin{
   		
   		private $dbVersion = "2";		//used for upgrade db on plugin update
	   	private static $arrMenuPages = array();
	   	private static $arrSubMenuPages = array();
	   	protected $capability = "manage_options";
	   	
	   	public static $isUserHasCapability = true;
	   	public static $adminTitle = null;
	   	
   		protected $mainFilepath;
   		protected $pluginFilebase;
   		private $dirPlugin;
   		protected $pluginName;
   		protected $arrAllowedViews = array();
   		
	   	private static $t;
	   	
	   	const ACTION_ADMIN_MENU = "admin_menu";
	   	const ACTION_ADMIN_INIT = "admin_init";
	   	const ACTION_ADD_SCRIPTS = "admin_enqueue_scripts";
   		const ACTION_AFTER_SETUP_THEME = "after_setup_theme";
   		const ACTION_PRINT_SCRIPT = "admin_print_footer_scripts";
   		const ACTION_AFTER_SWITCH_THEME = "after_switch_theme";
   		const ACTION_PLUGINS_LOADED = "plugins_loaded";
   		
   		//install addons from this folder in the addon library itself on activate
   		const DIR_INSTALL_ADDONS = "addons_install";	
		
   		protected $coreAddonType;
   		protected $coreAddonsView;
   		
   		protected $textBuy; 
   		protected $linkBuy;
   		protected $pluginTitle;
   		
   		
		/**
		 *
		 * the constructor
		 */
		public function __construct($mainFilepath){
			
			self::$t = $this;
						
			$this->mainFilepath = $mainFilepath;
			
			$mainFilename = basename($mainFilepath);
			
			$pathPlugin = str_replace('\\', "/", GlobalsUC::$pathPlugin);
			
			$dirPlugins = dirname($pathPlugin)."/";
			
			$dirPlugin = str_replace($dirPlugins, "", $pathPlugin);
			$this->dirPlugin = $dirPlugin;
			$this->pluginFilebase = $dirPlugin.$mainFilename;
			
			UniteFunctionsUC::validateNotEmpty($this->coreAddonsView, "core addons view");
			UniteFunctionsUC::validateNotEmpty($this->pluginTitle, "plugin title");
			
			//update globals
			GlobalsUC::$view_default = $this->coreAddonsView;
			GlobalsUC::$defaultAddonType = $this->coreAddonType;
				
			parent::__construct();
			
			$this->init();
		}		

		
		/**
		 * process activate event - install the db (with delta).
		 */
		public function onActivate(){
			
			$this->createTables();
			
			$this->importCurrentThemeAddons();
			
			//import addons that comes in the addon library package
			$this->importPackageAddons();
			
		}
				
		
		/**
		 * after switch theme
		 */
		public function afterSwitchTheme(){
			
			$this->importCurrentThemeAddons();
		}
		
		
		/**
		 * do all actions on theme setup
		 */
		public function onThemeSetup(){
			
		}
		
		
		/**
		 * create the tables if not exists
		 */
		public function createTables($isForce = false){
			
			$this->createTable(GlobalsUC::TABLE_ADDONS_NAME, $isForce);
			$this->createTable(GlobalsUC::TABLE_CATEGORIES_NAME, $isForce);
		}
		
		
		/**
		 *
		 * craete tables
		 */
		public function createTable($tableName, $isForce = false){
		
			global $wpdb;
			
			//if table exists - don't create it.
			$tableRealName = $wpdb->prefix.$tableName;
			if($isForce == false && UniteFunctionsWPUC::isDBTableExists($tableRealName))
				return(false);
			
			$charset_collate = $wpdb->get_charset_collate();
			
			switch($tableName){
				case GlobalsUC::TABLE_LAYOUTS_NAME:
					$sql = "CREATE TABLE " .$tableRealName ." (
					id int(9) NOT NULL AUTO_INCREMENT,
					title varchar(255) NOT NULL,
					layout_data mediumtext,					
					ordering int not NULL,
					catid int not NULL,
					layout_type varchar(60),
					relate_id int not NULL,
					parent_id int not NULL,
					params text NOT NULL,
					PRIMARY KEY (id)
					)$charset_collate;";
				break;
				case GlobalsUC::TABLE_CATEGORIES_NAME:
					$sql = "CREATE TABLE " .$tableRealName ." (
					id int(9) NOT NULL AUTO_INCREMENT,
					title varchar(255) NOT NULL,
					alias varchar(255),
					ordering int not NULL,
					params text NOT NULL,
					type tinytext,
					parent_id int(9),
					PRIMARY KEY (id)
					)$charset_collate;";
					break;
				
				case GlobalsUC::TABLE_ADDONS_NAME:
					$sql = "CREATE TABLE " .$tableRealName ." (
					id int(9) NOT NULL AUTO_INCREMENT,
					title varchar(255),
					name varchar(128),
					alias varchar(128),
					addontype varchar(128),
					description text,
					ordering int not NULL,
					templates mediumtext,
					config mediumtext,
					catid int,
					is_active tinyint,
					test_slot1 text,	
					test_slot2 text,	
					test_slot3 text,
					PRIMARY KEY (id)
					)$charset_collate;";
					break;
				default:
					UniteFunctionsMeg::throwError("table: $tableName not found");
				break;
			}
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
		}
		
		/**
		 * check db upgrae from admin_init - for plugin update
		 */
		private function checkDBUpgrade(){
			
			$optionDBVersion = "unitecreator_db_version";
			$savedDBVersion = get_option($optionDBVersion);
			
			if($savedDBVersion != $this->dbVersion){
				
				$this->createTables(true);
				
				update_option($optionDBVersion, $this->dbVersion);
			}
			
		}
		
		/**
		 *
		 * add ajax back end callback, on some action to some function.
		 */
		protected function addActionAjax($ajaxAction, $eventFunction){
			
			$this->addAction('wp_ajax_'.GlobalsUC::PLUGIN_NAME."_".$ajaxAction, $eventFunction, true);
			$this->addAction('wp_ajax_nopriv_'.GlobalsUC::PLUGIN_NAME."_".$ajaxAction, $eventFunction, true);
			
		}
		
		
		/**
		 *
		 * register the "onActivate" event
		 */
		protected function addEvent_onActivate($eventFunc = "onActivate"){
						
			register_activation_hook( $this->mainFilepath, array($this, $eventFunc) );
		}
		
		/**
		 *
		 * tells if the the current plugin opened is this plugin or not
		 * in the admin side.
		 */
		protected function isInsidePlugin(){
			
			$page = UniteFunctionsUC::getGetVar("page","",UniteFunctionsUC::SANITIZE_KEY);
			
			$isPageMaster = (strpos($page, "unlimited-elements-master") !== false);
			
			if($page == "addon-library-layouts_layout" || $isPageMaster == true)
				return(true);
				
			if($page == $this->pluginName || 
			   strpos($page, $this->pluginName."_") !== false || 
			   strpos($page, $this->pluginName."-") !== false )
			return(true);
			
			
			return(false);
		}
		
				
		/**
		 *
		 * add some wordpress action
		 */
		protected function addAction($action, $eventFunction, $isStatic = false, $numArgs = 1, $priority = 10){
			
			if($isStatic == false){
				add_action( $action, array($this, $eventFunction) ,$priority,$numArgs);
			}else{
				
				add_action( $action, array(self::$t, $eventFunction) ,$priority, $numArgs);
			}
											
		}
		
		
		/**
		 * add local filter
		 */
		protected function addLocalFilter($tag, $func, $numArgs=1){
			
			add_filter($tag, array($this, $func) , 10 , $numArgs);
		}
		
		
		/**
		 *
		 * validate admin permissions, if no pemissions - exit
		 */
		protected function validateAdminPermissions(){
			
			if(UniteFunctionsWPUC::isAdminPermissions() == false){
				echo "access denied, no admin permissions";
				return(false);
			}
			
		}
		
				
		/**
		 *
		 * admin main page function.
		 */
		public function adminPages(){
			
			GlobalsUC::$alterViewHeaderPrefix = $this->pluginTitle;
			
			if(!empty($this->arrAllowedViews) && in_array(self::$view, $this->arrAllowedViews) == false){
				
				echo esc_html__("this view not allowed in the plugin", "unlimited-elements-for-elementor");
				return(false);
			}
			
			$this->createTables();
			
			parent::adminPages();
			
		}
		
		
		/**
		 * add scripts to all admin pages
		 */
		public function addScriptsToAllAdminPages(){
			
			HelperUC::addStyleAbsoluteUrl( GlobalsUC::$url_provider."assets/provider_admin.css", "uc_provider_admin");
		}
		
		
		/**
		 * add outside plugin scripts
		 */
		public function onAddOutsideScripts(){
			
			try{
			
			//add outside scripts, only on posts or pages page
			$isPostsPage = UniteFunctionsWPUC::isAdminPostsPage();
						
				$this->addScriptsToAllAdminPages();
			
			}catch(Exception $e){
				
				HelperHtmlUC::outputException($e);
				
			}
		}

		
		/**
		 * print custom scripts
		 */
		public function onPrintFooterScripts(){
			
			HelperProviderUC::onPrintFooterScripts();
			
		}
		
		private static function a_________MENU__________(){}
		
		/**
		 *
		 * add menu page
		 */
		protected function addMenuPage($title,$pageFunctionName,$icon=null,$link=null,$menuSlug=null){
			self::$arrMenuPages[] = array("title"=>$title,"pageFunction"=>$pageFunctionName,"icon"=>$icon,"link"=>$link,"slug"=>$menuSlug);
		}
		
		
		/**
		 *
		 * add sub menu page
		 */
		protected function addSubMenuPage($slug,$title,$pageFunctionName,$realLink = false,$parentSlug = null){
			self::$arrSubMenuPages[] = array("slug"=>$slug,"title"=>$title,"pageFunction"=>$pageFunctionName,"realLink"=>$realLink,"parentSlug"=>$parentSlug);
		}

		
		/**
		 * put admin menu actually
		 */
		public function addAdminMenu_putActually($arrMenuPages, $arrSubMenuPages){
			
			global $menu, $submenu;
			
			$cleanTitle = false;
			$mainMenuSlug = null;
			
			if(empty($arrMenuPages))
				return(false);
			
			foreach($arrMenuPages as $mainMenu){
					
				$title = $mainMenu["title"];
				$pageFunctionName = $mainMenu["pageFunction"];
				$pluginName = UniteFunctionsUC::getVal($mainMenu, "plugin_name");
				
				$menuSlug = UniteFunctionsUC::getVal($mainMenu, "slug");
				
				if(empty($menuSlug) && !empty($pluginName))
					$menuSlug = $this->pluginName."-".$pluginName;
				
				if(empty($menuSlug))
					$menuSlug = $this->pluginName;
				
				$icon = "";
				if(isset($mainMenu["icon"])) 
					$icon = $mainMenu["icon"];
				
				add_menu_page( $title, $title, $this->capability, $menuSlug, array(self::$t, $pageFunctionName), $icon );
				
				
				$link = $mainMenu["link"];

				$cleanTitle = $title;
				$mainMenuSlug = $menuSlug;
								
				if(!empty($link)){
					$cleanTitle = $title;
					$mainMenuSlug = $link;
					
					$keys = array_keys($menu);
					$lastMainMenuKey = $keys[count($keys)-1];
					$menu[$lastMainMenuKey][2] = $link;
				}
				
			}
			
			if(empty($arrSubMenuPages))
				return(false);
			
			foreach($arrSubMenuPages as $key=>$submenuMenu){
								
				$title = $submenuMenu["title"];
				$pageFunctionName = $submenuMenu["pageFunction"];
				$pluginName = UniteFunctionsUC::getVal($submenuMenu, "plugin_name");
				
				$isRealLink = $submenuMenu["realLink"];
				$parentSlug = $submenuMenu["parentSlug"];
				
				if(empty($parentSlug) && !empty($pluginName))
					$parentSlug = $this->pluginName."-".$pluginName;
				
				if(empty($parentSlug))
					$parentSlug = $this->pluginName;
				
				$slug = $parentSlug."_".$submenuMenu["slug"];
				
				if($key == 0 && $isRealLink == false)
					$slug = $parentSlug;
				
				add_submenu_page($parentSlug, $title, $title, $this->capability, $slug, array(self::$t, $pageFunctionName) );
								
				//switch the link for real link
				if($isRealLink === true && isset($submenu[$parentSlug])){
					$arrMain = $submenu[$parentSlug];
					$keys = array_keys($arrMain);
					$lastKey = $keys[count($keys)-1];
					$arrMain[$lastKey][2] = $submenuMenu["slug"];
					$submenu[$parentSlug] = $arrMain;
				}
				
			}
			
			//clean double submenus
			if(!empty($cleanTitle) && isset($submenu[$mainMenuSlug])){
				$arrSubMenu = $submenu[$mainMenuSlug];
				if($arrSubMenu[0][0] == $cleanTitle)
					unset($submenu[$mainMenuSlug][0]);
			}
			
			
		}
		
		
		/**
		 * add admin menus from the list.
		 */
		public function addAdminMenu(){
			
			
			//add blox menu
			$this->addAdminMenu_putActually(self::$arrMenuPages, self::$arrSubMenuPages);
			
			//add plugins menu
			$arrMenuPages = UniteCreatorAdminWPPluginBase::getArrMenuPages();
			$arrSubMenuPages = UniteCreatorAdminWPPluginBase::getArrSubmenuPages();
			
			$this->addAdminMenu_putActually($arrMenuPages, $arrSubMenuPages);
			
			/*
			global $menu, $submenu;
			dmp($menu);
			dmp($submenu);
			exit();
			*/
		}
				
		
		private static function a_______IMPORT_ADDONS________(){}
		
		
		/**
		 * install addosn from some path
		 */
		protected function installAddonsFromPath($pathAddons, $addonsType = null){
						
			if(empty($addonsType))
				$addonsType = $this->coreAddonType;
			
			if(is_dir($pathAddons) == false)
				return(false);
			
			$exporter = new UniteCreatorExporter();
			$exporter->setMustImportAddonType($addonsType);
			$exporter->importAddonsFromFolder($pathAddons);
						
		}
		
		
		/**
		 * import current theme addons
		 */
		private function importCurrentThemeAddons(){
			
			$pathCurrentTheme = get_template_directory()."/";
			
			$dirAddons = apply_filters("ue_path_theme_addons", GlobalsUC::DIR_THEME_ADDONS);
			
			$pathAddons = $pathCurrentTheme.$dirAddons."/";
			
			$this->installAddonsFromPath($pathAddons);
		}
		
		
		/**
		 * import package addons
		 */
		protected function importPackageAddons(){
			
			$pathAddons = GlobalsUC::$pathPlugin.self::DIR_INSTALL_ADDONS."/";
			
			if(is_dir($pathAddons) == false)
				return(false);
			
			$imported = false;
			
			//install vc addons
			$pathAddonsVC = $pathAddons.$this->coreAddonType."/";
			if(is_dir($pathAddonsVC)){
				$this->installAddonsFromPath($pathAddonsVC, $this->coreAddonType);
				$imported = true;
			}
			
			
			return($imported);
		}
		
		
		/**
		 * set editor permission
		 * in default it's admin menu permission
		 */
		protected function setPermissionEditor(){
			
			$this->capability = "edit_posts";
		}
		
		
		private static function a_____OTHERS____(){}
		
		
		/**
		 * return if creator plugin exists
		 */
		protected function isCreatorPluginExists(){
			
			$arrPlugins = get_plugins();
			
			$pluginName = "addon_library_creator/addon_library_creator.php";
			if(isset($arrPlugins[$pluginName]) == false)
				return(false);
			
			$isActive = is_plugin_active($pluginName);
			
			return($isActive);
						
		}
		
		
		/**
		 * after update plugin
		 * install package addons, then redirect to dashboard
		 */
		private function onAfterUpdatePlugin(){
			
			$isImported = $this->importPackageAddons();
			if($isImported == false)
				return(false);
			
			//redirect to main view
			$urlRedirect = HelperUC::getViewUrl_Default();
			
			dmp("addons installed, redirecting...");
			echo "<script>location.href='$urlRedirect'</script>";
			exit();
			
		}
		
		
		/**
		 * run provider action if exists - only if inside plugin
		 */
		private function runProviderAction(){
			
			$action = UniteFunctionsUC::getGetVar("provider_action", "", UniteFunctionsUC::SANITIZE_KEY);
			if(empty($action))
				return(false);
			
			switch($action){
				case "run_after_update":
					$this->onAfterUpdatePlugin();
				break;
			}
			
		}
		
		
		/**
		 * 
		 * plugin action links
		*/
		public function plugin_action_links( $links ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=' . Settings::PAGE_ID ), __( 'Settings', 'elementor' ) );
			
			array_unshift( $links, $settings_link );
	
			$links['go_pro'] = sprintf( '<a href="%s" target="_blank" class="elementor-plugins-gopro">%s</a>', Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=wp-plugins&utm_campaign=gopro&utm_medium=wp-dash' ), __( 'Go Pro', 'elementor' ) );
			
			return $links;
		}
		
		
		
		/**
		 * create tables if not created on multisite
		 */
		protected function checkMultisiteCreateTables(){
						
			global $wpdb;
			$tablePrefix = $wpdb->prefix;
						
			$option = "addon_library_tables_created_{$tablePrefix}";
			
			$isCreated = get_option($option);
			if($isCreated == true)
				return(true);
			
			$this->createTables();
			
			update_option($option, true);
		}
		
				
		/**
		 * get admin page body
		 */
		private function getAdminPageBody(){
			
			ob_start();
			
			$this->adminPages();
			
			$content = ob_get_contents();
			ob_end_clean();
			
			return($content);
		}
		
		/**
		 * load in blank window html
		 */
		private function loadBlankWindowAdminPage($superClear = false){
			
			if($superClear == true){		//clear all styles
				global $wp_styles;
				if(empty($wp_styles))
					$wp_styles = new WP_Styles();
			
				$wp_styles->queue = array();
				
			}else{		//add wp styles
				
				wp_enqueue_style( 'colors' );
				wp_enqueue_style( 'ie' );
				
				//wp_auth_check_load();
				wp_enqueue_style( 'wp-auth-check' );
				wp_enqueue_script( 'wp-auth-check' );
		
				add_action( 'admin_print_footer_scripts', 'wp_auth_check_html', 5 );
				add_action( 'wp_print_footer_scripts', 'wp_auth_check_html', 5 );
			}
			
			
			self::onAddScripts();
			
			$htmlBody = $this->getAdminPageBody();
			$title = UniteFunctionsWPUC::getAdminTitle(self::$adminTitle);
			
			HelperUC::addStyle("blank_page_preview","uc_blank_page_preview");
			
			$arrCustomStyles = UniteProviderFunctionsUC::getCustomStyles();
			$htmlCustomCssStyles = HelperHtmlUC::getHtmlCustomStyles($arrCustomStyles);
			
			$arrJsCustomScripts = UniteProviderFunctionsUC::getCustomScripts();
			$htmlJSScripts = HelperHtmlUC::getHtmlCustomScripts($arrJsCustomScripts);
			
									
			?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo esc_html($title)?></title>
	<?php 
		
	//put admin styles
	if($superClear == true)
		print_admin_styles();
	else
		do_action("admin_print_styles");
	
	//put admin scripts
	print_head_scripts();
	
	
	if(!empty($htmlCustomCssStyles))
		echo "\n".$htmlCustomCssStyles;
	
	$view = self::$view;
		
	?>
	
</head>

<body class="uc-blank-preview uc-view-<?php echo esc_attr($view)?>">
	<?php echo UniteProviderFunctionsUC::escCombinedHtml($htmlBody)?>
	<?php 	
		echo UniteProviderFunctionsUC::escCombinedHtml($htmlJSScripts);		
		
		if($superClear == true)
			print_footer_scripts();
		else{
			do_action("admin_footer");
			do_action("admin_print_footer_scripts");
		}
	?>
</body>
</html>

		<?php
		
		exit();
		}
		
		
		/**
		 * is layouts builder plugin exists
		 */
		private function isLayoutBuilderPluginExists(){
			
			$objPlugins = new UniteCreatorPlugins();
			$isExists = $objPlugins->isPluginExists("layouts_builder");
			
			return($isExists);
		}
		
		
		/**
		 * copy wp addons from addon library
		 */
		private function checkMigrateFromAddonLibrary(){

			//check if plugin exists
			$isExists = $this->isLayoutBuilderPluginExists();
			
			if($isExists == false)
				return(false);
			
			//check if migrated
			$keyMigrate = "blox_is_migrated_from_addon_library";
			
			$isMigrated = UniteProviderFunctionsUC::getOption($keyMigrate);
						
			if($isMigrated == true)
				return(false);
			
			$objAddons = new UniteCreatorAddons();
			$objAddons->migrateAddonsFromType("wp");
			
			UniteProviderFunctionsUC::updateOption($keyMigrate, true);
						
		}	
		
		
		/**
		 * modify plugin variables if the plugin is not default (blox)
		 */
		protected function modifyPluginVariables(){
			
			if($this->pluginName == GlobalsUC::PLUGIN_NAME)
				return(false);
			
			$pluginName = $this->pluginName;
				
			GlobalsUC::$url_component_admin = admin_url()."admin.php?page={$pluginName}";
			GlobalsUC::$url_component_client = GlobalsUC::$url_component_admin;
			GlobalsUC::$url_component_admin_nowindow = GlobalsUC::$url_component_admin."&ucwindow=blank";
		}
		
		/**
		 * check if user has capability to modify addons
		 */
		private function setUserCapability(){
			
			$data = get_userdata( get_current_user_id() );
 			
			if ( is_object( $data) == false)
				return(false);
			 
			$allCaps = $data->allcaps;
			
			$currentCapability = UniteFunctionsUC::getVal($allCaps, $this->capability);
			
			if(empty($currentCapability))			
				self::$isUserHasCapability = false;
			
		}

		/**
		 * check rankmath with instagram collision
		 */
		public function checkRankmathAjaxCollision(){
			
			if(is_admin() == false)
				return(false);
			
			$isRankmathExists = class_exists("RankMath");
			
			if($isRankmathExists == false)
				return(false);
			
			if(function_exists("is_ajax")){
				
				$isAjax = is_ajax();
	
				if($isAjax == false)
					return(false);
			}
			
			$action = UniteFunctionsUC::getPostGetVariable("action","",UniteFunctionsUC::SANITIZE_KEY);
			
			if($action != "unlimitedelements_ajax_action")
				return(false);
			
			$clientAction = UniteFunctionsUC::getPostGetVariable("client_action","",UniteFunctionsUC::SANITIZE_KEY);
			
			if($clientAction != "save_instagram_connect_data")
				return(false);
				
			if(!isset($_REQUEST["access_token"]))
				return(false);
			
			$arrKeys = UniteFunctionsWPUC::getAllWPActionKeys("admin_init");
			
			$keyToRemove = "";
			
			foreach($arrKeys as $key){
				
				$pos = strpos($key, "process_oauth");
				
				if($pos === false)
					continue;

				if($pos == 32)
					$keyToRemove = $key;
			}

			if(!empty($keyToRemove))
				remove_action("admin_init", $keyToRemove);
			
		}
		
		
		/**
		 * on blank action
		 */
		public function onAdminInit(){
			
			if(is_admin() == false)
				return(true);
						
			GlobalsUC::initAfterAdminInit();
			
			$this->checkDBUpgrade();
			
			$this->checkMigrateFromAddonLibrary();

			$this->setUserCapability();
			
			
			if(self::isInsidePlugin() == false)
				return(true);
							
			if(GlobalsUC::$blankWindowMode == false)
				return(true);
			
			// run blank mode
				
			$isSuperClear = UniteFunctionsUC::getGetVar("superclear", "", UniteFunctionsUC::SANITIZE_KEY);
			$isSuperClear = UniteFunctionsUC::strToBool($isSuperClear);
			
			$this->loadBlankWindowAdminPage($isSuperClear);
			
		}
		
		
		
		/**
		 * add admin menu links, function for override
		 */
		protected function addAdminMenuLinks(){
			/* function for override */
		}
		
		
		/**
		 * set plugin title
		 */
		protected function setPluginTitle(){
		    
		    /* function for override */
		    
		}

		/**
		 * validate init values
		 */
		protected function validateInitValues(){
			
			UniteFunctionsUC::validateNotEmpty($this->pluginName, "plugin name");
			
		}
		
		
		/**
		 * add admin body class
		 */
		public function addAdminBodyClass($classes){
			
			if(empty($classes))
				$classes = "";
			
			$classes .= " unite-view-".self::$view;
				
			return($classes);
		}
		
		/**
		 * modify admin title
		 */
		public function modifyAdminTitle($title){
			
			switch(self::$view){
				case "addon":
					
					$addonID = UniteFunctionsUC::getGetVar("id","",UniteFunctionsUC::SANITIZE_ID);
					
					if(empty($addonID))
						return($title);

					try{
						$addon = new UniteCreatorAddon();
						$addon->initByID($addonID);
					}catch(Exception $e){
						return($title);
					}
					
					$addonTitle = $addon->getTitle();
					
					$title = str_replace("Widgets", $addonTitle." | Edit < Unlimited Elements", $title);
					
				break;
			}
			
			
			return($title);
		}
		
		/**
		 * 
		 * init function
		 */
		protected function init(){
			
			$this->validateInitValues();
			
			UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_BEFORE_ADMIN_INIT);
			
			parent::init();
			
			HelperProviderUC::globalInit();
			
			if(is_multisite() == true)
				$this->checkMultisiteCreateTables();
			
			$this->setPluginTitle();
			
			$this->modifyPluginVariables();
			
			$this->addAdminMenuLinks();
			

			//add internal hook for adding a menu in arrMenus
			$this->addAction(self::ACTION_ADMIN_MENU, "addAdminMenu");
			
			//if not inside plugin don't continue
			if($this->isInsidePlugin() == true){
				$this->addAction(self::ACTION_ADD_SCRIPTS, "onAddScripts", true,1, 9999);
				$this->addLocalFilter("admin_body_class", "addAdminBodyClass");
				$this->addLocalFilter("admin_title", "modifyAdminTitle");
				
			}else{	
				$this->addAction(self::ACTION_ADD_SCRIPTS, "onAddOutsideScripts");
			}
			
			$this->addAction(self::ACTION_PRINT_SCRIPT, "onPrintFooterScripts");
			
			$this->addAction(self::ACTION_AFTER_SWITCH_THEME, "afterSwitchTheme");
			
			$this->addEvent_onActivate();
			
			$this->addActionAjax("ajax_action", "onAjaxAction");
			
			//run provider action if exists (like after update)
			if($this->isInsidePlugin())
				$this->runProviderAction();
			
			add_action("init", array($this,"checkRankmathAjaxCollision"));
			
			$this->addAction("admin_init", "onAdminInit");
			
			
	}

		
		
	}

?>