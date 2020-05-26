<?php
/**
 * Plugin Name: MyWork Tools
 * Description: MyWork specific services and options
 * Author: MyWork
 * Author URI: https://mywork.com.au/
 *
 * @package wpengine/common-mu-plugin
 */


$bootstrap = WPMU_PLUGIN_DIR . '/mywork/bootstrap.php';

if (file_exists ($bootstrap))
    include $bootstrap;
