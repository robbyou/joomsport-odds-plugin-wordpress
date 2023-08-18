<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class jsPredictionHelper
{
    public static function JsHeader($options)
    {

        $kl = '';
        if (classJsportRequest::get('tmpl') != 'component') {
            $kl .= '<div class="">';
            $kl .= '<nav class="navbar navbar-default navbar-static-top" role="navigation">';
            $kl .= '<div class="navbar-header navHeadFull">';

            $brand = '';

            $kl .= '<ul class="nav navbar-nav pull-right navSingle">';
            
            if (isset($options['prleaders']) && $options['prleaders']) {
                $link = get_permalink($options['prleaders']);
                $prl = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
                if($prl){
                    $link = add_query_arg( 'prl', $prl, $link );
                }
                //$link = add_query_arg( 'action', 'calendar', $link );
                $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-stand"></i>'.__('Leaderboard','joomsport-prediction').'</a>';
            }
            /*<!--jsonlyinproPHP-->*/
            if (isset($options['privateleague']) && $options['privateleague']) {
                $settings = get_option("joomsport_prediction_settings","");
        
                $private_league = isset($settings["private_league"])?$settings["private_league"]:0;
                if($private_league){
                    $link = admin_url('admin.php?page=jsprediction-page-myleagues');
                    $settings = get_option("joomsport_prediction_settings","");
                    if(isset($settings["plrivate_league_shortcode_link"]) && $settings["plrivate_league_shortcode_link"]){
                        $link = get_site_url()."/".$settings["plrivate_league_shortcode_link"];
                    }

                    //$link = add_query_arg( 'action', 'calendar', $link );
                    $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-squad"></i>'.__('Private leagues','joomsport-prediction').'</a>';
                }
            }
            if (isset($options['global_classification']) && $options['global_classification']) {
                $link = get_permalink($options['global_classification']);
                $link = add_query_arg( 'gc', '1', $link );
                $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="fa fa-user"></i>'.__('Global classification','joomsport-prediction').'</a>';
            }
            /*<!--/jsonlyinproPHP-->*/
            if (isset($options['userleague']) && $options['userleague']) {
                $link = get_permalink($options['userleague']);
                $link = add_query_arg( 'action', 'rounds', $link );
                $prl = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
                if($prl){
                    $link = add_query_arg( 'prl', $prl, $link );
                }
                $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-itlist"></i>'.__('Round list','joomsport-prediction').'</a>';
            }
            
            $kl .= '</ul></div></nav></div>';
        }
        //$kl .= self::JsHistoryBox($options);
        $kl .= self::JsTitleBox($options);
        $kl .= "<div class='jsClear'></div>";

        return $kl;
    }

    public static function JsTitleBox($options)
    {
        $kl = '';
        $kl .= '<div class="heading col-xs-12 col-lg-12">
                    <div class="heading col-xs-6 col-lg-6">
                        <!--h2>
                           
                        </h2-->
                    </div>
                    <div class="selection col-xs-6 col-lg-6 pull-right">
                        <form method="post">
                            <div class="data">
                                '.(isset($options['tourn']) ? $options['tourn'] : '').'
                                <input type="hidden" name="jscurtab" value="" />    
                            </div>
                        </form>
                    </div>
                </div>';

        return $kl;
    }

    public static function JsHistoryBox($options)
    {
        $kl = '<div class="history col-xs-12 col-lg-12">
          <ol class="breadcrumb">
            <li><a href="javascript:void(0);" onclick="history.back(-1);" title="[Back]">
                <i class="fa fa-long-arrow-left"></i>[Back]
            </a></li>
          </ol>
          <div class="div_for_socbut">'.(isset($options['print']) ? '' : '').'<div class="jsClear"></div></div>
        </div>';

        return $kl;
    }

    

    public static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT']);
    }
    /*<!--jsonlyinproPHP-->*/
    public static function getPostsAsArray($posts){
        $returnArr = array();
        for($intA=0;$intA<count($posts);$intA++){
            $returnArr[] = $posts[$intA]->ID;
        }
        return $returnArr;
    }

    public static function getActiveMainLeaguesList(){

        $metaquery = array();
        $metaquery[] =
            array(
                'relation' => 'OR',
                array(
                    'key' => '_jswprediction_league_archive',
                    'value' => 0,
                    'compare' => '='
                ),

                array(
                    'key' => '_jswprediction_league_archive',
                    'compare' => 'NOT EXISTS'
                )

            ) ;

        $leaguesPosts = new WP_Query(array(
            'post_type' => 'jswprediction_league',
            'posts_per_page'   => -1,
            'orderby' => 'post_date',
            'order'=>'ASC',
            'meta_query' => $metaquery
        ));

        return $leaguesPosts->posts;
    }
    public static function getArchiveMainLeaguesList(){
        $metaquery = array();
        $metaquery[] =
            array(
                'relation' => 'AND',
                array(
                    'key' => '_jswprediction_league_archive',
                    'value' => 1,
                    'compare' => '='
                )
            ) ;

        $leaguesPosts = new WP_Query(array(
            'post_type' => 'jswprediction_league',
            'posts_per_page'   => -1,
            'orderby' => 'post_date',
            'order'=>'ASC',
            'meta_query' => $metaquery
        ));

        return $leaguesPosts->posts;
    }

    public static function getOptionsFromPostList($posts){
        $returnArr = array();
        for($intA=0;$intA<count($posts);$intA++){
            $returnArr[] = JoomSportHelperSelectBox::addOption($posts[$intA]->ID, $posts[$intA]->post_title);
        }
        return $returnArr;
    }

    public static function addUserToPrivateLeague($LeagueId, $userId, $confirmed=1){
        if(intval($LeagueId) && intval($userId) && jsPredictionHelper::checkUserExist($userId)){
            global $wpdb;
            $curIDcount = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_private_users} WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));
            if($curIDcount > 1){
                $wpdb->query("DELETE FROM {$wpdb->jswprediction_private_users} WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));

            }

            $curID = $wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_private_users} WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));

            if($curID){
                $wpdb->query("UPDATE {$wpdb->jswprediction_private_users} SET confirmed=".intval($confirmed)." WHERE id=".$curID);
            }else{
                $wpdb->query("INSERT IGNORE INTO {$wpdb->jswprediction_private_users}(privateID,userID,confirmed)"
                    . " VALUES(".intval($LeagueId).",".intval($userId).",".intval($confirmed).")"
                    . " ON DUPLICATE KEY UPDATE confirmed=".intval($confirmed));
            }

            return true;
        }
        return false;
    }
    public static function removeUserToPrivateLeague($LeagueId, $userId){
        global $wpdb;
        $sql = "SELECT u.id "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->prefix}users as u ON u.id=p.creatorID"
            . " WHERE p.id=".$LeagueId
            . " LIMIT 1"        ;
        $creatorID =  $wpdb->get_var($sql);

        if($creatorID == $userId){
            return false;
        }
        if(intval($LeagueId) && intval($userId)){

            $wpdb->query("DELETE FROM {$wpdb->jswprediction_private_users} WHERE privateID = ".intval($LeagueId)." AND userID = ".intval($userId));
        }
    }

    public static function checkUserExist($userID){
        return (bool) get_userdata( $userID );
    }

    public static function getLeagueInviteKey($LeagueId){
        global $wpdb;
        $query = "SELECT id FROM {$wpdb->jswprediction_private_league} WHERE id='".intval($LeagueId)."'";

        return $wpdb->get_var($query);
    }
    /*<!--/jsonlyinproPHP-->*/
}
