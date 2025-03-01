<?php
/**
 * $Id$.
 *
 * Created by Iban Borras.
 *
 * CreativeCommons License Attribution (By):
 * http://creativecommons.org/licenses/by/4.0/
 *
 * SalesLayer Conn class is a library for connection to SalesLayer API
 *
 * @modified 2019-05-21
 *
 * @version 1.29
 */
if (! Defined ('ABSPATH')) exit;
class SalesLayer_Conn 
{

    public $version_class = '1.29';

    public $url = 'api.saleslayer.com';

    public $SSL        = false;
    public $SSL_Cert   = null;
    public $SSL_Key    = null;
    public $SSL_CACert = null;

    public $connect_API_version = '1.17';

    public $response_error         = 0;
    public $response_error_message = '';

    private $__codeConn  = null;
    private $__secretKey = null;
    private $__keyCypher = 'sha256'; // <-- or 'sha1'

    protected $__group_multicategory                    = false;
    protected $__get_same_parent_variants_modifications = false;
    protected $__get_parent_modifications               = false;
    protected $__get_parents_category_tree              = false;

    public $data_returned        = null;
    public $response_api_version = null;
    public $response_time        = null;
    public $response_action      = null;
    public $response_tables_info = null;
    public $response_tables_data = null;

    public $response_table_modified_ids = null;
    public $response_table_deleted_ids  = null;
    public $response_files_list         = null;
    public $response_offline_file       = null;
    public $response_waiting_files      = null;

    public $time_unlimit = true;
    public $memory_limit = ''; // <-- examples: 512M or 1024M
    public $user_abort   = false;

    private $__error_list = [
        '1'  => 'Validation error',
        '2'  => 'Invalid connector code',
        '3'  => 'Wrong unique key',
        '4'  => 'Invalid codification key',
        '5'  => 'Incorrect date of last_update',
        '6'  => 'API version nonexistent',
        '7'  => 'Wrong output mode',
        '8'  => 'Invalid compression type',
        '9'  => 'Invalid private key',
        '10' => 'Service temporarily blocked',
        '11' => 'Service temporarily unavailable',
        '12' => 'Incorrect date-code',
        '13' => 'Date code has expired',
        '14' => 'Updating data. Try later'
    ];

    /**
     * Constructor - if you're not using the class statically.
     *
     * @param string $codeConn  Code Connector Identificator key
     * @param string $secretKey Secret key
     * @param bool   $SSL       Enable SSL
     * @param string $url       Url to SalesLayer API connection
     * @param bool   $forceuft8 Set PHP system default charset to utf-8
     */
    public function __construct($codeConn = null, $secretKey = null, $SSL = true, $url = false, $forceuft8 = true)
    {
            if (true == $forceuft8) {
                ini_set('default_charset', 'utf-8');
            }

            if ($codeConn) {
                $this->set_identification($codeConn, $secretKey);
            }

            $this->set_SSL_connection($SSL);
            $this->set_URL_connection($url);
    }

    /**
     * Create URL for API.
     *
     * @param timestamp $last_update last updated database
     *
     * @return string
     */
    private function __get_api_url($last_update = false)
    {
        if (null != $this->__secretKey) {
            $time    = time();
            $unic    = mt_rand();
            $key     = $this->__codeConn . $this->__secretKey . $time . $unic;
            $key     = ('sha256' == $this->__keyCypher ? hash('sha256', $key) : sha1($key));
            $key_var = ('sha256' == $this->__keyCypher ? 'key256' : 'key');

            $get = "&time=$time&unique=$unic&$key_var=$key";
        } else {
            $get = '';
        }

        $URL = 'http' . (($this->SSL) ? 's' : '') . '://' . $this->url . '?code=' . urlencode($this->__codeConn) . $get;

        if ($last_update) {
            $URL .= '&last_update=' . (!is_numeric($last_update) ? strtotime($last_update) : $last_update);
        }
        if (null !== $this->connect_API_version) {
            $URL .= '&ver=' . urlencode($this->connect_API_version);
        }
        if (false !== $this->__group_multicategory) {
            $URL .= '&group_category_id=1';
        }
        if (false !== $this->__get_same_parent_variants_modifications) {
            $URL .= '&same_parent_variants=1';
        }
        if (false !== $this->__get_parent_modifications) {
            $URL .= '&first_parent_level=1';
        }
        if (false !== $this->__get_parents_category_tree) {
            $URL .= '&parents_category_tree=1';
        }

        return $URL;
    }

