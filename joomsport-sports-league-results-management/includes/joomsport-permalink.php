<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
/*<!--jsonlyinproPHP-->*/
class JoomsportPermalink {

    public static function init(){
        add_action( 'admin_init', array( 'JoomsportPermalink', 'jspermalink_init' ) );
	    add_action( 'admin_init', array( 'JoomsportPermalink', 'jspermalink_save' ) );
    }
    public static function jspermalink_init(){
        $posts = array(
            array("post" => "joomsport_season","title" => __('Season','joomsport-sports-league-results-management')),
            array("post" => "joomsport_team","title" => __('Team','joomsport-sports-league-results-management')),
            array("post" => "joomsport_player","title" => __('Player','joomsport-sports-league-results-management')),
            array("post" => "joomsport_venue","title" => __('Venue','joomsport-sports-league-results-management')),
            array("post" => "joomsport_match","title" => __('Match','joomsport-sports-league-results-management')),
            array("post" => "joomsport_person","title" => __('Person','joomsport-sports-league-results-management')),
            array("post" => "joomsport_matchday","title" => __('Matchday','joomsport-sports-league-results-management')),
            array("post" => "joomsport_tournament","title" => __('League','joomsport-sports-league-results-management')),
        );

        add_settings_section( 'joomsportpermalink', 'JoomSport', function(){ }, 'permalink' );

        // Add our settings
        foreach ( $posts as $post ){
            add_settings_field(
                $post["post"],
                $post["title"],
                array( 'JoomsportPermalink', 'fieldsinput' )
                ,
                'permalink',
                'joomsportpermalink',
                $post
            );
        }
    }
    public static function fieldsinput($post){
        $value = get_option( 'joomsportslug_' . $post["post"], null );
        echo '<input type="text" class="regular-text code" name="joomsportslug['.esc_attr($post["post"]).']" value="'.esc_attr($value).'" placeholder="'.esc_attr($post["post"]).'" />';
    }
    public static function jspermalink_save(){
        if ( is_admin() ){
            if(isset($_POST['joomsportslug']) && count($_POST['joomsportslug'])){
                foreach ($_POST['joomsportslug'] as $key => $value) {
                    $value = sanitize_text_field($value);
                    if($value){
                        update_option('joomsportslug_' .sanitize_text_field($key), $value);
                    }
                }
            }
        }
    }
}
JoomsportPermalink::init();



function myplugin_rewrite_tag() {
	add_rewrite_tag( '%jstournament%', '([^&]+)' );
}
add_action('init', 'myplugin_rewrite_tag', 10, 0);

function myplugin_rewrite_rule() {
	add_rewrite_rule( '^season/jstournament/([^/]*)/?', 'index.php?post_type=joomsport_season&tournament=$matches[1]','top' );
}
add_action('init', 'myplugin_rewrite_rule', 10, 0);
function wpa_course_post_link( $post_link, $id = 0 ){
    $post = get_post($id);
    if ( is_object( $post ) ){
        if  ($post->post_type == 'joomsport_season') {
            $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
            if( $terms ){
                return str_replace( '%jstournament%' , $terms[0]->slug , $post_link );
            }
        }
    }
    return $post_link;
}
add_filter( 'post_type_link', 'wpa_course_post_link', 1, 3 );

/*<!--/jsonlyinproPHP-->*/


add_filter('the_title', 'jоomsport_filter_seasontitle', 999, 2);
function jоomsport_filter_seasontitle($title, $id) {
    global $wpdb, $post_type, $post, $pagenow;
    /*if( is_admin() || !in_the_loop() ){
        return $title;
    }*/
    if($pagenow == 'nav-menus.php'){
        $tpost  = get_post($id);
        if($tpost->post_type == 'joomsport_season'){
            $terms = wp_get_object_terms( $id, 'joomsport_tournament' );
            $post_name = '';
            if( $terms ){

                $post_name .= $terms[0]->name;
            }
            $post_name .= " ".$title;
            //remove_filter( 'the_title', 'jоomsport_filter_seasontitle' );
            return $post_name;
        }
    }
    if(!$post){
        return $title;
    }

    if ( !in_the_loop() ) return $title;

    if($title != $post->post_title){
        return $title;
    }
    if($id != $post->ID){
        return $title;
    }
    if($post_type == 'joomsport_season'){
        $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
        $post_name = '';
        if( $terms ){

            $post_name .= $terms[0]->name;
        }
        $post_name .= " ".$title;
        //remove_filter( 'the_title', 'jоomsport_filter_seasontitle' );
        return $post_name;
    }/*elseif($post_type == 'joomsport_match'){
        $m_date = get_post_meta($post->ID,'_joomsport_match_date',true);
        if($m_date){
            $m_date_str = explode("-", $m_date);
            if(count($m_date_str)){
                $m_date = $m_date_str[2].".".$m_date_str[1].".".$m_date_str[0];
            }
        }
        $post_name = $m_date." ".$title;
        return $post_name;
    }*/
    return $title;
}
add_filter( 'document_title_parts', function( $title_parts_array ) {
    global $wpdb, $post_type, $post;

    if(!$post){
        return $title_parts_array;
    }
    if($post_type == 'joomsport_season'){
        $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
        $post_name = '';
        if( $terms ){

            $post_name .= $terms[0]->name;
        }
        //$post_name .= " ".$title;
        $title_parts_array['title'] =  $post_name ." ".$title_parts_array['title'];
    }

    return $title_parts_array;
} );
add_filter( 'pre_get_document_title', function( $title )
  {
    global $wpdb, $post_type, $post;
    if(!$title){
        return '';
    }
    if(!$post){
        return $title;
    }
    if($post_type == 'joomsport_season'){
        $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
        $post_name = '';
        if( $terms ){

            $post_name .= $terms[0]->name;
        }
        $title =  $post_name ." ".$title;
    }
    /*elseif($post_type == 'joomsport_match'){
        $m_date = get_post_meta($post->ID,'_joomsport_match_date',true);
        if($m_date){
            $m_date_str = explode("-", $m_date);
            if(count($m_date_str)){
                $m_date = $m_date_str[2].".".$m_date_str[1].".".$m_date_str[0];
            }
        }
        $post_name = $m_date." ".$title;
        return $post_name;
    }*/

    return $title;
  }, 999, 1 );
