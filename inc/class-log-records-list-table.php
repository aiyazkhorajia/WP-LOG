<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Log_Records_List_Table extends WP_List_Table
{
    /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */


    function __construct() {
        parent::__construct(array(
            'singular'=> 'asm_log_record', //Singular label
            'plural' => 'asm_log_records', //plural label, also this well be one of the table css class
            'ajax'   => false //We won't support Ajax for this table
        ));
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {

        $columns = [
            'col_id'=>__('ID', 'aftersales'),
            'col_created_date'=>__('Created Date', 'aftersales'),
            'col_ip_address'=>__('IP Address', 'aftersales'),
            'col_website_name'=>__('Website Name', 'aftersales'),
            'col_step'=>__('URL - Step', 'aftersales'),
            'col_message_key'=>__('Message Key', 'aftersales'),
            'col_prepared_data'=>__('Prepared Data / API Response', 'aftersales'),
        ];

        return $columns;
    }

    /**
     *
     * @return array
     */
    protected function get_sortable_columns() {
        return array(
            'col_id'    => array('id', false)
        );
    }

    function column_default($item, $column_name){
        switch($column_name){
            case 'col_id':
            case 'col_created_date':
            case 'col_ip_address':
            case 'col_website_name':
            case 'col_step':
            case 'col_message_key':
            case 'col_prepared_data':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    public function extra_tablenav($which)
    {
        if('top' == $which) {
            $from = $to = '';
            if(isset($_REQUEST['date_from']) && strlen($_REQUEST['date_from']) > 0) {
                $from = $_REQUEST['date_from'];
            }
            if(isset($_REQUEST['date_to']) && strlen($_REQUEST['date_to']) > 0) {
                $to = $_REQUEST['date_to'];
            }
            ?>
            <div class="alignleft actions daterangeactions">
                <label for="daterange-actions-picker"><?php _e('Filter', 'aftersales')?></label>
                <?php _e('From', 'aftersales')?><input autocomplete="off" type="text" name="date_from" id="daterange-actions-picker-from" placeholder="<?php _e('Date From', 'aftersales')?>" value="<?php echo $from; ?>" />
                <?php _e('To', 'aftersales')?><input autocomplete="off" type="text" name="date_to" id="daterange-actions-picker-to" placeholder="<?php _e('Date To', 'aftersales')?>" value="<?php echo $to; ?>" />
                <?php submit_button(__('Apply', 'aftersales'), 'action', 'show_date_range_records', false); ?>
            </div>
            <?php
            unset($from, $to);
        }
    }

    function getWhereClause() {
        $where_clause = "1";
        if(isset($_REQUEST['date_from']) && strlen($_REQUEST['date_from']) > 0) {
            $from = DateTime::createFromFormat('d/m/Y', $_REQUEST['date_from']);
            $where_clause .= sprintf(' AND created_date >= "%s 00:00:00"', $from->format('Y-m-d'));
        }
        if(isset($_REQUEST['date_to']) && strlen($_REQUEST['date_to']) > 0) {
            $to = DateTime::createFromFormat('d/m/Y', $_REQUEST['date_to']);
            $where_clause .= sprintf(' AND created_date <= "%s 23:59:59"', $to->format('Y-m-d'));
        }
        unset($from, $to);
        return $where_clause;
    }


    /**
    * Prepare the table with different parameters, pagination, columns and table elements
    */

    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        /*
        select
        id, created_date, ip_address, server_name as website_name, url as step,
        post_data as message_key, response_data as prepared_data
        FROM aftersales.wpasmtbl_request_debug_log
        where 1
        order by id desc limit 500;
        */

        $query = "SELECT id, created_date, ip_address, server_name as website_name, url as step,
        post_data as message_key, response_data as prepared_data FROM {$wpdb->prefix}request_debug_log";
        $count_query = "SELECT COUNT(1) AS total_records FROM {$wpdb->prefix}request_debug_log";

        $where_clause = $this->getWhereClause();

        $query.= ' WHERE '.$where_clause;
        $count_query.= ' WHERE '.$where_clause;

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result, latest first by default
        $orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'id';
        $order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : 'DESC';

        if(!empty($orderby) & !empty($order)) {
            $query.=' ORDER BY '.$orderby.' '.$order;
        }

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->get_var($count_query);

        //How many to display per page?
        $perpage = 50;

        //Which page is this?
        $paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';

        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ) {
            $paged = 1;
        }

        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);

        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)) {
            $offset=($paged-1)*$perpage;
            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }

        /* -- Register the pagination -- */

        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );

        //The pagination links are automatically built according to those parameters
        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);

        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results($query, ARRAY_A);
    }


    /**
     * Display the rows of records in the table
     * @return string, echo the markup of the rows
     */

    function display_rows() {
        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();


        //Loop for each record
        if(!empty($records)) {
            foreach($records as $rec) {
                //Open the line
                echo '<tr id="record_' . $rec['id'] . '">';
                foreach ( $columns as $column_name => $column_display_name ) {
                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    //Display the cell

                    switch ( $column_name ) {
                        case "col_id":
                            echo '<td '.$attributes.' width="10%">'.stripslashes($rec['id']).'</td>';
                            break;
                        case "col_created_date":
                            echo '<td '.$attributes.'>'.date_i18n( 'd-m-Y H:i:s', strtotime( $rec['created_date'] ) ).'</td>';
                            break;
                        case "col_ip_address":
                            echo '<td '.$attributes.'>'.stripslashes($rec['ip_address']).'</td>';
                            break;
                        case "col_website_name":
                            echo '<td '.$attributes.'>'.stripslashes($rec['website_name']).'</td>';
                            break;
                        case "col_step":
                            echo '<td '.$attributes.'>'.stripslashes($rec['step']).'</td>';
                            break;
                        case "col_message_key":
                            echo '<td '.$attributes.'>'.stripslashes($rec['message_key']).'</td>';
                            break;
                        case "col_prepared_data":
                            echo '<td '.$attributes.'><pre>'.stripslashes($rec['prepared_data']).'</pre></td>';
                            break;
                    }
                }
                //Close the line
                echo'</tr>';
            }
        }
    }
}