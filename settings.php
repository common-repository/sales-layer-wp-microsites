<?php
if (! Defined ('ABSPATH')) exit;

define('SLYRMC_version', 1.7);

define('SLYRMC_name', 'Sales Layer');

define('SLYRMC_name_icon', 'saleslayer_icon.png');
define('SLYRMC_name_logo', 'logo_head_saleslayer.png');

define('SLYRMC_short_code', 'saleslayer_catalog');

define('SLYRMC_connector_id', 'SLYRMC_connector_id');
define('SLYRMC_connector_key', 'SLYRMC_connector_key');

define('SLYRMC_url_API', 'api.saleslayer.com/');

// Constructs plugin dirname:
if (function_exists('plugin_dir_path')) {

    $dirname = explode('/', str_replace('\\', '/', plugin_dir_path(__FILE__)));
    define('SLYRMC_PLUGIN_NAME_DIR', $dirname[count($dirname) - 2]);

}
