<?php

namespace MyWorkWPMU\admin;

use MyWorkWPMU\includes\BaseMyWorkClass;
use MyWorkWPMU\includes\Loader;

class AdminController extends BaseMyWorkClass {

	public function loader(Loader $loader)
    {
        $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
        $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
        $loader->add_action( 'admin_menu', $this, 'add_admin_page' );
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
            '/wp-content/mu-pluins/mywork/admin/assets/images/mw-icon-small.png',
            80
        );
    }

    // Load the plugin admin page partial.
    public function load_admin_page_content() {
        require_once MYWORK_WPMU_PATH . 'admin/partials/mywork-wpmu-admin-display.php';
    }

}
