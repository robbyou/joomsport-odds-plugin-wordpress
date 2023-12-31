<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomSportModerMday{
    public static function showMdays(){
        $obj = new JoomSportMdayModer_Plugin();
        $obj->screen_option();
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'eview'){
            $obj->plugin_eview_page();

        }else{
            $obj->plugin_settings_page();
        }
        
    }
    
    
}

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class JoomSportMdayModer_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct( array(
                'singular' => __( 'Matchday', 'joomsport-sports-league-results-management' ), 
                'plural'   => __( 'Matchdays', 'joomsport-sports-league-results-management' ),
                'ajax'     => false 

        ) );

        /** Process bulk action */
        $this->process_bulk_action();

    }

    public static function get_stages( $per_page = 5, $page_number = 1 ) {

        global $wpdb;
        $season_id = isset($_REQUEST['season_id'])?intval($_REQUEST['season_id']):0;
        $canAddMatches = JoomSportUserRights::canAddMatches();
        $my_posts = JoomSportUserRights::getUserPosts();

        //wp_term_taxonomy
        $sql = "SELECT t.term_id as id, t.name as e_name"
                . " FROM {$wpdb->term_taxonomy} as tt"
                . " JOIN {$wpdb->terms} as t ON t.term_id = tt.term_id"
                . " WHERE tt.taxonomy = 'joomsport_matchday'";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . sanitize_sql_orderby( "{$_REQUEST['orderby']} {$_REQUEST['order']}" );

        }
        if(!$season_id){
            $sql .= " LIMIT $per_page";

            $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        }
        
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        if(!$canAddMatches && count($my_posts)){
            $metaquery[] =
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_joomsport_home_team',
                        'value' => $my_posts,
                        'compare' => 'IN'
                    ),

                    array(
                        'key' => '_joomsport_away_team',
                        'value' => $my_posts,
                        'compare' => 'IN'
                    )
                ) ;
            $matches = new WP_Query(array(
                'post_type' => 'joomsport_match',
                'posts_per_page'   => -1,
                'author' => get_current_user_id(),
                'post_status' => 'publish',

                'meta_query' => $metaquery
            ));

            $resultNew = array();

            for($intA=0;$intA<count($matches->posts);$intA++) {
                $md = get_the_terms($matches->posts[$intA]->ID, 'joomsport_matchday');
                if (isset($md[0])) {
                    $resultNew[$md[0]->term_id] = array("id" => $md[0]->term_id, "e_name" => $md[0]->name);

                }
            }

            //$resultNew = array_unique($resultNew);
            $resultNew = array_values($resultNew);
            $result = $resultNew;

        }

        if($season_id){
            $seasres = array();
            for($intA = 0; $intA < count($result); $intA++){

                $metas = get_option("taxonomy_{$result[$intA]['id']}_metas");
                $md_season_id = $metas['season_id'];

                if($md_season_id == $season_id){

                        $seasres[] = $result[$intA];

                    
                }
            }

            return $seasres;
        }
        



        return $result;
    }
    public static function delete_stage( $id ) {
        global $wpdb;

    }
    public static function record_count() {
        global $wpdb;
        $season_id = isset($_REQUEST['season_id'])?intval($_REQUEST['season_id']):0;
        $canAddMatches = JoomSportUserRights::canAddMatches();
        $my_posts = JoomSportUserRights::getUserPosts();
        
        $sql = "SELECT t.term_id as id, t.name as e_name"
                . " FROM {$wpdb->term_taxonomy} as tt"
                . " JOIN {$wpdb->terms} as t ON t.term_id = tt.term_id"
                . " WHERE tt.taxonomy = 'joomsport_matchday'";



        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        if(!$canAddMatches && count($my_posts)){
            $metaquery[] =
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_joomsport_home_team',
                        'value' => $my_posts,
                        'compare' => 'IN'
                    ),

                    array(
                        'key' => '_joomsport_away_team',
                        'value' => $my_posts,
                        'compare' => 'IN'
                    )
                ) ;
            $matches = new WP_Query(array(
                'post_type' => 'joomsport_match',
                'posts_per_page'   => -1,
                'author' => get_current_user_id(),
                'post_status' => 'publish',

                'meta_query' => $metaquery
            ));

            $resultNew = array();

            for($intA=0;$intA<count($matches->posts);$intA++) {
                $md = get_the_terms($matches->posts[$intA]->ID, 'joomsport_matchday');
                if (isset($md[0])) {
                    $resultNew[] = $md[0]->term_id;

                }
            }
            $resultNew = array_unique($resultNew);
            $resultNew = array_values($resultNew);
            $result = $resultNew;

        }

        if($season_id){
            $seasres = array();
            for($intA = 0; $intA < count($result); $intA++){
                $metas = get_option("taxonomy_{$result[$intA]['id']}_metas");
                $md_season_id = $metas['season_id'];
                if($md_season_id == $season_id){
                    $seasres[] = $result[$intA];
                }
            }
            return count($seasres);
        }
        return count($result);
        
    }
    public function no_items() {
        echo __( 'No matchdays avaliable.', 'joomsport-sports-league-results-management' );
    }
    function column_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'joomsport_delete_event' );

        $title = '<strong><a href="'.get_admin_url(get_current_blog_id(), 'admin.php?page=joomsport_mday_moder&action=eview&id='.absint( $item['id'] ).'').'">' . $item['e_name'] . '</a></strong>';

        $actions = array();

        return $title . $this->row_actions( $actions );
    }
    
    function column_cb( $item ) {
        return '';
    }
    function get_columns() {
        $columns = array(
          'name'    => __( 'Name', 'joomsport-sports-league-results-management' ),
          'season_name'    => __( 'Season', 'joomsport-sports-league-results-management' ),  
        );

        return $columns;
    }
    function column_default($item, $column_name){
        switch($column_name){

            case 'season_name':
                $metas = get_option("taxonomy_{$item['id']}_metas");

                return get_the_title($metas['season_id']);

            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    public function get_sortable_columns() {
        $sortable_columns = array(
          'name' => array( 'name', true )
        );

        return $sortable_columns;
    }
    public function get_bulk_actions() {
        $actions = array(
        );

        return $actions;
    }
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $season_id = isset($_REQUEST['season_id'])?intval($_REQUEST['season_id']):0;

        $per_page     = $this->get_items_per_page( 'jsevents_per_page', 20 );
        if($season_id){
            $per_page = 100;
        }
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( array(
          'total_items' => $total_items, //WE have to calculate the total number of items
          'per_page'    => $per_page //WE have to determine how many items to show on a page
        ) );


        $this->items = self::get_stages( $per_page, $current_page );

    }
    
    
    
    public function process_bulk_action() {
        if ( 'eview' === $this->current_action() ) {
            

        }
    }
    
}


