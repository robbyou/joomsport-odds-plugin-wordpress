<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-prediction-page-settings.php';
/*<!--jsonlyinproPHP-->*/
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-prediction-page-myleagues.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-prediction-page-openleagues.php';
/*<!--/jsonlyinproPHP-->*/

class JoomSportPredictionAdminInstall {
    
    public static function init(){
        //global $joomsportSettings;
        self::joomsport_languages();
        add_action( 'admin_menu', array('JoomSportPredictionAdminInstall', 'create_menu') );
        
        self::_defineTables();

    }


    public static function create_menu() {

        add_menu_page( __('JoomSport Predictions', 'joomsport-prediction'), __('JoomSport Predictions', 'joomsport-prediction'),
            'manage_options', 'joomsport_prediction', array('JoomSportPredictionAdminInstall', 'action'),'dashicons-icon-arrow-streamline-target');
        add_submenu_page( 'joomsport_prediction', __('Settings', 'joomsport-prediction'), __('Settings', 'joomsport-prediction'),
                'manage_options', 'joomsport_prediction_settings', array('JoomsportPredictionPageSettings', 'action') );
        
        /*<!--jsonlyinproPHP-->*/
        $settings = get_option("joomsport_prediction_settings","");
        $private_league = isset($settings["private_league"])?$settings["private_league"]:0;
        if($private_league){
            $obj = JSPredictionLeagues_Plugin::get_instance();
            $hook = add_submenu_page( 'joomsport_prediction', __( 'Private leagues', 'joomsport-prediction' ), __( 'Private leagues', 'joomsport-prediction' ), 'read', 'jsprediction-page-myleagues', function(){ $obj = JSPredictionLeagues_Plugin::get_instance();$obj->plugin_settings_page();});
            add_action( "load-$hook", function(){ $obj = JSPredictionLeagues_Plugin::get_instance();$obj->screen_option();}  );
            add_submenu_page( 'options.php', __( 'Private leagues New', 'joomsport-prediction' ), __( 'Private leagues New', 'joomsport-prediction' ), 'read', 'jsprediction-myleagues-form', array('JSPredictionLeaguesNew_Plugin', 'view'));

            $obj = JSPredictionOpenLeagues_Plugin::get_instance();
            $hook = add_submenu_page( 'joomsport_prediction', __( 'Open leagues', 'joomsport-prediction' ), __( 'Open leagues', 'joomsport-prediction' ), 'read', 'jsprediction-page-openleagues', function(){ $obj = JSPredictionOpenLeagues_Plugin::get_instance();$obj->plugin_settings_page();});
            add_action( "load-$hook", function(){ $obj = JSPredictionOpenLeagues_Plugin::get_instance();$obj->screen_option();}  );
        }
        /*<!--/jsonlyinproPHP-->*/
        
        // javascript
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-uidp-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        add_action('admin_enqueue_scripts', array('JoomSportPredictionAdminInstall', 'joomsport_admin_js'));
        add_action('admin_enqueue_scripts', array('JoomSportPredictionAdminInstall', 'joomsport_admin_css'));
        
    }

