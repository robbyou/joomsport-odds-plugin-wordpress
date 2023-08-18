<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
/*<!--jsonlyinproPHP-->*/
class JoomsportPredictionShortcodes {

    public static function init() {
        $settings = get_option("joomsport_prediction_settings","");

        $private_league = isset($settings["private_league"])?$settings["private_league"]:0;
        if($private_league) {

            add_shortcode('jsPrivateLeagues', array('JoomsportPredictionShortcodes', 'jsPrivateLeagues'));
        }
    }

    public static function jsPrivateLeagues(){
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jsselect2',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/js/select2.min.js');
        wp_enqueue_style('jscssselect2',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/select2.min.css');
        wp_enqueue_script('jsbootstrap-js','https://maxcdn.bootstrapcdn.com/bootstrap/4.2.0/js/bootstrap.min.js',array ( 'jquery' ));
        wp_enqueue_script( 'jswprediction-predfe-js', plugins_url('../sportleague/assets/js/jsprediction.js', __FILE__), array( 'wp-i18n', 'jquery', 'jsbootstrap-js' ) );
        wp_set_script_translations('jswprediction-predfe-js', 'joomsport-prediction');
        wp_enqueue_style('jsprediction',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/prediction.css');
        wp_enqueue_style('jscssfont','//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');

        $wp_scripts = wp_scripts();
        wp_enqueue_style(
            'jquery-ui-theme-smoothness',
            sprintf(
                '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', // working for https as well now
                $wp_scripts->registered['jquery-ui-core']->ver
            )
        );

        wp_enqueue_style('jscssbtstrp',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/btstrp.css');
        wp_enqueue_style('jscssjoomsport',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/joomsport.css');
        if (is_rtl()) {
          wp_enqueue_style( 'jscssjoomsport-rtl',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/joomsport-rtl.css', array('jscssjoomsport'));
        }
        require_once JOOMSPORT_PREDICTION_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'sportleague.php';
        require_once JOOMSPORT_PREDICTION_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class-jsprediction-myleagues.php';
        require_once JOOMSPORT_PREDICTION_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class-jsprediction-league-row.php';

        $activeLeagues = jsPredictionMyLeagues::getActiveLeaguesList();
        $archiveLeagues = jsPredictionMyLeagues::getArchiveLeaguesList();
        $invitedLeague = jsPredictionMyLeagues::getInvitedLeagues();
        ob_start();
        require JOOMSPORT_PREDICTION_PATH_VIEWS . 'widgets' . DIRECTORY_SEPARATOR . 'privateleague.php';
        $res = ob_get_contents();
        
        ob_end_clean();
        return $res;
    }


}


JoomsportPredictionShortcodes::init();
/*<!--/jsonlyinproPHP-->*/