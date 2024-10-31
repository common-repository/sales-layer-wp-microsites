<?php
if (! Defined('ABSPATH')) {
    exit;
}
$token_ok = false;
if (isset($_GET['slyrmc_reload_stats']) && !empty($_GET['slyrmc_reload_stats'])) {
    $token = sanitize_text_field($_GET['slyrmc_reload_stats']);
    $token_sl = get_option('SLYRMC_unique_token');
    if ($token == $token_sl) {
        $token_ok = true;
    }
} else {
    $exploded_request_uri = explode('/', esc_url_raw($_SERVER['REQUEST_URI']));
    $token_sl = get_option('SLYRMC_unique_token');
    if (in_array($token_sl, $exploded_request_uri, false)) {
        $token_ok = true;
    }
}

if ($_POST['id'] && !empty($_POST['id']) && $token_ok) {
    $id      = sanitize_text_field($_POST['id']);
    header_remove();
    session_start();

    define('SLYRMC_PLUGIN_DIR', dirname(__FILE__).'/');

    if ($id == get_option(SLYRMC_connector_id)) {
        echo '1';

        require_once SLYRMC_PLUGIN_DIR.'settings.php';
        require_once SLYRMC_PLUGIN_DIR.'admin/Plugin.class.php';

        session_write_close();
        ob_end_flush();
        flush();
        ignore_user_abort(true);

        $conn = new SLYRMC_Plugin();

        $conn->sync($_POST['id'], get_option(SLYRMC_connector_key));
    } else {
        echo '0';
    }
} else {
    include get_query_template('404');
    header('HTTP/1.0 404 Not Found');
    exit;
}
die();