    /**
     * Clean previous error code.
     */
    private function __clean_error()
    {
        $this->response_error         = 0;
        $this->response_error_message = '';
    }

    /**
     * Set the Connector identification and secret key.
     *
     * @param string $codeConn Connector Code Identificator
     * @param string $secret   Secret Key for secure petitions
     */
    public function set_identification($codeConn, $secretKey = null)
    {
        $this->__codeConn  = $codeConn;
        $this->__secretKey = $secretKey;

        $this->__clean_error();
    }

    /**
     * Get the Connector identification code.
     *
     * @return connector code
     */
    public function get_identification_code()
    {
        return $this->__codeConn;
    }

    /**
     * Get the Connector identification secret.
     *
     * @return connector secret
     */
    public function get_identification_secret()
    {
        return $this->__secretKey;
    }

    /**
     * Set the SSL true/false connection.
     *
     * @param bool $stat indicator
     */
    public function set_SSL_connection($stat)
    {
        $this->SSL = $stat;
    }

    /**
     * Set SSL client credentials.
     *
     * @param string $cert   SSL client certificate
     * @param string $key    SSL client key
     * @param string $CACert SSL CA cert (only required if you are having problems with your system CA cert)
     */
    public function set_SSL_credentials($cert = null, $key = null, $CACert = null)
    {
        $this->SSL_Cert   = $cert;
        $this->SSL_Key    = $key;
        $this->SSL_CACert = $CACert;
    }

    /**
     * Set the URL for the connection petitions.
     *
     * @param string $url base
     */
    public function set_URL_connection($url)
    {
        if ($url) {
            $this->url = $url;
        }
    }

    /**
     * Set the API version to connect.
     *
     * @param float $version version number of the API to connect
     */
    public function set_API_version($version)
    {
        $this->connect_API_version = $version;
    }

    /**
     * Set group multicategory products.
     *
     * @param bool $group
     */
    public function set_group_multicategory($group)
    {
        $this->__group_multicategory = $group;
    }

    /**
     * Set value for getting same parent variants modifications on single variant modification.
     *
     * @param bool $enable
     */
    public function set_same_parent_variants_modifications($enable)
    {
        $this->__get_same_parent_variants_modifications = $enable;
    }

    /**
     * Set value for getting modifications/deletions of first level parents.
     *
     * @param bool $enable
     */
    public function set_first_level_parent_modifications($enable)
    {
        $this->__get_parent_modifications = $enable;
    }

    /**
     * Set value for getting modifications/deletions of first level parents.
     *
     * @param bool $enable
     */
    public function set_parents_category_tree($enable)
    {
        $this->__get_parents_category_tree = $enable;
    }

    /**
     * Check if Connector identification have been set.
     *
     * @return bool
     */
    public function hasConnector()
    {
        return null !== $this->__codeConn && null !== $this->__secretKey;
    }

    /**
     * Request to retrieve information.
     *
     * @param timestamp $last_update         last updated database
     * @param array     $params              extra parameters for the API
     * @param string    $connector_type      strict specification of connector type
     * @param bool      $add_reference_files return file or image reference names
     *
     * @return array info or false (if error)
     */
    public function get_info($last_update = null, $params = null, $connector_type = null, $add_reference_files = false)
    {
        if ($this->hasConnector()) {

            $api_url = $this->__get_api_url($last_update);
     
            if ($this->time_unlimit) {
                set_time_limit(0);
            }
            if ($this->memory_limit) {
                ini_set('memory_limit', $this->memory_limit);
            }
            if ($this->user_abort) {
                ignore_user_abort(true);
            }

            if (is_array($params) && !empty($params)) {

                // $params = array('body' => json_encode($params));
                $params = array('body' => $params);
                $wp_remote = wp_remote_post( $api_url, $params );
            
            }else{

                $wp_remote = wp_remote_get( $api_url );

            }

            $wp_body = wp_remote_retrieve_body( $wp_remote );
            $wp_response_code = wp_remote_retrieve_response_code( $wp_remote );
            
            if ($wp_body !== false && $wp_response_code >= 200 && $wp_response_code < 300){

                $this->data_returned = json_decode(preg_replace('/^\xef\xbb\xbf/', '', $wp_body), 1);

                if (false !== $this->data_returned && is_array($this->data_returned)) {

                    unset($wp_body);

                    if ($connector_type
                        && isset($this->data_returned['schema']['connector_type'])
                        && $connector_type != $this->data_returned['schema']['connector_type']) {

                        $this->__trigger_error('Wrong connector type: ' . $this->data_returned['schema']['connector_type'], 105);

                    } else {

                        $this->__clean_error();

                        return $this->__parsing_json_returned();
                    }

                } else {

                    $this->__trigger_error('Void response or malformed: ' . $wp_body, 101);
                }

            }else{

                $this->__trigger_error('Error connection: ' . wp_remote_retrieve_response_message( $wp_remote ), 102);

            }

        }

        return false;
    }

