<?php
/**
 * MyWork Must Use Plugin
 */

$bootstrap = WPMU_PLUGIN_DIR . '/mywork/bootstrap.php';

if (file_exists ($bootstrap))
    include $bootstrap;