    public static function joomsport_fe_wp_head(){
        global $post,$post_type;
        $jsArray = array("jswprediction_league","jswprediction_round");
        if(in_array($post_type, $jsArray)){
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('jscssbtstrp',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/btstrp.css');
            wp_enqueue_style('jscssjoomsport',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/joomsport.css');
            wp_enqueue_style('jsprediction',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/prediction.css');
            wp_enqueue_script( 'jswprediction-predfe-js', plugins_url('../sportleague/assets/js/jsprediction.js', __FILE__), array( 'wp-i18n' ) );
            wp_set_script_translations('jswprediction-predfe-js', 'joomsport-prediction');
            wp_enqueue_style('jscssfont','//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
        }
             
     }

    public static function action(){
    
    }
    public static function joomsport_languages() {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'joomsport-prediction' );

            load_textdomain( 'joomsport-prediction', plugin_basename( dirname( __FILE__ ) . "/../languages/joomsport-prediction-$locale.mo" ));
            load_plugin_textdomain( 'joomsport-prediction', false, plugin_basename( dirname( __FILE__ ) . "/../languages" ) );
    }
    
    public static function joomsport_admin_js(){
        global $post_type;
        wp_enqueue_script( 'jswprediction-common-js', plugins_url('../assets/js/common.js', __FILE__) );
        wp_enqueue_media();
    }
    
    public static function joomsport_admin_css(){
        wp_enqueue_style( 'jswprediction-common-css', plugins_url('../assets/css/common.css', __FILE__) );
        wp_register_style('jswprediction-icons-css', plugins_url('../assets/css/iconstyles.css', __FILE__));
        wp_enqueue_style('jswprediction-icons-css');
        
    }
    
    public static function _defineTables()
    {
            global $wpdb;
            $wpdb->jswprediction_league = $wpdb->prefix . 'jswprediction_league';
            $wpdb->jswprediction_round = $wpdb->prefix . 'jswprediction_round';
            $wpdb->jswprediction_round_matches = $wpdb->prefix . 'jswprediction_round_matches';
            $wpdb->jswprediction_round_users = $wpdb->prefix . 'jswprediction_round_users';
            $wpdb->jswprediction_types = $wpdb->prefix . 'jswprediction_types';
            $wpdb->jswprediction_private_league = $wpdb->prefix . 'jswprediction_private_league';
            $wpdb->jswprediction_private_based = $wpdb->prefix . 'jswprediction_private_based';
            $wpdb->jswprediction_private_users = $wpdb->prefix . 'jswprediction_private_users';
            $wpdb->jswprediction_scorepredict = $wpdb->prefix . 'jswprediction_scorepredict';
            
            
            
    }

    public static function _installdb(){
        global $wpdb;
        flush_rewrite_rules();
        self::_defineTables();
        
        include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );

        $charset_collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
                if ( ! empty($wpdb->charset) )
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                if ( ! empty($wpdb->collate) )
                        $charset_collate .= " COLLATE $wpdb->collate";
        }


        $create_config_sql = "CREATE TABLE {$wpdb->jswprediction_league} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `name` varchar(255) NOT NULL,
                                        `seasons` varchar(50) NOT NULL,
                                        `predictions` varchar(255) NOT NULL,
                                        `options` varchar(255) NOT NULL,
                                        PRIMARY KEY ( `id` )) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_league, $create_config_sql );

        
        $create_config_sql = "CREATE TABLE {$wpdb->jswprediction_round} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `rname` varchar(255) NOT NULL,
                                        `ordering` tinyint(4) NOT NULL,
                                        `league_id` int(11) NOT NULL,
                                        `closedate` int(11) NOT NULL,
                                        PRIMARY KEY ( `id` )) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_round, $create_config_sql );
        
        $create_ef_sql = "CREATE TABLE {$wpdb->jswprediction_round_matches} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `round_id` int(11) NOT NULL,
                                        `match_id` int(11) NOT NULL,
                                        PRIMARY KEY ( `id` )) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_round_matches, $create_ef_sql );
        
        $create_ef_select_sql = "CREATE TABLE {$wpdb->jswprediction_round_users} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `user_id` int(11) NOT NULL,
                                        `round_id` int(11) NOT NULL,
                                        `prediction` text NOT NULL,
                                        `editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                        `filldate` timestamp NULL DEFAULT NULL,
                                        `points` int(11) NOT NULL,
                                        `place` smallint(4) NOT NULL,
                                        `filled` smallint(4) NOT NULL DEFAULT '0',
                                        `options` text NOT NULL,
                                        `success` smallint(4) NOT NULL DEFAULT '0',
                                        PRIMARY KEY  (`id`),UNIQUE KEY `user_id` (`user_id`,`round_id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_round_users, $create_ef_select_sql );
        
        $create_events_sql = "CREATE TABLE {$wpdb->jswprediction_types} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `name` varchar(150) NOT NULL,
                                        `identif` varchar(100) NOT NULL,
                                        `ptype` varchar(100) NOT NULL,
                                        `ordering` tinyint(4) NOT NULL,
                                        `showtype` varchar(1) NOT NULL DEFAULT '0',
                                        `options` text NOT NULL,
                                        PRIMARY KEY  (`id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_types, $create_events_sql );
        
        if(!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->jswprediction_types}")){
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 1, 'name' => esc_attr('Exact Result'), 'identif' => 'ScoreExact', 'ptype' => 'score', 'ordering' => 0, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 2, 'name' => esc_attr('Winner & Score difference'), 'identif' => 'ScoreSideAndDiff', 'ptype' => 'score', 'ordering' => 1, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 3, 'name' => esc_attr('Correct winner'), 'identif' => 'ScoreWinner', 'ptype' => 'score', 'ordering' => 2, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));
            
        }
        
        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->jswprediction_round_users} LIKE 'winner_side'");
        
        if (empty($is_col)) {
            $wpdb->query("ALTER TABLE ".$wpdb->jswprediction_round_users." ADD `winner_side` SMALLINT NOT NULL DEFAULT '0' , ADD `score_diff` SMALLINT NOT NULL DEFAULT '0'");

            
        }
        /*<!--jsonlyinproPHP-->*/
        
        $create_jswprediction_private_league_sql = "CREATE TABLE {$wpdb->jswprediction_private_league} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `leagueName` varchar(255) NOT NULL,
                                        `is_private` varchar(1) NOT NULL DEFAULT '0',
                                        `creatorID` int(11) NOT NULL,
                                        `options` text NOT NULL,
                                        PRIMARY KEY  (`id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_private_league, $create_jswprediction_private_league_sql );
        
        $create_jswprediction_private_league_sql = "CREATE TABLE {$wpdb->jswprediction_private_based} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `leagueID` int(11) NOT NULL,
                                        `privateID` int(11) NOT NULL,
                                        PRIMARY KEY  (`id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_private_based, $create_jswprediction_private_league_sql );
        
        $create_jswprediction_private_league_sql = "CREATE TABLE {$wpdb->jswprediction_private_users} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `privateID` int(11) NOT NULL,
                                        `userID` int(11) NOT NULL,
                                        `confirmed` varchar(1) NOT NULL DEFAULT '0',
                                        PRIMARY KEY  (`id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_private_users, $create_jswprediction_private_league_sql );
        
        
        $create_jswprediction_score_sql = "CREATE TABLE {$wpdb->jswprediction_scorepredict} (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `match_id` int(11) NOT NULL,
                                      `user_id` int(11) NOT NULL,
                                      `score1` smallint(6) NULL DEFAULT NULL,
                                      `score2` smallint(6) NULL DEFAULT NULL,
                                      PRIMARY KEY  (`id`),
                                      KEY `match_id` (`match_id`)
                                    )";
        maybe_create_table( $wpdb->jswprediction_scorepredict, $create_jswprediction_score_sql );

        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->jswprediction_private_league} LIKE 'invitekey'");

        if (empty($is_col)) {
            $wpdb->query("ALTER TABLE ".$wpdb->jswprediction_private_league." ADD `invitekey` VARCHAR(100) NOT NULL DEFAULT ''");


        }

        if(!$wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_types} WHERE 'identif' => 'ScoreBonus'")){
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 4, 'name' => esc_attr('Bonus for correct goals guess'), 'identif' => 'ScoreBonus', 'ptype' => 'score', 'ordering' => 3, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));

        }


        /*<!--/jsonlyinproPHP-->*/
    }
    

}

