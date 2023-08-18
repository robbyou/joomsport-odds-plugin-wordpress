<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**<!--WPJSSTDDEL--!>
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

class JoomSportClassMatchdayKnockout{
    private $_mdID = null;
    private $_seasonID = null;
    public function __construct($mdID) {
        $this->_mdID = $mdID;
        $metas = get_option("taxonomy_{$mdID}_metas");
        $this->_seasonID = $metas['season_id'];
    }
    public function getViewEdit(){
        $metas = get_option("taxonomy_{$this->_mdID}_metas");
        $knockoutView = isset($metas['knockout']) ? $metas['knockout'] : array();
        
        //var_dump($knockoutView);
        wp_enqueue_style('jscssbracket',plugin_dir_url( __FILE__ ).'../../../sportleague/assets/css/drawBracketBE.css');
        $matrix_stages = array(
            2 => 1,
            4 => 2,
            8 => 3,
            16 => 4,
            32 => 5,
            64 => 6,
            128 => 7,
            256 => 8,
        );
        
        //$kformat = 128;
        $kformat = $metas["knockout_format"];
        
        $stages = $matrix_stages[$kformat];
        //echo pow( 64, 1/2);
        $participiants = JoomSportHelperObjects::getParticipiants($this->_seasonID);
        ?>
        <div class="jsOverXdiv">
        <div class="drawBracketContainerBE">
            <table border="0" cellpadding="0" cellspacing="0" class="table" id="jsKnockTableBe">
            <?php

            for($intA=0; $intA < intval($kformat/2); $intA++){
                echo '<tr>';
                for($intB=0; $intB < $stages; $intB ++){
                    if($intA == 0 || ($intA % (pow(2,$intB)))==0){
                        
                        $kvalues = array(
                            "home" => 0,
                            "away" => 0,
                            "score1" => "",
                            "score2" => "",
                            "match_id" => ""
                        );
                        if(isset($knockoutView[$intB][$intA])){
                            $kvalues = array(
                                "home" => $knockoutView[$intB][$intA]["home"],
                                "away" => $knockoutView[$intB][$intA]["away"],
                                "score1" => $knockoutView[$intB][$intA]["score1"],
                                "score2" => $knockoutView[$intB][$intA]["score2"],
                                "match_id" => $knockoutView[$intB][$intA]["match_id"]
                            );
                        }
                        
                        echo '<td class="even" id="knocktd_'.esc_attr($intA.'_'.$intB).'" data-game="'.esc_attr($intA).'" data-level="'.esc_attr($intB).'" rowspan="'.esc_attr(pow(2,$intB)).'">';
                        $morefaclass = '';
                        if($intA % (pow(2 ,($intB+1))) == 0 && $intB != $stages-1){
                            echo '<div class="jsborderI"></div>';
                            
                        }elseif($intB == $stages-1){
                            echo '<div class="jsborderIFin"></div>';
                        }else{
                            $morefaclass = ' facirclebot';
                        }
                        echo '<i class="jsknockadd fa fa-plus-square'.esc_attr($morefaclass).'" aria-hidden="true"></i>';
                        if($intB < $stages - 1){
                            echo '<i class="jsproceednext fa fa-arrow-circle-right'.esc_attr($morefaclass).'" aria-hidden="true"></i>';
                        }else{
                            echo '<i class="jsproceednext jsknockfinal fa fa-arrow-circle-right'.esc_attr($morefaclass).'" aria-hidden="true"></i>';
                            echo '<input type="hidden" id="jsknock_winnerid" name="jsknock_winnerid" value="'.esc_attr(isset($metas['winner'])?$metas['winner']:0).'" />';
                        }
                        //echo '<i class="fa fa-cog jsmatchconf" aria-hidden="true"></i>';
                        echo '<div class="knockround"><div class="player knocktop ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName'.esc_attr($kvalues["home"]>0?' knockHover'.$kvalues["home"]:'').'">';
                        
                        if($intB == 0){
                            
                            echo  '<select class="js_selpartic js_selpartichome" id="js_selpartic_'.esc_attr($intA.'_'.$intB).'" name="set_home_team_'.esc_attr($intA.'_'.$intB).'">'
                                . '<option value="0">'.__('Select participant', 'joomsport-sports-league-results-management').'</option>';
                                $selected='';
                                if(-1 == $kvalues["home"]){
                                    $selected = ' selected';
                                }
                                echo '<option value="-1" '.$selected.'>'.__('BYE', 'joomsport-sports-league-results-management').'</option>';

                                if(count($participiants)){
                                    foreach ($participiants as $part) {
                                        $selected='';
                                        if($part->ID == $kvalues["home"]){
                                            $selected = ' selected';
                                        }
                                        echo '<option value="'.esc_attr($part->ID).'" '.$selected.'>'.esc_html($part->post_title).'</option>';
                                    }
                                }

                            echo   '</select>';
                        }else{
                            if($kvalues["home"] > 0){
                                echo '<div class="knwinner">'.esc_html(get_the_title($kvalues["home"])).'</div>';
                                if(isset($metas['winner']) && ($intB >= $stages - 1) && ($metas['winner'] == $kvalues["home"])){
                                    echo '<div class="jsknockwinnerDiv"></div>';
                                }
                                echo '<input type="hidden" class="js_selpartichome" name="set_home_team_'.esc_attr($intA.'_'.$intB).'" value="'.esc_attr($kvalues["home"]).'">';
                        
                            }elseif($kvalues["home"] == -1){
                                echo '<div class="knwinner">'.__('BYE', 'joomsport-sports-league-results-management').'</div>';
                                echo '<input type="hidden" class="js_selpartichome" name="set_home_team_'.esc_attr($intA.'_'.$intB).'" value="'.esc_attr($kvalues["home"]).'">';
                        
                            }
                        }
                              
                        echo  '</div>';
                        echo '<div class="knockscore">';
                        if($kvalues["match_id"] && count($kvalues["match_id"])){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                
                                echo '<div class="knockscoreItem" data-index="'.esc_attr($intZ).'">'
                                        . '<input type="text" class="mglScore mglScoreHome" value="'.esc_attr($kvalues["score1"][$intZ]).'" name="set_home_score_'.esc_attr($intA.'_'.$intB).'[]" size="3" maxlength="3" />'
                                        . '<input type="hidden" name="match_id_'.esc_attr($intA.'_'.$intB).'[]" value="'.esc_attr($kmid).'" />'
                                        . '<i class="fa fa-cog jsmatchconf2" data-index="'.esc_attr($intZ).'" aria-hidden="true"></i>'
                                        . '<i class="jsknockdel fa fa-minus-square" aria-hidden="true"></i>'
                                    . '</div>';
                                $intZ++;
                            }    
                        }else{
                            echo '<div class="knockscoreItem">'
                                        . '<input type="text" class="mglScore mglScoreHome" value="'.esc_attr($kvalues["score1"]).'" name="set_home_score_'.$intA.'_'.$intB.'[]" size="3" maxlength="3" />'
                                        . '<input type="hidden" name="match_id_'.esc_attr($intA.'_'.$intB).'[]" value="'.esc_attr($kvalues["match_id"]?$kvalues["match_id"]:'').'" />'
                                    . '<i class="fa fa-cog jsmatchconf2" data-index="0" aria-hidden="true"></i>'                                        
                                        . '<i class="jsknockdel fa fa-minus-square" aria-hidden="true"></i>'
                                    . '</div>';
                        }  
                        echo '</div>';
                        echo '</div></div>';
                        echo '<div class="player knockbot ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName'.($kvalues["away"]>0?' knockHover'.$kvalues["away"]:'').'">';
                        if($intB == 0){
                            
                            echo '<select class="js_selpartic js_selparticaway" name="set_away_team_'.esc_attr($intA.'_'.$intB).'">'
                                . '<option value="0">'.__('Select participant', 'joomsport-sports-league-results-management').'</option>';
                                $selected='';
                                if(-1 == $kvalues["away"]){
                                    $selected = ' selected';
                                }
                                echo '<option value="-1" '.$selected.'>'.__('BYE', 'joomsport-sports-league-results-management').'</option>';

                                if(count($participiants)){
                                    foreach ($participiants as $part) {
                                        $selected='';
                                        if($part->ID == $kvalues["away"]){
                                            $selected = ' selected';
                                        }
                                        echo '<option value="'.esc_attr($part->ID).'" '.$selected.'>'.esc_html($part->post_title).'</option>';
                                    }
                                }

                            echo   '</select>';
                        }else{
                            if($kvalues["away"] > 0){
                                
                                echo '<div class="knwinner">'.esc_html(get_the_title($kvalues["away"])).'</div>';
                                
                                if(isset($metas['winner']) && ($intB >= $stages - 1) && ($metas['winner'] == $kvalues["away"])){
                                    echo '<div class="jsknockwinnerDiv"></div>';
                                }
                                echo '<input type="hidden" class="js_selparticaway" name="set_away_team_'.esc_attr($intA.'_'.$intB).'" value="'.esc_attr($kvalues["away"]).'">';
                        
                            }elseif($kvalues["away"] == -1){
                                echo '<div class="knwinner">'.__('BYE', 'joomsport-sports-league-results-management').'</div>';
                                echo '<input type="hidden" class="js_selparticaway" name="set_away_team_'.esc_attr($intA.'_'.$intB).'" value="'.esc_attr($kvalues["away"]).'">';
                        
                            }
                        }
                                
                        echo '</div>'
                                . '<div class="knockscore">';
                        if( $kvalues["match_id"] && count($kvalues["match_id"])){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                
                                echo '<div class="knockscoreItem" data-index="'.esc_attr($intZ).'">'
                                        . '<input type="text" class="mglScore mglScoreAway" value="'.esc_attr($kvalues["score2"][$intZ]).'" name="set_away_score_'.esc_attr($intA.'_'.$intB).'[]" size="3" maxlength="3" />'
                                    . '</div>';
                                $intZ++;
                            }
                        }else{
                            echo '<div class="knockscoreItem">'
                                        . '<input type="text" class="mglScore mglScoreAway" value="'.esc_attr($kvalues["score2"]).'" name="set_away_score_'.esc_attr($intA.'_'.$intB).'[]" size="3" maxlength="3" />'
                                    . '</div>';
                        }    
                        echo         '</div>'
                            .'</div></div></div>';
                        echo '<input type="hidden" name="knocklevel[]" value="'.esc_attr($intA.'*'.$intB).'" />';
                        echo '</td>';
                    }
                }

