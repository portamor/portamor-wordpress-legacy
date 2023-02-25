<?php

    // No direct access to this file
    defined( 'ABSPATH' ) or die();

?>

<?php if ( isset( $_GET['settings-updated'] ) ) : ?>

    <?php if ( get_option('hotjar_site_id') == '' ): ?>
        <div id="message" class="notice notice-warning is-dismissible">
            <p><strong><?php _e('Hotjar script is disabled.', 'hotjar'); ?></strong></p>
        </div>
    <?php else: ?>
        <div id="message" class="notice notice-success is-dismissible">
                <p><strong><?php echo sprintf( __('Hotjar script installed for Site ID %s <a href="%s" target="_blank">Click here to verify your install</a>.', 'hotjar'), get_option('hotjar_site_id'), get_site_url() . '?hjVerifyInstall=' . get_option('hotjar_site_id') ); ?></strong></p>
        </div>
    <?php endif; ?>

<?php endif; ?>


<div id="business-info-wrap" class="wrap">

    <div class="wp-header">
        <img src="<?php echo plugins_url( '../static/hotjar_logo_2x.png', __FILE__ ); ?>" alt="Hotjar" class="hotjar-logo">
        <img src="<?php echo plugins_url( '../static/tagline_2x.png', __FILE__ ); ?>" alt="<?php echo __('The fast & visual way to understand your users.', 'hotjar'); ?>" class="hotjar-tagline">
    </div>



    <form method="post" action="options.php">
        <?php settings_fields( 'hotjar' );
        do_settings_sections('hotjar'); ?>

        <div id="hotjar-form-area">
            <p><?php
            $url = 'https://insights.hotjar.com/site/list';
            $link = sprintf( wp_kses( __( 'Visit your <a href="%s" target="_blank">Hotjar site list</a> to find your unique Hotjar ID.', 'hotjar'), array(  'a' => array( 'href' => array(), 'target' =>  '_blank' ) ) ), esc_url( $url ) );
            echo $link;
            ?></p>
            <p><?php _e('Input your Hotjar ID into the field below to connect your Hotjar and WordPress accounts.', 'hotjar'); ?></p>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                        <label for="hotjar_site_id"><?php esc_html_e( 'Hotjar ID', 'hotjar'); ?></label>
                        </th>

                        <td>
                        <input type="number" name="hotjar_site_id" id="hotjar_site_id" value="<?php echo esc_attr( get_option('hotjar_site_id') ); ?>" />
                        <p class="description" id="wp_hotjar_site_id_description"><?php esc_html_e( '(Leave blank to disable)', 'hotjar' ); ?></p>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

        <?php submit_button(); ?>

    </form>
</div>