add_action( 'init', array( 'JoomSportPredictionAdminInstall', 'init' ), 4);
add_action( 'wp_enqueue_scripts', array('JoomSportPredictionAdminInstall','joomsport_fe_wp_head') );



function jspred_deactivation_popup() {
    $ignorePop = get_option('jspred_deactivation_popup',0);
    if(!$ignorePop){
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_script( 'utils' ); // for user settings
    ?>
        <script type="text/javascript">
        jQuery('tr[data-slug="joomsport-prediction"] .deactivate a').click(function(){
            var content_html = '<h3><?php echo __('Please share the reason of deactivation.','joomsport-prediction')?></h3>';
            content_html += '<div class="jsportPopUl"><ul style="overflow:hidden;">';
            content_html += '<li><input id="jsDeactivateReason1" type="radio" name="jsDeactivateReason" value="1" /><label for="jsDeactivateReason1"><?php echo __('Plugin is too complicated','joomsport-prediction')?></label><textarea name="jsDeactivateReason1_text" id="jsDeactivateReason1_text" placeholder="<?php echo __('What step did actually stop you?','joomsport-prediction')?>"></textarea></li>';
            content_html += '<li><input id="jsDeactivateReason2" type="radio" name="jsDeactivateReason" value="2" /><label for="jsDeactivateReason2"><?php echo __('I miss some features','joomsport-prediction')?></label><textarea name="jsDeactivateReason2_text" id="jsDeactivateReason2_text" placeholder="<?php echo __('What features did you miss?','joomsport-prediction')?>"></textarea></li>';
            content_html += '<li><input id="jsDeactivateReason3" type="radio" name="jsDeactivateReason" value="3" /><label for="jsDeactivateReason3"><?php echo __('I found the other plugin','joomsport-prediction')?></label><textarea name="jsDeactivateReason3_text" id="jsDeactivateReason3_text" placeholder="<?php echo __('What plugin did you prefer?','joomsport-prediction')?>"></textarea></li>';
            content_html += '<li><input id="jsDeactivateReason4" type="radio" name="jsDeactivateReason" value="4" /><label for="jsDeactivateReason4"><?php echo __('It is not working as expected','joomsport-prediction')?></label><textarea name="jsDeactivateReason4_text" id="jsDeactivateReason4_text" placeholder="<?php echo __('What was wrong?','joomsport-prediction')?>"></textarea></li>';
            content_html += '<li><input id="jsDeactivateReason5" type="radio" name="jsDeactivateReason" value="5" /><label for="jsDeactivateReason5"><?php echo __('Other','joomsport-prediction')?></label><textarea name="jsDeactivateReason5_text" id="jsDeactivateReason5_text" placeholder="<?php echo __('What is the reason?','joomsport-prediction')?>"></textarea></li>';
            content_html += '</ul></div>';
            content_html += '<div style="text-align:center;"><?php echo __('THANK YOU IN ADVANCE!','joomsport-prediction')?></div>';
            content_html += '<p><input id="jsDeactivateOpt1" type="checkbox" name="jsDeactivateOpt1" value="1" /><label for="jsDeactivateOpt1"><?php echo __('Do not show again','joomsport-prediction')?></label></p>';
            content_html += '<p><a id="jsportPopSkip" class="button" href="'+jQuery('tr[data-slug="joomsport-prediction"] .deactivate a').attr('href')+'"><?php echo __('Skip','joomsport-prediction')?></a>';
            content_html += '<a id="jsportPopSend" class="button-primary button" href="'+jQuery('tr[data-slug="joomsport-prediction"] .deactivate a').attr('href')+'"><?php echo __('Send','joomsport-prediction')?></a></p>';    
            content_html += '<p class="joomsportPopupPolicy"><a href="http://joomsport.com/send-form-privacy.html" target="_blank"><?php echo __('Send Form Privacy Policy','joomsport-prediction')?></a></p>';
                jQuery('tr[data-slug="joomsport-prediction"] .deactivate a').pointer({
                    content: content_html,
                    position: {
                        my: 'left top',
                        at: 'center bottom',
                        offset: '-1 0'
                    },
                    close: function() {
                        //
                    }
                }).pointer('open');
            return false;
            });
        </script><?php
    }
}
add_action( 'admin_footer', 'jspred_deactivation_popup' );

