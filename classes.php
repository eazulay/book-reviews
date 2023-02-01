<?php
if ( !class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class BKRV_Reviews_List_Table extends WP_List_Table {
    public $prefix;
    public $action_confirmation_text = '';

    function __construct(){
        global $wpdb;
        parent::__construct( array(
            'singular' => 'bkrv_review',
            'plural' => 'bkrv_reviews',
            'ajax' => false
        ) );
        $this->prefix = $wpdb->prefix.'bkrv_';
    }

    function extra_tablenav( $which ){
        if ( $which == "top" ){
            //The code that goes before the table is here
            echo $this->action_confirmation_text;
        }
        /*if ( $which == "bottom" ){
            print_r($this->_column_headers);
        }*/
    }

    function prepare_items() {
        global $wpdb;
        $this->process_bulk_action();
        $query = "SELECT r.review_id, p.post_title, r.review_title, r.reviewer, r.review_date, r.first_published, r.status, r.created_date"
            . " FROM " . $this->prefix . "reviews r"
            . " LEFT JOIN " . $wpdb->posts . " p ON p.ID=r.book_id";
        $orderby = !empty( $_GET["orderby"] ) ? $wpdb->_real_escape( $_GET["orderby"] ) : 'review_id';
        $order = !empty( $_GET["order"] ) ? $wpdb->_real_escape( $_GET["order"] ) : 'ASC';
        if (!empty( $orderby ) & !empty( $order ))
            $query .= ' ORDER BY '.$orderby.' '.$order;
        $totalitems = $wpdb->query( $query );
        $perpage = 10;
        $paged = !empty( $_GET["paged"] ) ? $wpdb->_real_escape( $_GET["paged"] ) : '';
        if (empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 )
            $paged = 1;
        $totalpages = ceil( $totalitems / $perpage );
        if (!empty( $paged ) && !empty( $perpage ) ){
            $offset = ( $paged - 1 ) * $perpage;
            $query .= ' LIMIT ' . (int)$offset . ',' . (int)$perpage;
        }
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden = array();
        $this->items = $wpdb->get_results($query);
        $this->_column_headers = array( $columns, $hidden, $sortable, 'col_review_title' );
    }

    function get_columns() {
        return array(
            'cb'    => '',
            'col_review_title' => __('Title', 'book-reviews'),
            'col_book' => __('Book', 'book-reviews'),
            'col_reviewer' => __('Reviewer', 'book-reviews'),
            'col_review_date' => __('Review Date', 'book-reviews'),
            'col_first_published' => __('First Published', 'book-reviews'),
            'col_status' => __( 'Status', 'book-reviews' ),
            'col_date' => __( 'Date', 'book-reviews' )
        );
    }

    public function get_sortable_columns() {
        return array(
            'col_review_title' => array( 'review_title', false ),
            'col_book' => array( 'post_title', false ),
            'col_reviewer' => array( 'reviewer', false ),
            'col_review_date' => array( 'review_date', true ),
            'col_first_published' => array( 'first_published', false ),
            'col_status' => __( 'status', 'false' ),
            'col_date' => array( 'created_date', true )
        );
    }

    function display_rows() {
        $records = $this->items;
        list( $columns, $hidden ) = $this->get_column_info();
        if (!empty( $records )){
            foreach($records as $rec){
                $editlink = get_admin_url() . 'admin.php?page=' . $_REQUEST['page'] . '&action=edit&id=' . $rec->review_id;
                $deleteLink = get_admin_url() . 'admin.php?page=' . $_REQUEST['page'] . '&action=delete&id=' . $rec->review_id . '&_wpnonce=' . wp_create_nonce( 'delete_review' );
                echo '<tr id="record_' . $rec->review_id . '">';
                foreach($columns as $column_name => $column_display_name){
                    $classes = "$column_name column-$column_name";
                    $styles = "";
                    if (in_array( $column_name, $hidden ))
                        $styles = 'display:none;';
                    switch($column_name){
                        case 'cb':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, '' ) . '><input type="checkbox" name="selected[' . $rec->review_id . ']" /></td>';
                            break;
                        case 'col_review_title':
                            $classes .= ' row-title';
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, '' ) . '><a href="' . $editlink . '" aria-label="“' . stripslashes( $rec->review_title ) . '” (Edit)">' . stripslashes( $rec->review_title ) . '</a>';
                            echo $this->row_actions( array(
                                'edit' => '<a href="' . $editlink . '" aria-label="Edit “' . stripslashes( $rec->review_title ) . '”">Edit</a>',
                                'delete' => '<a href="' . $deleteLink . '" class="submitdelete" aria-label="Delete “' . stripslashes( $rec->review_title ) . '”">Delete</a>'
                            ) );
                            /*echo '<div class="row-actions">
                                <span class="edit"><a href="' . $editlink . '" aria-label="Edit “' . stripslashes( $rec->review_title ) . '”">Edit</a> | </span>
                                <span class="trash"><a href="' . $deleteLink . '" class="submitdelete" aria-label="Delete “' . stripslashes( $rec->review_title ) . '”">Delete</a></span>
                            </div>*/
                            echo '</td>';
                            break;
                        case 'col_book':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, $column_name ) . '>' . stripslashes( $rec->post_title ) . '</td>';
                            break;
                        case 'col_reviewer':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, $column_name ) . '>' . stripslashes( $rec->reviewer ) . '</td>';
                            break;
                        case 'col_review_date':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, $column_name ) . '>' . date( "d M Y", strtotime( $rec->review_date ) ) . '</td>';
                            break;
                        case 'col_first_published':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, $column_name ) . '>' . $rec->first_published . '</td>';
                            break;
                        case 'col_status':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, $column_name ) . '>' . $this->status_name( $rec->status );
                            break;
                        case 'col_date':
                            echo '<td ' . $this->bkrv_td_attributes( $classes, $styles, $column_name ) . '>' . $rec->created_date . '</td>';
                            break;
                    }
                }
                echo'</tr>';
            }
        }
    }
    
    function bkrv_td_attributes( $classes, $styles, $col_name ){
        $result = "";
        if (!empty( $classes ))
            $result .= "class='$classes'";
        if (!empty( $styles ))
            $result .= " style='$styles'";
        if (!empty( $col_name ))
            $result .= " data-colname='".$this->_column_headers[0][$col_name]."'";
        return $result;
    }

    function status_name( $status_code ){
        $status_name = $status_code;
        switch($status_code){
            case 'draft':
                $status_name = 'Draft';
                break;
            case 'publish':
                $status_name = 'Published';
                break;
        }
        return $status_name;
    }

    function get_bulk_actions(){
        return array(
            'delete' => __( 'Delete', 'book-reviews' )
        );
    }

    function process_bulk_action(){
        $action = $this->current_action();
        if (empty($action) || !wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ))
            return;
        switch($action){
            case 'delete':
                $this->delete_review( $_POST['selected'] );
                /*$selected_ids = join( ',', array_keys( $_POST['selected'] ) );
                $this->action_confirmation_text = 'Delete reviews with IDs: ' . $selected_ids;*/
                break;
        }
    }

    function delete_review($ids){
        global $wpdb;
        if (empty($ids))
            return;
        $prefix = $wpdb->prefix.'bkrv_';
        $where = "review_id";
        $rec_count = 1;
        if (is_array($ids)){
            $where .= ' IN (' . join( ',', array_keys( $ids ) ) . ')';
            $rec_count = count( $ids );
        }else{
            $where .= '=' . $ids;
        }
        $sql = "DELETE FROM " . $prefix . "reviews WHERE " . $where;
        $wpdb->query( $sql );
        $this->action_confirmation_text = _n( '1 Review deleted.', '%d Reviews deleted.', $rec_count, 'book-reviews' );
    }
}