    /**
     * Return received data.
     *
     * @return string or null
     */
    public function get_data_returned()
    {
        return $this->data_returned;
    }

    /**
     * Parsing received data.
     *
     * @return bool
     */
    private function __parsing_json_returned()
    {
        if (null !== $this->data_returned) {

            $this->response_api_version = $this->data_returned['version'];
            $this->response_time        = $this->data_returned['time'];

            if (isset($this->data_returned['error']) && $this->data_returned['error']) {
                if (isset($this->__error_list[$this->data_returned['error']])) {
                    $message_error = $this->__error_list[$this->data_returned['error']];
                } else {
                    $message_error = 'API error';
                }

                $this->__trigger_error($message_error, $this->data_returned['error']);
            } else {
                $this->response_action      = $this->data_returned['action'];
                $this->response_tables_info =
                $this->response_files_list  = [];

                if (      isset($this->data_returned['data_schema_info'])
                    && is_array($this->data_returned['data_schema_info'])
                    &&    count($this->data_returned['data_schema_info'])) {

                    foreach ($this->data_returned['data_schema_info'] as $table => $info) {

                        foreach ($info as $field => $props) {

                            $this->response_tables_info[$table]['fields'][$field] = [
                                'type'             => (('ID' == $field or substr($field, 0, 3) == 'ID_') ? 'key' : $props['type']),
                                'sanitized'        => (isset($props['sanitized']) ? $props['sanitized'] : (isset($props['basename']) ? $props['basename'] : $field)),
                                'has_multilingual' => ((isset($props['language_code']) and $props['language_code']) ? 1 : 0),
                            ];

                            if (isset($props['language_code']) && $props['language_code']) {
                                $this->response_tables_info[$table]['fields'][$field]['language_code'] = $props['language_code'];
                                $this->response_tables_info[$table]['fields'][$field]['basename']      = $props['basename'];
                            }

                            if (isset($props['title']) && $props['title']) {
                                $this->response_tables_info[$table]['fields'][$field]['title'] = $props['title'];
                            } elseif (isset($props['titles']) && $props['titles']) {
                                $this->response_tables_info[$table]['fields'][$field]['titles'] = $props['titles'];
                            } else {
                                $this->response_tables_info[$table]['fields'][$field]['title'] = $field;
                            }

                            if (isset($props['table_key'])) {
                                $this->response_tables_info[$table]['fields'][$field]['title'] = $props['table_key'];
                            }

                            if (isset($props['sizes']) && $props['sizes']) {
                                $this->response_tables_info[$table]['fields'][$field]['image_sizes'] = $props['sizes'];
                            }
                        }
                    }
                }

                $this->response_tables_data        =
                $this->response_table_modified_ids =
                $this->response_table_deleted_ids  = [];

                if (is_array($this->data_returned['data_schema'])) {

                    foreach ($this->data_returned['data_schema'] as $table => $info) {

                        foreach ($info as $ord => $fname) {

                            if (is_string($fname)) {

                                if (substr($fname, 0, 3) == 'ID_' and 'ID_PARENT' != $fname) {

                                    $this->response_tables_info[$table]['table_joins'][$fname] = preg_replace('/^ID_/', '', $fname);
                                }
                            }
                        }

                        $this->response_tables_data[$table]                    = ['modified' => [], 'deleted' => []];
                        $this->response_tables_info[$table]['count_registers'] =

                            (is_array($this->data_returned['data'][$table]) ? count($this->data_returned['data'][$table]) : 0);

                        $this->response_tables_info[$table]['count_modified'] =
                        $this->response_tables_info[$table]['count_deleted']  = 0;
                        $this->response_table_deleted_ids[$table]             = [];

                        if ($this->response_tables_info[$table]['count_registers']) {

                            foreach ($this->data_returned['data'][$table] as &$fields) {

                                if ('D' == $fields[0]) {

                                    $this->response_table_deleted_ids[$table][]      =
                                    $this->response_tables_data[$table]['deleted'][] = $fields[1];
                                    $this->response_tables_info[$table]['count_deleted'] ++;

                                } else {
                                    $data                                        = [];
                                    $this->response_table_modified_ids[$table][] = $data['ID'] = $fields[1];

                                    foreach ($this->data_returned['data_schema'][$table] as $ord => $field) {

                                        $fname = (!is_array($field)) ? $field : key($field);

                                        if (!in_array($fname, ['STATUS', 'ID'])) {

                                            if ('REF' == $fname or substr($fname, 0, 3) == 'ID_') {

                                                $data[$fname] = $fields[$ord];

                                            } else if (       isset($fields[$ord])
                                                       and is_array($fields[$ord])
                                                       and    isset($this->data_returned['data_schema'][$table][$ord][$fname])
                                                       and is_array($this->data_returned['data_schema'][$table][$ord][$fname])) {

                                                $data['data'][$fname] = [];

                                                if (isset($fields[$ord][0]) and 'U' != $fields[$ord][0]) {

                                                    foreach ($fields[$ord] as $fsub) {

                                                        if (is_array($fsub)) {
                                                            foreach ($fsub as $k => $a) {
                                                                if ($k > 1) {
                                                                    $ext = $this->data_returned['data_schema'][$table][$ord][$fname][intval($k)];
                                                                    if (is_array($ext)) { $ext = $ext['field']; }
                                                                    $data['data'][$fname][$fsub[1]][$ext]      =
                                                                    $this->response_files_list['list_files'][] = $a;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                            } else {
                                                $data['data'][$fname] = (isset($fields[$ord]) ? $fields[$ord] : '');
                                            }
                                        }
                                    }

                                    $this->response_tables_data[$table]['modified'][] = $data;
                                    $this->response_tables_info[$table]['count_modified']++;
                                }
                            }
                            unset($fields);
                        }
                    }
                }

                if (isset($this->data_returned['waiting'])) {
                    $this->response_waiting_files = $this->data_returned['waiting'];
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Set the error code and message.
     *
     * @param string $message error text
     * @param int    $errnum  error identificator
     */
    public function __trigger_error($message, $errnum)
    {
        if (0 === $this->response_error) {
            $this->response_error         = $errnum;
            $this->response_error_message = $message;
        }

        //trigger_error($message, E_USER_WARNING);
    }

    /**
     * Check if error.
     *
     * @return bool
     */
    public function has_response_error()
    {
        if ($this->response_error || false === $this->response_time) {
            return true;
        }

        return false;
    }

    /**
     * Get error number.
     *
     * @return int
     */
    public function get_response_error()
    {
        return $this->response_error;
    }

    /**
     * Get error message.
     *
     * @return string
     */
    public function get_response_error_message()
    {
        return $this->response_error_message;
    }

    /**
     * Returns the updated UNIX time from Sales Layer server.
     *
     * @param string $mode mode of output date (datetime|unix). Default: datetime
     *
     * @return int
     */
    public function get_response_time($mode = 'datetime')
    {
        return $this->response_time ? ('datetime' === $mode ? date('Y-m-d H:i:s', $this->response_time) : $this->response_time) : false;
    }

    /**
     * Get API version.
     *
     * @return string
     */
    public function get_response_api_version()
    {
        return $this->response_api_version;
    }

    /**
     * Get Conn class version.
     *
     * @return string
     */
    public function get_conn_class_version()
    {
        return $this->version_class;
    }

    /**
     * Geaction (update = only changes in the database, or refresh = all database information).
     *
     * @returstring
     */
    public function get_response_action()
    {
        return $this->response_action;
    }

    /**
     * Get list of tables.
     *
     * @return array
     */
    public function get_response_list_tables()
    {
        return array_keys($this->response_tables_info);
    }

    /**
     * Get information about the structure of tables.
     *
     * @return array
     */
    public function get_response_table_information($table = null)
    {
        return (null === $table) ? $this->response_tables_info : $this->response_tables_info[$table];
    }

    /**
     * Get parsed data of tables.
     *
     * @return array
     */
    public function get_response_table_data($table = null)
    {
        return (null === $table) ? $this->response_tables_data : $this->response_tables_data[$table];
    }

    /**
     * Get ID's of registers deleted.
     *
     * @return array
     */
    public function get_response_table_deleted_ids($table = null)
    {
        return (null === $table) ? $this->response_table_deleted_ids
                                   :
                                   ((isset($this->response_table_deleted_ids[$table])) ? $this->response_table_deleted_ids[$table] : []);
    }

    /**
     * Get ID's of registers modified.
     *
     * @return array
     */
    public function get_response_table_modified_ids($table = null)
    {
        return (null === $table ? $this->response_table_modified_ids
               :
               (isset($this->response_table_modified_ids[$table]) ? $this->response_table_modified_ids[$table] : []));
    }

    /**
     * Get only the modified information.
     *
     * @return array
     */
    public function get_response_table_modified_data($table = null)
    {
        if (null === $table) {
            if (isset($this->response_tables_data)) {
                $result = array();

                foreach (array_keys($this->response_tables_data) as $table) {
                    if (isset($this->response_tables_data[$table]['modified'])) {
                        $result[$table] = $this->response_tables_data[$table]['modified'];
                    }
                }

                return $result;
            }
        } elseif (isset($this->response_tables_data[$table]['modified'])) {
            return $this->response_tables_data[$table]['modified'];
        }

        return array();
    }

    /**
     * Get the list of all files to download.
     *
     * @return array
     */
    public function get_response_list_modified_files()
    {
        return $this->response_files_list;
    }

    /**
     * Get information about connector schema.
     *
     * @return array
     */
    public function get_response_connector_schema()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema'])) {
            return $this->data_returned['schema'];
        }

        return null;
    }

    /**
     * Get language titles of tables
     *
     * @return array
     */
    public function get_response_sanitized_table_names()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']) and isset($this->data_returned['schema']['sanitized_table_names'])) {

            return $this->data_returned['schema']['sanitized_table_names'];
        }

        return null;
    }
    /**
     * Get language titles of tables
     *
     * @return array
     */
    public function get_response_table_titles()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']) and isset($this->data_returned['schema']['language_table_names'])) {

            return $this->data_returned['schema']['language_table_names'];
        }

        return null;
    }

    /**
     * Get table joins
     *
     * @return array
     *
     */
    public function get_response_table_joins($table = null)
    {
        if (null !== $this->response_tables_info and is_array($this->response_tables_info)) {
            if (null === $table) {
                $list = [];
                foreach ($this->response_tables_info as $table => $info) { 
                    $list[$table] = (isset($info['table_joins']) ? $info['table_joins'] : []); 
                }

                return $list;
            }

            return (isset($this->response_tables_info[$table]['table_joins']) ? $this->response_tables_info[$table]['table_joins'] : []);
        }

        return null;
    }

    /**
     * Get information about connector type.
     *
     * @return array
     */
    public function get_response_connector_type()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']['connector_type'])) {
            return $this->data_returned['schema']['connector_type'];
        }

        return null;
    }

    /**
     * Get the default language of company database.
     *
     * @return string
     */
    public function get_response_default_language()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']['default_language'])) {
            return $this->data_returned['schema']['default_language'];
        }

