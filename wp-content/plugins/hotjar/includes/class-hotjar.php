<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hotjar {

	public function __construct()
	{
		
	}

	public function init() 
	{
		$this->init_admin();
    	$this->enqueue_script();
    	$this->enqueue_admin_styles();
	}

	public function init_admin() {
		register_setting( 'hotjar', 'hotjar_site_id' );
    	add_action( 'admin_menu', array( $this, 'create_nav_page' ) );
	}

	public function create_nav_page() {
		add_options_page(
		  esc_html__( 'Hotjar', 'hotjar' ), 
		  esc_html__( 'Hotjar', 'hotjar' ), 
		  'manage_options',
		  'hotjar_settings',
		  array($this,'admin_view')
		);
	}

	public static function admin_view()
	{
		require_once plugin_dir_path( __FILE__ ) . '/../admin/views/settings.php';
	}

	public static function hotjar_script()
	{
		$hotjar_site_id = get_option( 'hotjar_site_id' );
		$is_admin = is_admin();

		$hotjar_site_id = trim($hotjar_site_id);
		if (!$hotjar_site_id) {
			return;
		}

		if ( $is_admin ) {
			return;
		}

		echo "
		<script>
		(function(h,o,t,j,a,r){
			h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
			h._hjSettings={hjid:" . $hotjar_site_id . ",hjsv:5};
			a=o.getElementsByTagName('head')[0];
			r=o.createElement('script');r.async=1;
			r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
			a.appendChild(r);
		})(window,document,'//static.hotjar.com/c/hotjar-','.js?sv=');
		</script>
		";
	}

	private function enqueue_script() {
		add_action( 'wp_head', array($this, 'hotjar_script') );
	}

    private function enqueue_admin_styles() {
        add_action( 'admin_enqueue_scripts', array($this, 'hotjar_admin_styles' ) );
    }

    public static function hotjar_admin_styles() {
        wp_register_style( 'hotjar_custom_admin_style', plugins_url( '../admin/static/hotjar-admin.css', __FILE__ ), array(), '20190701', 'all' );
        wp_enqueue_style( 'hotjar_custom_admin_style' );
    }

}

?>