add_action( 'wp_ajax_jspred-updoption', 'jspred_update_option' );
function jspred_update_option() {
    $option_name = 'jspred_deactivation_popup';
    $option = intval($_POST['option']);
    

    update_option( $option_name, $option );
    die();
}

add_action( 'wp_ajax_jspred-senddeactivation', 'jspred_senddeactivation' );
function jspred_senddeactivation() {
    global $current_user;
    get_currentuserinfo();
    if($current_user->user_email){
        $ch_type = intval($_POST['ch_type']);
        $reason = '';
        switch($ch_type){
            case '1':
                $reason = __('Plugin is too complicated','joomsport-prediction');
                break;
            case '2':
                $reason = __('I miss some features','joomsport-prediction');
                break;
            case '3':
                $reason = __('I found the other plugin','joomsport-prediction');
                break;
            case '4':
                $reason = __('It is broken','joomsport-prediction');
                break;
            case '5':
                $reason = __('Other','joomsport-prediction');
                break;
        }
        $ch_text = ($_POST['ch_text']);
        $to = 'deactivate-pred@beardev.com';
        $subject = 'JoomSport Prediction Deactivation';
        $body = $reason . ":<br /><br />" . $ch_text;
        $headers = array('Content-Type: text/html; charset=UTF-8','FROM:'.$current_user->user_email);

        wp_mail( $to, $subject, $body, $headers );
    }
    die();
}