                echo '</tr>';
            }
            ?>
            </table>
            <div id="jsknock-selectwinner" title="<?php echo __("Select winner", "joomsport-sports-league-results-management");?>">
                
            </div>
        </div>  
        </div> 
        <?php

    }
    public function getView(){
        $metas = get_option("taxonomy_{$this->_mdID}_metas");
        if(isset($metas['knockout'])){
            $knockoutView = $metas['knockout'];
        }else{
            return '';
        }
                            
        wp_enqueue_style('jscssbracket22',plugin_dir_url( __FILE__ ).'../../../sportleague/assets/css/drawBracketBE.css');
        
        $matrix_stages = array(
            2 => 1,
            4 => 2,
            8 => 3,
            16 => 4,
            32 => 5,
            64 => 6,
            128 => 7,
            256 => 8,
        );
        
        //$kformat = 128;
        $kformat = $metas["knockout_format"];
        
        $stages = $matrix_stages[$kformat];
        //echo pow( 64, 1/2);
        $participiants = JoomSportHelperObjects::getParticipiants($this->_seasonID);
        
        $html = '<div class="jsOverXdiv">';
        $html .= '<div class="drawBracketContainerFE">';
        
        $Mterm = get_term($this->_mdID);
        
        if(isset($Mterm->term_id) && $Mterm->term_id){
            $html .= '<h4>'.esc_html($Mterm->name).'</h4>';
        }
           
        if ($stages > 5) {
            $html .= '<table border="0" cellpadding="0" cellspacing="0" class="table jsWideKnockout">';
        } else {
            $html .= '<table border="0" cellpadding="0" cellspacing="0" class="table">';
        }

            for($intA=0; $intA < intval($kformat/2); $intA++){
                $html .= '<tr>';
                for($intB=0; $intB < $stages; $intB ++){
                    if($intA == 0 || ($intA % (pow(2,$intB)))==0){
                        
                        $kvalues = array(
                            "home" => 0,
                            "away" => 0,
                            "score1" => "",
                            "score2" => "",
                            "match_id" => ""
                        );
                        if(isset($knockoutView[$intB][$intA])){
                            $kvalues = array(
                                "home" => $knockoutView[$intB][$intA]["home"],
                                "away" => $knockoutView[$intB][$intA]["away"],
                                "score1" => $knockoutView[$intB][$intA]["score1"],
                                "score2" => $knockoutView[$intB][$intA]["score2"],
                                "match_id" => $knockoutView[$intB][$intA]["match_id"]
                            );
                        }
                        
                        $html .= '<td class="even" id="knocktd_'.esc_attr($intA.'_'.$intB).'" data-game="'.esc_attr($intA).'" data-level="'.esc_attr($intB).'" rowspan="'.esc_attr(pow(2,$intB)).'">';
                        $morefaclass = '';
                        if($intA % (pow(2 ,($intB+1))) == 0 && $intB != $stages-1){
                            $html .= '<div class="jsborderI"></div>';
                            
                        }elseif($intB == $stages-1){
                            $html .= '<div class="jsborderIFin"></div>';
                        }else{
                            $morefaclass = ' facirclebot';
                        }

                        if($kvalues["match_id"]){
                            $match = new classJsportMatch($kvalues["match_id"][0], false);
                            
                            if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                               // $html .=  classJsportLink::match('<i class="fa fa-cog jsmatchconf" aria-hidden="true"></i>', $kvalues["match_id"], false, '');
                            
                            }
                            $partic_home = $match->getParticipantHome();
                            $partic_away = $match->getParticipantAway();
                            //var_dump($partic_home);
                            
                        }
                        
                        $html .= '<div class="knockround"><div class="player knocktop ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName'.esc_attr($kvalues["home"]>0?' knockHover'.$kvalues["home"]:'').'">';
                        
                        
                            if($kvalues["home"] > 0){
                                $html .= '<div class="knwinner">';
                                $obj = new classJsportParticipant($this->_seasonID);
                                $part = $obj->getParticipiantObj($kvalues["home"]);
                                if(is_object($part)){
                                   //$html .= $partic_home->getEmblem();
                                   $html .= jsHelper::nameHTML($part->getName(true)); 
                                }else{   
                                   $html .= '<div class="js_div_particName">'.$kvalues["home"].get_the_title($kvalues["home"]).'</div>'; 
                                }
                                
                                $html .= '</div>';
                                if(isset($metas['winner']) && ($intB == $stages-1) && ($metas['winner'] == $kvalues["home"])){
                                    $html .= '<div class="jsknockwinnerDiv"></div>';
                                }
                            }elseif($kvalues["home"] == -1){
                                $html .= '<div class="knwinner"><div class="js_div_particName">'.__('BYE', 'joomsport-sports-league-results-management').'</div></div>';
                               
                            }
                        
                              
                        $html .=  '</div>';
                        if(  $kvalues["match_id"] && count($kvalues["match_id"])){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                $match = new classJsportMatch($kmid, false);
                                if ($kvalues["score1"][$intZ]!='') {
                                    $html .= '<div class="knockscore">';
                                } else {
                                    $html .= '<div class="knockscore knockfix">';
                                }
                                if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                                   $html .=  classJsportLink::match(($kvalues["score1"][$intZ]!=''?$kvalues["score1"][$intZ]:'<i class="fa fa-search" aria-hidden="true"></i>'), $kmid, false, '');

                                }else{
                                    $html .= ($kvalues["score1"][$intZ]!=''?$kvalues["score1"][$intZ]:'&nbsp;');
                                }
                                $html .= '</div>';
                                
                            
                                $intZ++;
                            }
                        }    
                        $html .=  '</div></div>';
                        $html .= '<div class="player knockbot ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName'.($kvalues["away"]>0?' knockHover'.$kvalues["away"]:'').'">';
                        
                            if($kvalues["away"] > 0){
                                
                                $html .= '<div class="knwinner">';
                                $obj = new classJsportParticipant($this->_seasonID);
                                $part = $obj->getParticipiantObj($kvalues["away"]);
                                if(is_object($part)){
                                   //$html .= $partic_home->getEmblem();
                                   $html .= jsHelper::nameHTML($part->getName(true)); 
                                }else{
                                   $html .= '<div class="js_div_particName">'.get_the_title($kvalues["away"]).'</div>'; 
                                }
                                
                                $html .= '</div>';
                                if(isset($metas['winner']) && ($intB == $stages-1) && ($metas['winner'] == $kvalues["away"])){
                                    $html .= '<div class="jsknockwinnerDiv"></div>';
                                }
                                
                            }elseif($kvalues["away"] == -1){
                                $html .= '<div class="knwinner"><div class="js_div_particName">'.__('BYE', 'joomsport-sports-league-results-management').'</div></div>';
                                
                            }
                        
                                
                        $html .= '</div>';
                        if($kvalues["match_id"] && count($kvalues["match_id"])){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                
                                $match = new classJsportMatch($kmid, false);
                                if ($kvalues["score1"][$intZ]!='') {
                                    $html .= '<div class="knockscore">';
                                } else {
                                    $html .= '<div class="knockscore knockfix">';
                                }
                                //var_dump($match);
                                if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                                   $html .=  classJsportLink::match(($kvalues["score2"][$intZ]!=''?$kvalues["score2"][$intZ]:'&nbsp;'), $kmid, false, '');

                                }else{
                                    $html .= ($kvalues["score2"][$intZ]!=''?$kvalues["score2"][$intZ]:'&nbsp;');
                                }
                                $html .= '</div>';
                                $intZ++;
                            }
                        }  
                        $html .= '</div></div></div>';
                        $html .= '</td>';
                    }
                }

                $html .= '</tr>';
            }
           
            $html .= '</table>';

        $html .= '</div>';
        $html .= '</div>';
        return $html;
        
    }
    public function save(){
        $term_metas = get_option("taxonomy_{$this->_mdID}_metas");
        if (!is_array($term_metas)) {
            $term_metas = Array();
        }
        // Save the meta value
        $matches = array();
        if(isset($_POST['formdata'])){
            parse_str($_POST['formdata']);            
        }else{
            extract($_POST);  
        }
        remove_filter( 'get_terms_args', 'jsmday_filter_get_terms_args' );
        $mday = get_term($this->_mdID, 'joomsport_matchday');
        
        //$kformat  = 128;
        $kformat = $term_metas["knockout_format"];
        
        $return_match = 0;
        
        //echo 'level='.count($knocklevel).'<br />';
        //echo 'match_id='.count($match_id).'<br />';
        //echo 'home='.count($set_home_team).'<br />';
        //echo 'away='.count($set_away_team).'<br />';
        //echo 'score1='.count($set_home_score).'<br />';
        //echo 'score2='.count($set_away_score).'<br />';
        
        $matches_in_mday = array();
        
        
        $post_name = '';
        
        $terms = wp_get_object_terms( $this->_seasonID, 'joomsport_tournament' );
        if( $terms ){
            $post_name .= $terms[0]->slug;
        }
        $post_name .= " ".get_the_title($this->_seasonID);
                
                
        
        
        if(isset($knocklevel) && count($knocklevel)){
            for($intA = 0 ; $intA < count($knocklevel) ; $intA ++){
                $match = array();
                $kn = explode('*', $knocklevel[$intA]);
                $match["intA"] = $kn[0];
                $match["intB"] = $kn[1];
                $str_matchid = 'match_id_'.$match["intA"].'_'.$match["intB"];
                $match["match_id"] = isset($$str_matchid)?($$str_matchid):'';
                $str_home = 'set_home_team_'.$match["intA"].'_'.$match["intB"];
                $str_away = 'set_away_team_'.$match["intA"].'_'.$match["intB"];
                $match["home"] = sanitize_text_field(isset($$str_home)?$$str_home:null);
                $match["away"] = sanitize_text_field(isset($$str_away)?$$str_away:null);
                $str_home_score = 'set_home_score_'.$match["intA"].'_'.$match["intB"];
                $str_away_score = 'set_away_score_'.$match["intA"].'_'.$match["intB"];
                $match["score1"] = (isset($$str_home_score)?$$str_home_score:'');
                $match["score2"] = (isset($$str_away_score)?$$str_away_score:'');

                if($match["home"] != '-1' && $match["away"] != '-1'){
                    if($match["home"]){
                        $home_team = get_the_title(intval($match["home"]));
                    }
                    if($match["away"]){
                        $away_team = get_the_title(intval($match["away"]));
                    }
                    $updTitle = true;
                    if(!$match["home"] || !$match["away"]){
                        $kstage = $kformat/(pow(2, ($match["intB"]+1) ));
                        switch($kstage){
                            case 1:
                                $kstage_str = ' ' . __('Final', 'joomsport-sports-league-results-management');
                                break;
                            default:
                                $kstage_str = ' 1/'.$kstage;
                        }
                        $title =  $kstage_str;
                        $updTitle = false;
                    }else{
                        $title = $home_team.(empty(JoomsportSettings::get('jsconf_home_away_separator_vs')) ? ' vs ' : ' '.JoomsportSettings::get('jsconf_home_away_separator_vs').' ').$away_team;
                    }
                    
                    if($match["match_id"] && count($match["match_id"])){
                        
                        for($intM=0; $intM<count($match["match_id"]); $intM++){
                            $scoreChanged = false;
                            if($match["match_id"][$intM]){
                                $postM = get_post(intval($match['match_id'][$intM]));
                                /*$pst = get_post($match["match_id"][$intM]);   
                                
                                if(!$pst){
                                    $arr = array(
                                            'post_type' => 'joomsport_match',
                                            'post_title' => wp_strip_all_tags( $title ),
                                            'post_content' => '',
                                            'post_status' => 'publish',
                                            'post_author' => get_current_user_id()
                                    );
                                    echo $match["match_id"][$intM] = wp_insert_post( $arr );
                                    echo '<br/>';
                                }else{*/
                                    if($updTitle && $postM->post_title != wp_strip_all_tags( $title )){
                                        $upd_post = array(
                                            'ID'           => $match["match_id"][$intM],
                                            'post_title'   => wp_strip_all_tags( $title ),
                                        );
                                        wp_update_post($upd_post);
                                    }
                               //}    
                                
                                $allmeta = get_post_meta( $match["match_id"][$intM],'',true);    
                        
                                $old_score1 = get_post_meta($match["match_id"][$intM], '_joomsport_home_score', true);
                                $old_score2 = get_post_meta($match["match_id"][$intM], '_joomsport_away_score', true);

                                
                                
                                $home_teamDB = get_post_meta( $match["match_id"][$intM], '_joomsport_home_team', true );
                                if($home_teamDB == intval($match["away"])){
                                    if(!isset($allmeta["_joomsport_home_team"][0]) || $allmeta["_joomsport_home_team"][0] != intval($match["away"])){
                    
                                        update_post_meta($match["match_id"][$intM], '_joomsport_home_team', intval($match["away"]));
                                    }
                                    if(!isset($allmeta["_joomsport_away_team"][0]) || $allmeta["_joomsport_away_team"][0] != intval($match["home"])){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_away_team', intval($match["home"]));
                                    
                                        
                                    }
                                    if(!isset($allmeta["_joomsport_home_score"][0]) || $allmeta["_joomsport_home_score"][0] != sanitize_text_field($match["score2"][$intM])){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_home_score', sanitize_text_field($match["score2"][$intM]));
                                        $scoreChanged = true;
                                        
                                    }
                                    if(!isset($allmeta["_joomsport_away_score"][0]) || $allmeta["_joomsport_away_score"][0] != sanitize_text_field($match["score1"][$intM])){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_away_score', sanitize_text_field($match["score1"][$intM]));
                                        $scoreChanged = true;
                                        
                                    }
                                    
                                }else{
                                    if(!isset($allmeta["_joomsport_home_team"][0]) || $allmeta["_joomsport_home_team"][0] != intval($match["home"])){
                                         update_post_meta($match["match_id"][$intM], '_joomsport_home_team', intval($match["home"]));
                                    
                                    }
                                    if(!isset($allmeta["_joomsport_away_team"][0]) || $allmeta["_joomsport_away_team"][0] != intval($match["away"])){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_away_team', intval($match["away"]));
                                    
                                    }
                                    if(!isset($allmeta["_joomsport_home_score"][0]) || $allmeta["_joomsport_home_score"][0] != sanitize_text_field($match["score1"][$intM])){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_home_score', sanitize_text_field($match["score1"][$intM]));
                                        $scoreChanged = true;
                                    }
                                    if(!isset($allmeta["_joomsport_away_score"][0]) || $allmeta["_joomsport_away_score"][0] != sanitize_text_field($match["score2"][$intM])){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_away_score', sanitize_text_field($match["score2"][$intM]));
                                        $scoreChanged = true;
                                        
                                    }
                                    
                                }
                                
                                $m_played = (int) get_post_meta( $match["match_id"], '_joomsport_match_played', true );
                                if(in_array($m_played, array(0,1))){
                                    $m_played = '0';
                                    if($match["score1"][$intM]!='' && $match["score2"][$intM] != ''){
                                        $m_played = '1';
                                        //$match_date = get_post_meta( $match["match_id"][$intM], '_joomsport_match_date', true );
                                        //$match_time = get_post_meta( $match["match_id"][$intM], '_joomsport_match_time', true );
                                        //update_post_meta($match["match_id"][$intM], '_joomsport_match_date', sanitize_text_field($match_date));
                                        //update_post_meta($match["match_id"][$intM], '_joomsport_match_time', sanitize_text_field($match_time));

                                    }
                                    if((!isset($old_score1[$intM]) && !isset($old_score2[$intM])) || ($old_score1[$intM] == '' && $old_score2[$intM] == '')){
                                        update_post_meta($match["match_id"][$intM], '_joomsport_match_played', $m_played);
                                
                                    }
                                }
                            }else{
                                
                                $metadata = get_post_meta(intval($match["home"]),'_joomsport_team_personal',true);
                                $home_team = isset($metadata['middle_name'])?(sanitize_text_field($metadata['middle_name'])):"";

                                $metadata = get_post_meta(intval($match["away"]),'_joomsport_team_personal',true);
                                $away_team = isset($metadata['middle_name'])?(sanitize_text_field($metadata['middle_name'])):"";
                                if(!$home_team){
                                    $home_team = get_the_title(intval($match["home"]));
                                }
                                if(!$away_team){
                                    $away_team = get_the_title(intval($match["away"]));
                                }



                                $title = $home_team.(empty(JoomsportSettings::get('jsconf_home_away_separator_vs')) ? ' vs ' : ' '.JoomsportSettings::get('jsconf_home_away_separator_vs').' ').$away_team;
                                $arr = array(
                                        'post_type' => 'joomsport_match',
                                        'post_title' => wp_strip_all_tags( $title ),
                                        'post_content' => '',
                                        'post_status' => 'publish',
                                        'post_author' => get_current_user_id(),
                                        'post_name' => wp_strip_all_tags($post_name." ".$title)
                                );
                                
                                
                                


                                $match["match_id"][$intM] = wp_insert_post( $arr );
                                if($match["match_id"][$intM]){
                                    update_post_meta($match["match_id"][$intM], '_joomsport_home_team', intval($match["home"]));
                                    update_post_meta($match["match_id"][$intM], '_joomsport_away_team', intval($match["away"]));
                                    update_post_meta($match["match_id"][$intM], '_joomsport_home_score', sanitize_text_field($match["score1"][$intM]));
                                    update_post_meta($match["match_id"][$intM], '_joomsport_away_score', sanitize_text_field($match["score2"][$intM]));
                                    update_post_meta($match["match_id"][$intM], '_joomsport_match_date', '');
                                    update_post_meta($match["match_id"][$intM], '_joomsport_match_time', '');
                                    
                                    $m_played = '0';
                                    if($match["score1"][$intM]!='' && $match["score2"][$intM] != ''){
                                        $m_played = '1';
                                        $match_date = get_post_meta( $match["match_id"][$intM], '_joomsport_match_date', true );
                                        $match_time = get_post_meta( $match["match_id"][$intM], '_joomsport_match_time', true );
                                        update_post_meta($match["match_id"][$intM], '_joomsport_match_date', sanitize_text_field($match_date));
                                        update_post_meta($match["match_id"][$intM], '_joomsport_match_time', sanitize_text_field($match_time));
                                    }

                                    update_post_meta($match["match_id"][$intM], '_joomsport_match_played', $m_played);
                                }

                                //wp_set_post_terms( $match["match_id"][$intM], array((int)$this->_mdID), 'joomsport_matchday');
                 
                            }
                            wp_set_post_terms( $match["match_id"][$intM], array((int)$this->_mdID), 'joomsport_matchday');
                            if(!isset($allmeta["_joomsport_seasonid"][0]) || $allmeta["_joomsport_seasonid"][0] != $this->_seasonID){
                                    
                                update_post_meta($match["match_id"][$intM], '_joomsport_seasonid', $this->_seasonID);
                            }
                            $matches_in_mday[] = $match["match_id"][$intM];
                            
                            if($scoreChanged){
                                do_action("joomsport_score_changed", $match["match_id"][$intM]);
                            }
                            jsHelperMatchesDB::updateMatchDB($match["match_id"][$intM]);
                        }

                    }else{
                        $metadata = get_post_meta(intval($match["home"]),'_joomsport_team_personal',true);
                        $home_team = isset($metadata['middle_name'])?(sanitize_text_field($metadata['middle_name'])):"";

                        $metadata = get_post_meta(intval($match["away"]),'_joomsport_team_personal',true);
                        $away_team = isset($metadata['middle_name'])?(sanitize_text_field($metadata['middle_name'])):"";
                        if(!$home_team){
                            $home_team = get_the_title(intval($match["home"]));
                        }
                        if(!$away_team){
                            $away_team = get_the_title(intval($match["away"]));
                        }



                        $title = $home_team.(empty(JoomsportSettings::get('jsconf_home_away_separator_vs')) ? ' vs ' : ' '.JoomsportSettings::get('jsconf_home_away_separator_vs').' ').$away_team;
                        $arr = array(
                                'post_type' => 'joomsport_match',
                                'post_title' => wp_strip_all_tags( $title ),
                                'post_content' => '',
                                'post_status' => 'publish',
                                'post_author' => get_current_user_id(),
                                'post_name' => wp_strip_all_tags($post_name." ".$title)
                        );
                        
                        
                        $match["match_id"] = wp_insert_post( $arr );
                        
                        if($match["match_id"]){
                            update_post_meta($match["match_id"], '_joomsport_home_team', intval($match["home"]));
                            update_post_meta($match["match_id"], '_joomsport_away_team', intval($match["away"]));
                            update_post_meta($match["match_id"], '_joomsport_home_score', sanitize_text_field($match["score1"]));
                            update_post_meta($match["match_id"], '_joomsport_away_score', sanitize_text_field($match["score2"]));
                            //update_post_meta($match["match_id"], '_joomsport_match_date', '');
                            //update_post_meta($match["match_id"], '_joomsport_match_time', '');
                            $m_played = '0';
                            if($match["score1"]!='' && $match["score2"] != ''){
                                $m_played = '1';
                                $match_date = get_post_meta( $match["match_id"], '_joomsport_match_date', true );
                                $match_time = get_post_meta( $match["match_id"], '_joomsport_match_time', true );
                                update_post_meta($match["match_id"], '_joomsport_match_date', sanitize_text_field($match_date));
                                update_post_meta($match["match_id"], '_joomsport_match_time', sanitize_text_field($match_time));
                            }
                            
                            update_post_meta($match["match_id"], '_joomsport_match_played', $m_played);
                        }

                        wp_set_post_terms( $match["match_id"], array((int)$this->_mdID), 'joomsport_matchday');
                        update_post_meta($match["match_id"], '_joomsport_seasonid', $this->_seasonID);
                        $match["match_id"] = array($match["match_id"]);
                        $matches_in_mday[] = $match["match_id"];

                        jsHelperMatchesDB::updateMatchDB($match["match_id"]);
                    }
                    
                }
                
                $matches[$kn[1]][$kn[0]] = $match;
                
                if(isset($_POST['xLevel']) && isset($_POST['yLevel'])){
                    if($match["intA"] == $_POST['yLevel'] && $match["intB"] == $_POST['xLevel']){
                        if(isset($_POST['dIndex'])){
                            $return_match = $match["match_id"][intval($_POST['dIndex'])];
                        }
                    }
                }
                
            }
        }
        
        $matchesMday = new WP_Query(array(
            'post_type' => 'joomsport_match',
            'posts_per_page'   => -1,
            'orderby' => 'id',
            'order'=>'ASC',
            'tax_query' => array(
                array(
                'taxonomy' => 'joomsport_matchday',
                'field' => 'term_id',
                'terms' => $this->_mdID)
            )  
        ));
        
        $recalcTeams = array();
        foreach ($matchesMday->posts as $post) {
            if(!in_array($post->ID, $matches_in_mday)){
                $recalcTeams[] = get_post_meta( $match_id, '_joomsport_home_team', true );
                $recalcTeams[] = get_post_meta( $match_id, '_joomsport_away_team', true );
                wp_delete_post($post->ID);
            }
        }
        if(count($recalcTeams)){
            do_action('joomsport_update_playerlist', $this->_seasonID, $recalcTeams);
        }
        
        $term_metas['knockout'] = ($matches);
        $term_metas["winner"] = 0;
        
        if(isset($_POST["jsknock_winnerid"]) && $_POST["jsknock_winnerid"]){
            $term_metas["winner"] = intval($_POST["jsknock_winnerid"]);
        }
        
        //var_dump($matches);
        update_option( "taxonomy_{$this->_mdID}_metas", $term_metas );
        
        if($return_match){
            echo $return_match;
        }
        
    }

}