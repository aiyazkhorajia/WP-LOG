<?php
class Model_RequestDebugLog {

    public function __construct() {
        global $wpdb;
        $wpdb->request_debug_log = "{$wpdb->prefix}request_debug_log";
        $this->columns = array(
            'id' => '%d',
            'user_id' => '%d',
            'session_values' => '%s',
            'url' => '%s',
            'error_message' => '%s',
            'created_date' => '%s',
            'ip_address' => '%s',
            'server_name' => '%s',
            'exception_type' => '%s',
            'request_start_time' => '%s',
            'is_async_request' => '%s',
            'post_data' => '%s',
            'response_data' => '%s',
            'request_type' =>'%s',
        );
        $this->allowedColumns = array_keys($this->columns);
    }

    public function insert($data) {


        global $wpdb;

        //Initialise column format array
        $column_formats = $this->columns;

        //White list columns
        $data = array_intersect_key($data, $column_formats);

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);

        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        //log_debug_output_to_file($data);
        $wpdb->insert($wpdb->request_debug_log, $data, $column_formats);

        return $wpdb->insert_id;

    }
}
