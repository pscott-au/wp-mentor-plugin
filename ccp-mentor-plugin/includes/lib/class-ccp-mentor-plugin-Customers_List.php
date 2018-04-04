<?php
// Based on https://github.com/collizo4sky/WP_List_Table-Class-Plugin-Example/blob/master/plugin.php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Customers_List extends WP_List_Table {

    public $status_filter = 1;
	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Mentor', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Mentors', 'sp' ), //plural name of the listed records
			'ajax'     => true //does this table support ajax?
		] );
	}
	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_customers( $per_page = 5, $page_number = 1, $status_filter ) {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}mentee_eois WHERE status = " . $status_filter . ' ';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}




	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function inc_customer_status( $id ) {
		global $wpdb;
        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mentee_eois SET status = status + 1 WHERE id = %d", $id) );
    }




	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $id ) {
		global $wpdb;
        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mentee_eois SET status = status - 1 WHERE id = %d", $id) );        
      /**
       * $wpdb->update( "{$wpdb->prefix}mentee_eois",
       *                ['status' => -1 ], 
       *                [ 'id' => $id ], 
       *                [ '%d' ],
       *                [ '%d' ] );
		* $wpdb->delete(
		*	"{$wpdb->prefix}mentee_eois",
		*	[ 'id' => $id ],
		*	[ '%d' ]
		* );
        **/
	}
	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}mentee_eois";
		return $wpdb->get_var( $sql );
	}
	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No Mentees avaliable.', 'sp' );
	}
	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
            case 'email': 
            case 'abn': 
			case 'business_name':
			case 'formatted_address':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
        //!PSreturn '';
		return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']) ;
         // . sprintf('<input type="checkbox" name="bulk-approve[]" value="%s" />', $item['ID']) ;
	}
	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );
        // $approve_nonce =  wp_create_nonce( 'sp_approve_customer' );
		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			 'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Deny</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];
        if ( $this->status_filter < 1 )
        {
            $actions['edit'] = sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Approve</a>', esc_attr( $_REQUEST['page'] ), 
                   'approve', absint( $item['id'] ), $delete_nonce );
            // array_push($actions,  );
        }
		return $title . $this->row_actions( $actions );
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Name', 'sp' ),
			'business_name' => __( 'Business', 'sp' ),
			'formatted_address'    => __( 'Address', 'sp' ),
            'abn'     => __( 'ABN', 'sp' ),
            'email'   => __( 'Email', 'sp' ),
		];
		return $columns;
	}
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', true ),
			'business_name' => array( 'business_name', false )
		);
		return $sortable_columns;
	}
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete',
            'bulk-approve' => 'Approve'
		];
		return $actions;
	}
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();
		/** Process bulk action */
		$this->process_bulk_action();
		$per_page     = $this->get_items_per_page( 'customers_per_page', 25 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();
		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );
		$this->items = self::get_customers( $per_page, $current_page, $this->status_filter );
	}
	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...

		if ( 'approve' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {

				self::inc_customer_status( absint( $_GET['customer'] ) );
		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
                        // error_log( esc_url_raw(add_query_arg()) );
		                // wp_redirect( esc_url_raw(add_query_arg()) );
                        //return '/wp-admin/admin.php?page=wp_list_table_class&action=delete&customer=4&_wpnonce=0dcf9c0beb';
                        return;
				exit;
			}
		}




		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {

				self::delete_customer( absint( $_GET['customer'] ) );
		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
                        // error_log( esc_url_raw(add_query_arg()) );
		                // wp_redirect( esc_url_raw(add_query_arg()) );
                        //return '/wp-admin/admin.php?page=wp_list_table_class&action=delete&customer=4&_wpnonce=0dcf9c0beb';
                        return;
				exit;
			}
		}
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
            // error_log('test' );
			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_customer( $id );
			}
			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        //wp_redirect( esc_url_raw(add_query_arg()) );
                return;
			exit;
		}
	}
}