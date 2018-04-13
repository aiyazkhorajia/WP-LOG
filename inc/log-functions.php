<?php 
function log_debug_output_to_table($messageKey, $messageData, $requestType = 'DebugLog')
{
    $messageData = var_export($messageData, true);
    require_once( dirname(__file__) . '/class-model-request-debug-log.php' );
    $serviceLogRecord = array();
    $serviceLogRecord['user_id'] = get_current_user_id();
    $serviceLogRecord['url'] = $_SERVER['REQUEST_URI'];
    $session_values = '';
    if (isset($_SESSION)) {
        $session_values = json_encode($_SESSION);
    }
    $serviceLogRecord['session_values'] = $session_values;
    $serviceLogRecord['created_date'] = current_time('Y-m-d H:i:s', 0);
    $serviceLogRecord['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $serviceLogRecord['server_name'] = $_SERVER['SERVER_NAME'];
    $serviceLogRecord['post_data'] = $messageKey;
    $serviceLogRecord['response_data'] = $messageData;
    $serviceLogRecord['request_start_time'] = current_time('Y-m-d H:i:s', 0);
    $serviceLogRecord['is_async_request'] = defined( 'DOING_AJAX' ) && DOING_AJAX;
    $serviceLogRecord['request_type'] = $requestType;
    $serviceLogRecord['error_message'] = "";
    $serviceLogRecord['exception_type'] = "";


    $modelRequestDebugLog = new Model_RequestDebugLog();

    $modelRequestDebugLog->insert($serviceLogRecord);
}