        return null;
    }

    /**
     * Get the languages used by the company database.
     *
     * @return array
     */
    public function get_response_languages_used()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']['languages'])) {
            return $this->data_returned['schema']['languages'];
        }

        return null;
    }

    /**
     * Get information about Sales Layer company ID.
     *
     * @return number
     */
    public function get_response_company_ID()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']['company_ID'])) {
            return $this->data_returned['schema']['company_ID'];
        }

        return null;
    }

    /**
     * Get information about Sales Layer company name.
     *
     * @return string
     */
    public function get_response_company_name()
    {
        if (null !== $this->data_returned and isset($this->data_returned['schema']['company_name'])) {
            return $this->data_returned['schema']['company_name'];
        }

        return null;
    }

    /**
     * Get the compact file(s) in the offline mode.
     *
     * @return array
     */
    public function get_response_offline_file()
    {
        if (is_array($this->data_returned['output']['offline_files']) && count($this->data_returned['output']['offline_files'])) {
            return $this->data_returned['output']['offline_files'];
        }

        return null;
    }

    /**
     * Get number of images or files waiting in process.
     *
     * @return array
     */
    public function get_response_waiting_files()
    {
        if (is_array($this->response_waiting_files) && count($this->response_waiting_files)) {
            return $this->response_waiting_files;
        }

        return null;
    }

    /**
     * Get field titles.
     *
     * @return array
     */
    public function get_response_field_titles($table = null)
    {
        $titles = [];

        if (null !== $this->data_returned) {
            if (!$table) {
                $tables = array_keys($this->response_tables_info);
            } else {
                $tables = [$table];
            }

            $languages = $this->data_returned['schema']['languages'];

            foreach ($tables as $table) {
                $titles[$table] = [];

                if (isset($this->response_tables_info[$table])
                    and is_array($this->response_tables_info[$table]['fields'])
                    and count($this->response_tables_info[$table]['fields'])) {
                    foreach ($this->response_tables_info[$table]['fields'] as $field => &$info) {
                        if (!in_array($field, ['ID', 'ID_PARENT'])) {
                            $field_name = (isset($info['basename']) ? $info['basename'] : $field);

                            if (isset($info['titles']) and count($info['titles'])) {
                                $titles[$table][$field_name] = $info['titles'];
                            } else {
                                if (!isset($titles[$table][$field_name])) {
                                    $titles[$table][$field_name] = [];
                                }

                                $title = ((isset($info['title']) and $info['title']) ? $info['title'] : $field_name);

                                if (isset($info['language_code'])) {
                                    $titles[$table][$field_name][$info['language_code']] = $title;
                                } else {
                                    foreach ($languages as $lang) {
                                        $titles[$table][$field_name][$lang] = $title;
                                    }
                                }
                            }
                        }
                    }

                    unset($info);
                }
            }
        }

        return $titles;
    }

    /**
     * Get field titles in certain language.
     *
     * @param string $language (ISO 639-1)
     *
     * @return array
     */
    public function get_response_language_field_titles($language, $table = null)
    {
        $titles = [];

        if (null !== $this->data_returned) {
            if (!$table) {
                $tables = array_keys($this->response_tables_info);
            } else {
                $tables = [$table];
            }

            $default_language = $this->data_returned['schema']['default_language'];

            foreach ($tables as $table) {
                $titles[$table] = [];

                if (isset($this->response_tables_info[$table])
                    and is_array($this->response_tables_info[$table]['fields'])
                    and count($this->response_tables_info[$table]['fields'])) {
                    foreach ($this->response_tables_info[$table]['fields'] as $field => &$info) {
                        if (!in_array($field, ['ID', 'ID_PARENT'])) {
                            if (isset($info['language_code'])) {
                                if ($info['language_code'] == $language) {
                                    if (isset($info['titles']) and count($info['titles'])) {
                                        if (isset($info['titles'][$language])) {
                                            $titles[$table][$field] = $info['titles'][$language];
                                        } elseif (isset($info['titles'][$default_language])) {
                                            $titles[$table][$field] = $info['titles'][$default_language];
                                        } else {
                                            $titles[$table][$field] = reset($info['titles']);
                                        }
                                    } elseif (isset($info['title'])) {
                                        $titles[$table][$field] = $info['title'];
                                    } else {
                                        $titles[$table][$field] = $info['basename'];
                                    }
                                }
                            } else {
                                $titles[$table][$field] = $field;
                            }
                        }
                    }
                    unset($info);
                }
            }
        }

        return $titles;
    }
}
