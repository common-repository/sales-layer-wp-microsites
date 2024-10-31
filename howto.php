<?php
if (! Defined('ABSPATH')) {
    exit;
}
if (!current_user_can('administrator')) {
    die('There are insufficient permissions to enter here. Have you logged in?');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title data-i18n="howto.title"></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div id="slyr_catalogue_admin">
    <header>
        <div id="logo">
            <a href="#"><img src="<?php echo plugin_dir_url(__FILE__).'images/'.SLYRMC_name_logo; ?>"
                             title="logotipo de Sales Layer"></a>
        </div>
        <h3>How To Start</h3>
    </header>
    <main>
        <section class="howtoinfo">
            <p><strong><?php echo SLYRMC_name; ?></strong> plugin allows you to add in your Wordpress website all your
                catalogue super easily. To do so, the catalog automatically imports and syncs all the product
                information.</p>
            <p>First of all the plugin needs the <strong>connector ID code</strong> and the <strong>private key</strong>.
                You will find them in the connector details of <strong><?php echo SLYRMC_name; ?></strong>.</p>
        </section>
        <section class="steps">
            <ol>
                <li>Go to <a href="<?php echo admin_url() ?>admin.php?page=slyrmc_config"><?php echo SLYRMC_name; ?> >
                        Configuration</a></li>
                <li>Add connection credentials.</li>
                <li>Import categories, products, formats and locations.</li>
                <li>Copy and paste the shortcode <strong>[<?php echo SLYRMC_short_code; ?>]</strong> inside the body of a
                    page.
                </li>
            </ol>
        </section>
    </main>
</div>
</body>
</html>