/*add_filter( 'aioseop_title', 'allinone_jsport_wordpress_seo_title' );

function allinone_jsport_wordpress_seo_title( $title ){
    global $wpdb, $post_type, $post;

    if($post_type == 'joomsport_match'){
        $m_date = get_post_meta($post->ID,'_joomsport_match_date',true);
        if($m_date){
            $m_date_str = explode("-", $m_date);
            if(count($m_date_str)){
                $m_date = $m_date_str[2].".".$m_date_str[1].".".$m_date_str[0];

                $title = str_replace("%match_date%", $m_date, $title);
            }
        }
        $title = str_replace("%match_date%", "", $title);

    }
    return $title;
}*/

/*<!--jsonlyinproPHP-->*/
add_filter('the_title', 'jоomsport_filter_pro_matchtitle', 999, 2);
function jоomsport_filter_pro_matchtitle($title, $id) {
    global $wpdb, $post_type, $post, $pagenow;

    if(!$post){
        return $title;
    }

    //if ( !in_the_loop() ) return $title;

    if($title != $post->post_title){
        return $title;
    }
    if($id != $post->ID){
        return $title;
    }
    if($post_type == 'joomsport_match'){
        $settingstitle = JoomsportSettings::get('matchTitle','');
        if(!$settingstitle){
            return $title;
        }

        $m_played = get_post_meta( $post->ID, '_joomsport_match_played', true );
        $m_date = get_post_meta($post->ID, '_joomsport_match_date', true);
        $home = get_post_meta($post->ID, '_joomsport_home_team', true);
        $away = get_post_meta($post->ID, '_joomsport_away_team', true);
        $seasonID = get_post_meta($post->ID,'_joomsport_seasonid',true);
        $competition = '';
        $term_list = get_the_terms($seasonID, 'joomsport_tournament');
        if(count($term_list)) {
            $competition = $term_list[0]->name;
        }

        $md = '';
        $term_list = get_the_terms($post->ID, 'joomsport_matchday');
        if(count($term_list)) {
            $md = $term_list[0]->name;
        }

        remove_filter( 'the_title', 'jоomsport_filter_pro_matchtitle' );

        $new_title = get_post( $home )->post_title." - ".get_post( $away )->post_title;
        if($m_played == 1){
            $score1 = get_post_meta($post->ID, '_joomsport_home_score', true);
            $score2 = get_post_meta($post->ID, '_joomsport_away_score', true);

            $new_title.= ": ".$score1."-".$score2;



        }
        $newDateString = '';
        if($m_date){
            $myDateTime = DateTime::createFromFormat('Y-m-d', $m_date);
            $newDateString = $myDateTime->format('j M Y');
            $new_title.= " ({$newDateString})";
        }

        $settingstitle = str_replace('{league}',$competition,$settingstitle);
        $settingstitle = str_replace('{season}',get_post( $seasonID )->post_title,$settingstitle);
        $settingstitle = str_replace('{matchday}',$md,$settingstitle);
        $settingstitle = str_replace('{date}',$newDateString,$settingstitle);
        $settingstitle = str_replace('{home team}',get_post( $home )->post_title,$settingstitle);
        $settingstitle = str_replace('{away team}',get_post( $away )->post_title,$settingstitle);
        if($m_played == 1) {
            $settingstitle = str_replace('{match score}', $score1 . " : " . $score2, $settingstitle);
        }else{
            $settingstitle = str_replace('{match score}', "", $settingstitle);

        }
        return $settingstitle;

    }
    return $title;

}
/*<!--/jsonlyinproPHP-->*/