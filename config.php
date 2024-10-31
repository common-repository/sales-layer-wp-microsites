<?php
if (!Defined('ABSPATH')) {
    exit;
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once SLYRMC_PLUGIN_DIR.'admin/Plugin.class.php';

if (!current_user_can('administrator')) {
    die('There are insufficient permissions to enter here. Have you logged in?');
}

$backend = new SLYRMC_Plugin();
$importOk = false;
$r = false;
$_SESSION['slyr']['last_step'] = ($_SESSION['slyr']['last_step'] == null ? 0 : $_SESSION['slyr']['last_step']);

if (($slyr_conn_id = get_option(SLYRMC_connector_id) and $_SESSION['slyr']['last_step'] == 1) or $_SESSION['slyr']['last_step'] > 1) {
    $_SESSION['slyr']['last_step'] = 0;

    unset($_GET['action']);
}
 $script = 'var slyrmc_plugin_name_dir = \''.esc_attr(SLYRMC_PLUGIN_NAME_DIR).'\';';

wp_register_script('config_name', '');
wp_enqueue_script('config_name');
wp_add_inline_script('config_name', $script);



if (isset($_POST['connector-id']) && isset($_POST['private-key'])) {
    $importOk = 0;
    $_SESSION['slyr']['last_step'] = 1;

    $slyr_conn_id  = sanitize_text_field($_POST['connector-id']);
    $slyr_conn_key = sanitize_text_field($_POST['private-key']);

    if ((!empty($_POST['connector-id']) && !empty($_POST['private-key']))) {
        $r = $backend->getData($slyr_conn_id, $slyr_conn_key);

        if (!isset($r['error'])) {
            $_SESSION['slyr']['last_step'] = 2;
            $_SESSION['slyr']['connector-id'] = $slyr_conn_id;
            $_SESSION['slyr']['private-key']  = $slyr_conn_key;
            $_SESSION['slyr']['data'] = $r;
            $_SESSION['slyr']['last_step'] = 0;
            $importOk = $_POST['import'] = 1;
        }
    }
} elseif (isset($_POST['import']) or isset($_POST['update'])) {
    $r = $backend->sync(get_option(SLYRMC_connector_id), get_option(SLYRMC_connector_key));

    if (!isset($r['error'])) {
        $importOk = true;
        $_SESSION['slyr']['importOk'] = 1;

        if (isset($_SESSION['slyr']['connector-id']) && isset($_SESSION['slyr']['private-key'])) {
            $slyr_conn_id = $_SESSION['slyr']['connector-id'];
            $slyr_conn_key = $_SESSION['slyr']['private-key'];
        } else {
            $slyr_conn_key = get_option(SLYRMC_connector_key);
        }

        if (isset($_POST['import'])) {
            unset($r);
        }
    }

    $_SESSION['slyr']['last_step'] = 0;
} elseif (isset($_GET['slyr_logout']) && $_GET['slyr_logout'] == 1) {
    unset($_SESSION['slyr']['connector-id'], $_SESSION['slyr']['private-key'], $_GET['slyr_logout']);

    update_option(SLYRMC_connector_id, 0);
    update_option(SLYRMC_connector_key, 0);

    $_SESSION['slyr']['last_step'] = 1;
} elseif (!$slyr_conn_id or isset($_POST['change'])) {
    $_SESSION['slyr']['last_step'] = 1;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Configuración</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div id="slyr_catalogue_admin">
    <div class="container login">
        <div class="login-screen">
            <div class="login-icon">
                <h1><?php echo esc_html(SLYRMC_name); ?> plugin
                    <small>/ configuration</small>
                </h1>
            </div>
            <div class="login-form">
                <form method="POST" action="" id="config_form">

                    <?php if (isset($r['error'])): ?>
                        <div class="dialog dialog-warning"><?php echo $r['error']['message'] ?></div>
                    <?php elseif ($importOk): ?>
                        <div class="dialog dialog-success">The catalog has been synchronized!</div>
                    <?php endif ?>
                    <?php
                    if ($_SESSION["slyr"]["last_step"] == 0) :
                        $slyr_config = $GLOBALS['wpdb']->get_results('select * from slyr___api_config');

                        if (isset($r['catalogue']) && is_array($r['catalogue']) && !empty($r['catalogue'])) {
                            $toshow = '<h6>Changes:</h6>';
                            $toshow .= '<ul class="list_changes">';

                            $table_names = array('catalogue', 'products', 'product_formats', 'locations');
                            $indexes = array('modified', 'deleted');

                            foreach ($table_names as $table_name) {
                                if (isset($r[$table_name])) {
                                    foreach ($indexes as $index) {
                                        if (isset($r[$table_name][$index])) {
                                            $toshow .= '<li><strong id="'.esc_attr($table_name).'">'.esc_html($r[$table_name][$index]).'</strong> '.(($index == 'modified') ? 'updated' : $index).' '.(($table_name == 'catalogue') ? 'sections' : str_replace(
                                                '_',
                                                ' ',
                                                $table_name
                                                )).'</li>';
                                        }
                                    }
                                }
                            }

                            $toshow .= '</ul>';
                            echo $toshow;
                        }
                        ?>
                        <?php if (isset($slyr_conn_id)): ?>
                            <h6>Current connector ID code:</h6>
                            <h3 style="margin-top:0">
                                <?php echo esc_html($slyr_conn_id); ?>
                            </h3>
                        <?php endif; ?>
                        <?php if (isset($slyr_config[0]->last_update)): ?>
                            <h6>Last synchronization:</h6>
                            <h4 style="margin-top:0">
                                <?php echo esc_html($slyr_config[0]->last_update); ?>
                                - <?php echo date('Y-m-d H:i:s', $slyr_config[0]->last_update); ?></h4>
                        <?php endif; ?>
                        <?php if (!isset($_POST['import']) && !isset($_POST['update'])): ?>
                                <button href="#fakelink" class="button button-primary" name="update"><strong>Update
                                catalogue</strong></button>
                        <?php endif; ?>
                        <?php if (!isset($_POST['import'])): ?>
                            <button href="#fakelink"
                            class="button action<?php # if (!isset($_POST['update'])):?> mlm<?php # endif;?>"
                            name="change"><strong>Change connector ID</strong></button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($_SESSION["slyr"]["last_step"] == 1): ?>
                        <table class="form-table">
                            <tr>
                                <td>
                                    <input type="text" class="regular-text" value="" placeholder="Connector ID code"
                                           id="connector-id" name="connector-id" autocomplete="off" required="true" maxlength="34">
                                    <label class="login-field-icon fui-lock" for="connector-id"></label>
                                    <?php if ($slyr_conn_id): ?>
                                        <small style="color:rgba(1,1,1,0.5)">Current ID:
                                            <strong><?php echo esc_html($slyr_conn_id); ?></strong>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="text" class="regular-text" value="" placeholder="Connector private key"
                                           id="private-key" name="private-key" autocomplete="off" required="true" maxlength="34">
                                    <label class="login-field-icon fui-lock" for="private-key"></label>
                                </td>
                            </tr>
                        </table>
                        <button id="connect" class="button button-primary button_block">Connect!</button>
                        <?php if ($slyr_conn_id): ?>
                            <a href="<?php echo admin_url('admin.php?page=slyrmc_config&action=cancel_change') ?>"
                               class="button action" style="margin-top:10px"><strong>Cancel</strong></a>
                        <?php endif; ?>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div><!-- /.container -->
</div>
</body>
</html> 