<?php
function aftersales_admin_menu() {
    add_menu_page(
        __('Check Log Records', 'aftersales'),
        __('Log Records', 'aftersales'),
        'manage_options',
        'check_log_records',
        'aftersales_log_records'
    );
}
add_action( 'admin_menu', 'aftersales_admin_menu' );

function aftersales_log_records() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    require_once ( dirname(__file__) . '/class-log-records-list-table.php' );
    $wp_list_table = new Log_Records_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $wp_list_table->prepare_items();
    ?>
    <div class="wrap">
        <h1><?php _e('Log Records', 'aftersales'); ?></h1>
        <form id="posts-filter" method="get">
            <input type="hidden" name="page" value="check_log_records" />
            <?php $wp_list_table->display(); ?>
        </form>
    </div>
    <?php
}



/**
 * Adds the datepicker settings to the admin footer.
 * Only loads on the plugin-name settings page
 */
function asm_admin_footer() {

    $screen = get_current_screen();

    if ( $screen->id == 'toplevel_page_check_log_records' ) {

        ?><script type="text/javascript">
            jQuery(document).ready(function(){
                var dateFormat = "dd/mm/yy",
                from = jQuery('#daterange-actions-picker-from')
                        .datepicker({
                            changeMonth: true,
                            dateFormat : dateFormat,
                            numberOfMonths: 2,
                            maxDate: "0"
                        })
                        .on('change', function() {
                            to.datepicker( 'option', 'minDate', getDate(this) );
                        }),
                to = jQuery('#daterange-actions-picker-to')
                        .datepicker({
                            changeMonth: true,
                            dateFormat : dateFormat,
                            numberOfMonths: 2,
                            maxDate: "0"
                        })
                        .on('change', function() {
                            from.datepicker( 'option', 'maxDate', getDate(this) );
                        });
                function getDate( element ) {
                  var date;
                  try {
                    date = jQuery.datepicker.parseDate( dateFormat, element.value );
                  } catch( error ) {
                    date = null;
                  }

                  return date;
                }
            });
        </script><?php

    }

} //  admin_footer()
add_action( 'admin_print_scripts', 'asm_admin_footer', 1000 );
/**
 * Enqueues the built-in Datepicker script
 * Only loads on the plugin-name settings page
 */
function asm_enqueue_scripts( $hook_suffix ) {
    $screen = get_current_screen();
    if ( $screen->id == $hook_suffix ) {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'asm-admin-ui-css', get_template_directory_uri() . '/css/jquery-ui.theme.css', false, "1.11.4", false );
    }
} // enqueue_scripts()
add_action( 'admin_enqueue_scripts', 'asm_enqueue_scripts' );


add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts() {
  echo '<style>
    #col_website_name {
        width: 15%;
    }

    #col_prepared_data {
        width: 40%;
    }
  </style>';
}