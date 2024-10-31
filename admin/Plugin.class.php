<?php

if (! Defined('ABSPATH')) {
    exit;
}
if (!defined('SLYRMC_connector_id')) {
    include_once SLYRMC_PLUGIN_DIR.'../settings.php';
}
if (!class_exists('SLYRMC_Softclear_API')) {
    require_once SLYRMC_PLUGIN_DIR.'admin/api/api_sc.php';
}

// plugin lib

class SLYRMC_Plugin extends SLYRMC_Softclear_API
{

    private $connector;
    private $plugin;

    public function __construct()
    {

        parent::__construct();

    }

    public function getPlugin()
    {

        return $this->plugin;
    }

    public function getData($connectorid, $privatekey)
    {

        $response = $this->get_sync_data($connectorid, $privatekey, 1);

        if (isset($response['error'])) {

            if ($response['error']['code'] == 105) {

                $response['error']['message'] = 'Ops! This connector is not for WordPress.';

            } else {
                if ($response['error']['code'] < 5) {

                    $response['error']['message'] = 'Ops! The credentials are incorrect.';
                }
            }

            return $response;
        }

        return $this->plugin = $response;

    }

    public function sync($connectorid, $privatekey)
    {

        $sync_data = $this->get_sync_data($connectorid, $privatekey, 0);

        if (isset($sync_data['error']) && isset($sync_data['error']['message'])) {

            return array('error' => array('message' => $sync_data['error']['message']));

        } else {
            if (isset($sync_data['error']) && !isset($sync_data['error']['message'])) {

                return array('error' => array('message' => 'Error on sync data'));

            } else {
                if (is_array($sync_data) && empty($sync_data)) {

                    return array('error' => array('message' => 'There is no new information to synchronize.'));

                }
            }
        }

        return $sync_data;

    }

    public function checkUserConnector($connectorId)
    {

        return ($this->checkUserConnector($connectorId) ? true : false);
    }

}
