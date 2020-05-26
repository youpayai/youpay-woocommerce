<?php
/**
 * Plugin Bootstrap File
 */

// Current Plugin Version
define( 'MYWORK_WPMU_VERSION', '1.0.0' );

// Set Path
define( 'MYWORK_WPMU_PATH', WPMU_PLUGIN_DIR . '/mywork/');

// Load Composer
require MYWORK_WPMU_PATH . '/vendor/autoload.php';

// Run Plugin
\MyWorkWPMU\includes\MyWorkWPMU::run();