class JoomSportMdayModer_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		//add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
            /*<!--jsonlyinproPHP-->*/
            $season_id = isset($_REQUEST['season_id'])?intval($_REQUEST['season_id']):0;
		?>
		<div class="wrap">
			<h2><?php echo __('Matchdays', 'joomsport-sports-league-results-management');?>
                        </h2>
                        
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
                                                            <?php
                                                            $results = JoomSportHelperObjects::getSeasons(-1, false);
                                                            ?>
                                                            <div class="form-field" style="float:right;">    

                                                                <label for="season_id"><?php echo __('Season', 'joomsport-sports-league-results-management'); ?></label>

                                                                <?php
                                                                    echo wp_kses(JoomSportHelperSelectBox::Optgroup('season_id', $results,$season_id,' id="season_id" onchange="this.form.submit();"',true,''), JoomsportSettings::getKsesSelect());
                                                                ?>

                                                            </div>
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
        /*<!--/jsonlyinproPHP-->*/
        /*<!--jsaddlinkDIVPHP-->*/
	}
        
        /**
	 * Plugin settings page
	 */
	public function plugin_eview_page() {
            /*<!--jsonlyinproPHP-->*/
            $term_id = isset($_REQUEST['id'])?intval($_REQUEST['id']):0;
            if(!$term_id){
                return;
            }
            $term_obj = get_term($term_id, 'joomsport_matchday');
            
            if(isset($_REQUEST['eviewTask']) && $_REQUEST['eviewTask'] == 'update'){
                JoomSportClassMatchday::save($term_id);
            }
            
		?>
		<div class="wrap">
			<h2>
                            <?php echo esc_html($term_obj->name);?>
                        </h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post" id="edittag">

                                                            <div>
                                                            <?php echo JoomSportClassMatchday::getViewEdit($term_id);?>
                                                            </div>
                                                            <?php
                                                            $metas = get_option("taxonomy_{$term_id}_metas");
                                                            if(!$metas['matchday_type']){
                                                            ?>
                                                                <input type="hidden" name="tag_ID" value="<?php echo esc_attr($term_id);?>">
                                                                <input type="hidden" name="eviewTask" value="update">
                                                                <input type="submit" name="submit" id="submit" class="button button-primary" value="Update">
                                                            <?php
                                                            }
                                                            ?>
                                                            <div id="modalAj"><!-- Place at bottom of page --></div>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
        /*<!--/jsonlyinproPHP-->*/
        /*<!--jsaddlinkDIVPHP-->*/
	}
        
	/**
	 * Screen options
	 */
	public function screen_option() {
            if(isset($_POST['wp_screen_options']['option'])){
                update_user_meta(get_current_user_id(), 'jsevents_per_page', intval($_POST['wp_screen_options']['value']));

            }
            $option = 'per_page';
            $args   = array(
                    'label'   => 'Matchdays',
                    'default' => 20,
                    'option'  => 'jsevents_per_page'
            );

            add_screen_option( $option, $args );

            $this->customers_obj = new JoomSportMdayModer_List_Table();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
