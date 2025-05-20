<?php
/**
 * Plugin Name: SLiMS Storage Monitor
 * Plugin URI: https://github.com/username/slims-storage-monitor (Example URI)
 * Description: Laporan realtime penggunaan disk folder penting di SLiMS.
 * Version: 1.0.1
 * Author: Ade Ismail Siregar (adeismailbox@gmail.com)
 * Author URI: mailto:adeismailbox@gmail.com
 */

use SLiMS\Plugins;

defined('INDEX_AUTH') or die('Direct access not allowed!');

// hook menu
$plugin = Plugins::getInstance();
$plugin->registerMenu(
    'reporting', // Target menu
    __('Storage Monitor'), // Menu title
    __DIR__ . '/page.php' // Path to the plugin page
);
?>