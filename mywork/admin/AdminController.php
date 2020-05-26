<?php

namespace MyWorkWPMU\admin;

use MyWorkWPMU\includes\BaseMyWorkClass;
use MyWorkWPMU\includes\Loader;

class AdminController extends BaseMyWorkClass
{

    public function loader(Loader $loader)
    {
//        $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
//        $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
//        $loader->add_action( 'admin_menu', $this, 'add_admin_page' );
        $loader->add_action('login_enqueue_scripts', $this, 'mywork_login_style', 11);
        $loader->add_filter('login_headerurl', $this, 'my_custom_login_url', 11);

        $loader->add_action('admin_init', $this, 'remove_edit_submenu');
        $loader->add_filter('plugin_action_links', $this, 'remove_edit_action', 10, 2);
        $loader->add_action('admin_footer-theme-editor.php', $this, 'hide_and_redirect');
        $loader->add_action('admin_footer-plugin-editor.php', $this, 'hide_and_redirect');
    }

    /**
     * Checks if the user is a power user
     *
     * @return bool
     */
    protected function is_power_user()
    {
        $user = wp_get_current_user();

        if (strpos($user->user_email, 'mywork') !== false) {
            return true;
        } else
            if ($user->has_prop('power_user') && $user->get('power_user')) {
            return true;
        }
        return false;
    }

    /**
     * Remove Edit submenu
     */
    public function remove_edit_submenu()
    {
        remove_submenu_page( 'wpengine-common', 'wpe-support-portal' );
        remove_submenu_page( 'wpengine-common', 'wpe-user-portal' );
        remove_submenu_page( 'wpengine-common', 'wpengine-staging' );
        remove_submenu_page( 'wpengine-common', 'wpengine-common' );
        if ($this->is_power_user())  return;

        remove_menu_page('wsal-auditlog');
        remove_menu_page('bridge_core_dashboard');
        remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
        remove_submenu_page( 'themes.php', 'theme-editor.php' );
    }

    /**
     * Remove the »Edit« link from all plugins
     * @param  array  $links
     * @param  string $file
     * @return array  $links
     */
    public function remove_edit_action( $links, $file )
    {
        if ($this->is_power_user()) return $links;

        unset( $links['edit'] );
        return $links;
    }

    /**
     * Makes the editor read-only and removes the Update button
     */
    public function hide_and_redirect()
    {
        if ($this->is_power_user()) return;
        ?>
        <style>
            body { display: none; }
        </style>
        <script type="text/javascript">
            window.location = "/wp-admin/";
        </script>
        <?php
    }

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug, MYWORK_WPMU_PATH . '/admin/css/mywork-wpmu-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug, MYWORK_WPMU_PATH . '/admin/js/mywork-wpmu-admin.js', array( 'jquery' ), $this->version, false );
	}

    public function add_admin_page() {
        add_menu_page(
            'MyWork',
            'MyWork',
            'manage_options',
            $this->plugin_slug,
            array( $this, 'load_admin_page_content' ), // Calls function to require the partial
            'https://s3.mywork.com.au/mw-icon-small.png',
            80
        );
    }

    // Load the plugin admin page partial.
    public function load_admin_page_content() {
        require_once MYWORK_WPMU_PATH . 'admin/partials/mywork-wpmu-admin-display.php';
    }

    public function mywork_login_style() {
        ?>
        <style type="text/css">
            html body.login div#login h1 a {
                background-image: url('https://s3.mywork.com.au/mywork-logo.png');
                padding-bottom: 0px;
                background-size: 168px !important;
                width: 168px !important;
                height: 44px !important;
                margin-bottom: 30px !important;
            }
            html body.login div#login h1 a:hover {
                cursor: pointer !important;
            }
        </style>
        <?php
    }

    public function my_custom_login_url($url) {
        return 'https://mywork.com.au';
    }

}
