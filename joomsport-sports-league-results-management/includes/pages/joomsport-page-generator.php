<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomsportPageGenerator{
    public static function action(){
        global $wpdb;
        /*<!--jsonlyinproPHP-->*/
        if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
            $generatem = isset($_POST['autogeneration']) && $_POST['autogeneration'] == 'true';
            if ($generatem) {
                $msg = self::generate();
                $msgType = 'notice notice-error';
                
                if(!$msg){
                    $msg = sprintf(__('Successfully generated. Check schedule on %s Matchday list%s layout','joomsport-sports-league-results-management'),'<a href="edit-tags.php?taxonomy=joomsport_matchday&post_type=joomsport_match">','</a>');

                    $msgType = 'notice notice-success';
                }
                if( is_wp_error( $msg ) ) {
                    $msg =  $msg->get_error_message();
                    
                }
                ?>
                <div class="<?php echo esc_attr($msgType);?>">
                    <p><?php echo  esc_html($msg); ?></p>
                </div>
                <?php
                //$app->redirect($link, $msg, $msgType);
            }
        }
        $participants = array();
        $season_id = isset($_POST['season_id'])?  ($_POST['season_id']):0;
        $md_type = isset($_POST['md_type'])?  intval($_POST['md_type']):0;
        $seasons = JoomSportHelperObjects::getSeasonsGroups();
        $is_mday_type = array();
        $is_mday_type[] = JoomSportHelperSelectBox::addOption("0", __('Round-robin','joomsport-sports-league-results-management'));
        $is_mday_type[] = JoomSportHelperSelectBox::addOption("1", __('Knockout','joomsport-sports-league-results-management'));
        if($season_id){
            $seasongroup = explode('|', $season_id);
            if(isset($seasongroup[1])){
                $participants = JoomSportHelperObjects::getParticipiants($seasongroup[0], $seasongroup[1]);
            }
        }
        
        $is_algorithm = array();
        $is_algorithm[] = JoomSportHelperSelectBox::addOption("0", __('Standard algorithm','joomsport-sports-league-results-management'));
        $is_algorithm[] = JoomSportHelperSelectBox::addOption("1", __('Berger algorithm','joomsport-sports-league-results-management'));
        
        
        
        ?>
        <script>
        jQuery(document).ready(function() {
            jQuery('.numbersOnlyAG').keyup(function () { 
                this.value = this.value.replace(/[^0-9\.]/g,'');
            });
            jQuery('#generatem').on('click', function(){
                document.MatchGeneratorForm.autogeneration.value = 'true';
                document.MatchGeneratorForm.submit();
            });

            jQuery('#generateknock').on('click', function(){
                var formatnum = document.MatchGeneratorForm.format_post.value;

                if(formatnum == '0'){
                    alert('<?php echo __('Please specify number of participants', 'joomsport-sports-league-results-management');?>');
                    
                    return false;
                }
                var teams_knock = jQuery('#teams_knock_sel').val();
                if(!teams_knock){
                    alert('<?php echo __('Please select participants', 'joomsport-sports-league-results-management');?>');
                    
                    return false;
                }

                

                document.MatchGeneratorForm.autogeneration.value = 'true';
                document.MatchGeneratorForm.submit();
            });


        });  


        (function($){
            //Shuffle all rows, while keeping the first column
            //Requires: Shuffle
         $.fn.shuffleRows = function(){
             return this.each(function(){
                var main = $(/table/i.test(this.tagName) ? this.tBodies[0] : this);
                var firstElem = [], counter=0;
                main.children().each(function(){
                     firstElem.push(this.firstChild);
                });
                main.shuffle();
                main.children().each(function(){
                   this.insertBefore(firstElem[counter++], this.firstChild);
                });
             });
           }
          /* Shuffle is required */
          $.fn.shuffle = function() {
            return this.each(function(){
              var items = $(this).children();
              return (items.length)
                ? $(this).html($.shuffle(items))
                : this;
            });
          }

          $.shuffle = function(arr) {
            for(
              var j, x, i = arr.length; i;
              j = parseInt(Math.random() * i),
              x = arr[--i], arr[i] = arr[j], arr[j] = x
            );
            return arr;
          }
        })(jQuery);

        function shuffleNumberstm(){

            jQuery.each(jQuery("input[name^='team_number_rand']"),function(i,el){
                jQuery(this).val(i+1);
            })
        }

        function checkQnty(obj){
            var qty = parseInt(obj.value);
            var qty_list = parseInt(jQuery('#teams_knock option').size()); 
            if(qty_list > qty){
                jQuery("#qty_notify").text("<?php echo __('You added more participants than chosen knockout format allows. Not all the teams/player will participate Matches', 'joomsport-sports-league-results-management');?>");
            }else{
                jQuery("#qty_notify").text('');
            }

        }


        function ReAnalize_tbl_Rows( tbl_id ) {
            start_index = 0;
            var tbl_elem = getObj(tbl_id);
            if (tbl_elem.rows[start_index]) {
                for (var i=start_index; i<tbl_elem.rows.length; i++) {

                    if (i > 0) { 
                            tbl_elem.rows[i].cells[0].style.visibility = 'visible';
                    } else { tbl_elem.rows[i].cells[0].style.visibility = 'hidden'; }
                    if (i < (tbl_elem.rows.length - 1)) {
                            tbl_elem.rows[i].cells[1].style.visibility = 'visible';
                    } else { tbl_elem.rows[i].cells[1].style.visibility = 'hidden'; }

                }
            }
        }

        jQuery(document).ready(function(){
            jQuery(".up,.down").click(function(){
                var row = jQuery(this).parents("tr:first");
                if (jQuery(this).is(".up")) {
                    row.insertBefore(row.prev());
                } else if (jQuery(this).is(".down")) {
                    row.insertAfter(row.next());
                }
                ReAnalize_tbl_Rows('teamsToShuf');
            });
        });
        jQuery( document ).ready(function() {

            jQuery("#teamsToShuf").sortable(

            );

        });
        </script>
        <div class="jsSettingsPage">
            <div class="jsBepanel">
                    <div class="jsBEheader">
                        <?php echo __('Match generator', 'joomsport-sports-league-results-management');?>
                    </div>
                    <div class="jsBEsettings">
                    <form method="post" id="MatchGeneratorForm" name="MatchGeneratorForm">
                    <table>
                        <tr>
                            <td style="padding: 5px 0px;" width="130">
                                <?php echo __("Season",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <?php 
                                    if(!empty($seasons)){
                                        echo wp_kses(JoomSportHelperSelectBox::Optgroup('season_id', $seasons, $season_id,' onchange="this.form.submit();"'), JoomsportSettings::getKsesSelect());
                                    }
                                    //echo $lists['seasons'];?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0px;">
                                <?php echo __("Matchday type",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <?php
                                
                                    echo wp_kses(JoomSportHelperSelectBox::Simple('md_type', $is_mday_type,$md_type,' onchange="this.form.submit();"',false), JoomsportSettings::getKsesSelect());
        
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0px;">
                                <?php echo __("Matchday name",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <input style="margin-bottom: 0px;"  type="text" name="mday_name" value="Matchday" />
                            </td>
                        </tr>
                        <?php
                        if (!$md_type && $season_id) {
                            ?>
                        <tr>
                            <td style="padding: 5px 0px;">
                                <?php echo __("Scheduling algorithm",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <?php
                                
                                    echo wp_kses(JoomSportHelperSelectBox::Simple('sc_algorithm', $is_algorithm,0,' ',false), JoomsportSettings::getKsesSelect());
        
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0px;">
                                <?php echo __("Rounds",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <input type="text" name="rounds" value="2" style="width:30px;" class="numbersOnlyAG" />
                            </td>

                        </tr>
                        <tr>
                            <td style="padding: 5px 0px;">
                                <input type="button"  class="button" value="<?php echo esc_attr(__("Randomize",'joomsport-sports-league-results-management'));?>" onclick="jQuery('#teamsToShuf').shuffleRows();shuffleNumberstm();" />

                            </td>
                            <td style="padding: 5px 10px;">
                                <input type="button"class="button"  name="generatem" id="generatem" value="<?php echo esc_attr(__("Generate Matches",'joomsport-sports-league-results-management'));?>" />
                            </td>

                        </tr>
                        <?php 
                        } ?>
                    </table>
                    <?php
                    if ($season_id) {
                        ?>
                    <div style="padding:10px 0px; width:50%;">
                        <?php
                        if (!$md_type) {
                            ?>

                        <table>
                            <tbody id="teamsToShuf">
                            <?php for ($intA = 0; $intA < count($participants); ++$intA) {
                    ?>
                            <tr class="ui-state-default">
                                
                                <td style="padding:5px 10px;cursor:move;">
                                    <?php echo esc_html(get_the_title($participants[$intA]->ID));
                    ?>
                                    <input type="hidden" name="team_number_id[]" value="<?php echo esc_attr($participants[$intA]->ID);
                    ?>" />
                                </td>
                                
                            </tr>

                            <?php 
                }
                            ?>
                            </tbody>
                        </table>

                        <?php

                        } else {
                            $is_format_post = array();
                            $is_format_post[] = JoomSportHelperSelectBox::addOption("2", 2);
                            $is_format_post[] = JoomSportHelperSelectBox::addOption("4", 4);
                            $is_format_post[] = JoomSportHelperSelectBox::addOption("8", 8);
                            $is_format_post[] = JoomSportHelperSelectBox::addOption("16", 16);
                            $is_format_post[] = JoomSportHelperSelectBox::addOption("32", 32);
                            $is_format_post[] = JoomSportHelperSelectBox::addOption("64", 64);
                        ?>

                        <table>
                            <tr>
                                <td style="padding: 5px 0px;" width="130">
                                    <?php echo __("Select participants",'joomsport-sports-league-results-management');?>
                                    
                                </td>
                                <td style="padding: 5px 10px;" width="250">
                                    <?php
                                    if(count($participants)){
                                        echo '<select name="teams_knock[]" id="teams_knock_sel" class="jswf-chosen-select" data-placeholder="'.esc_attr(__('Add item','joomsport-sports-league-results-management')).'" multiple>';
                                        foreach ($participants as $tm) {
                                            
                                            echo '<option value="'.esc_attr($tm->ID).'" >'.esc_html($tm->post_title).'</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                                
                            </tr>
                        </table>
                        <div>
                            <div id="qty_notify" style="color:red;"></div>
                            <?php 
                                echo wp_kses(JoomSportHelperSelectBox::Simple('format_post', $is_format_post,0,'class="inputbox" size="1" id="format_post" onchange="checkQnty(this);"',false), JoomsportSettings::getKsesSelect());
        
                            ?>

                            <input type="button" class="button" name="generateknock" id="generateknock" value="<?php echo esc_attr(__("Generate Knockout",'joomsport-sports-league-results-management'));?>" />

                        </div>
                        <?php 
                        }
                        ?>
                    </div>    

                    <?php

                    }
                    ?>
                    <div>
                        
                        <div>
                            <input type="hidden" name="autogeneration" value="false" />
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
                        </div>
                        
                    </div>
                    </form>
                </div>
            </div>    
        </div>    
        <?php
        /*<!--/jsonlyinproPHP-->*/
        /*<!--jsaddlinkDIVPHP-->*/
    }
    
    public static function generate()
    {

        $seasonVar = isset($_POST['season_id'])?  sanitize_text_field($_POST['season_id']):0;
        if ($seasonVar == '0') {
            $season_id = 0;
            $group_id = 0;
        } else {
            $ex = explode('|', $seasonVar);
            $season_id = $ex[0];
            $group_id = $ex[1];
        }
        $md_type = isset($_POST['md_type'])?  intval($_POST['md_type']):0;
        
        
        if ($md_type) {
            $format_post = isset($_POST['format_post'])?  intval($_POST['format_post']):0;
            $teams_knock = isset($_POST['teams_knock'])?  array_map('intval', $_POST['teams_knock']):array();

            return self::algoritm_knock($format_post, $teams_knock);
        } else {
            $team_number_id = isset($_POST['team_number_id'])?  ($_POST['team_number_id']):array();

            $rounds = isset($_POST['rounds'])?  intval($_POST['rounds']):0;
            if (count($team_number_id) < 4) {
                //something wrong
                return __("Generating failed! At least 4 participants are required.",'joomsport-sports-league-results-management');
            } else {
                if(isset($_POST['sc_algorithm']) && $_POST['sc_algorithm'] == 1){
                    return self::algoritm2($team_number_id, $rounds);
                }else{
                    return self::algoritm1($team_number_id, $rounds);
                }
                
            }
        }
    }

    public static function algoritm1($teams, $rounds)
    {
        if (count($teams) % 2 != 0) {
            array_push($teams, 0);
        }
        $halfarr = count($teams) / 2;
        $md_name = isset($_POST['mday_name'])?(sanitize_text_field($_POST['mday_name'])):'Matchday';
        $round_day = 1;
        for ($intR = 0; $intR < $rounds; ++$intR) {
            $duo_teams = array_chunk($teams,  $halfarr);
            $duo_teams[1] = array_reverse($duo_teams[1]);
            $continue = true;
            $first_team = $duo_teams[0][0];
            $last_team = $duo_teams[1][0];
            while ($continue) {
                $intB = 0;
                $matchday_id = self::create_mday(0, $md_name.' '.$round_day, $round_day);
                if( is_wp_error( $matchday_id ) ) {
                    return $matchday_id;
                }
                foreach ($duo_teams[0] as $home) {
                    if ($intR % 2 == 0) {
                        $row['home'] = $home;
                        $row['away'] = $duo_teams[1][$intB];
                    } else {
                        $row['away'] = $home;
                        $row['home'] = $duo_teams[1][$intB];
                    }
                    if($matchday_id){
                        if ($row['home'] && $row['away']) {
                            self::addMatch($row, $matchday_id, $intB);
                        }
                    }    
                    ++$intB;
                }
                ++$round_day;

                $tmp = $duo_teams[0][$halfarr - 1];
                $to_top = $duo_teams[1][0];
                unset($duo_teams[1][0]);
                unset($duo_teams[0][$halfarr - 1]);
                array_push($duo_teams[1], $tmp);
                $duo_teams[1] = array_values($duo_teams[1]);
                $arr_start = array($duo_teams[0][0], $to_top);
                $arr_end = array_slice($duo_teams[0], 1);
                if (count($arr_end)) {
                    $arr_start = array_merge($arr_start, $arr_end);
                }
                $duo_teams[0] = $arr_start;
                if ($duo_teams[1][0] == $last_team) {
                    $continue = false;
                }
            }
        }
        return '';
    }
    public static function algoritm2($teams, $rounds){
        $number = count($teams);
        
        if($number % 2 !=0){
            $number++;
            array_push($teams, 0);
        }
        $array = $teams;
        $last_child = $teams[$number-1];
        $initial = $array;
        array_pop($array);
        $cirle = $number / 2;
        $cuts = $cirle - 1;
        $round_day = 1;
        $md_name = isset($_POST['mday_name'])?(sanitize_text_field($_POST['mday_name'])):'Matchday';
        
        for($intRounds=0;$intRounds<$rounds;$intRounds++){

            for($intR=0;$intR<$number-1;$intR++){
                $row = array();
                
                $output1 = array_slice($array,1,$cuts);
                $output2 = array_slice($array,$cuts+1);
                $output2 = array_reverse($output2);
                $intB = 0;
                
                $matchday_id = self::create_mday(0, $md_name.' '.$round_day, $round_day);
                
                if( is_wp_error( $matchday_id ) ) {
                    return $matchday_id;
                }
                //echo " Round {$intR}<br />";
                if($intR % 2 == 0 ){
                    if($intRounds % 2 == 0){
                        //echo $array[0] . ' vs ' .$last_child."<br />";
                        $row['home'] = $array[0];
                        $row['away'] = $last_child;
                    }else{
                        //echo $last_child . ' vs ' .$array[0]."<br />";
                        $row['home'] = $last_child;
                        $row['away'] = $array[0];
                    }

                }else{
                    if($intRounds % 2 == 0){
                        //echo $last_child . ' vs ' .$array[0]."<br />";
                        $row['home'] = $last_child;
                        $row['away'] = $array[0];
                    }else{
                        //echo $array[0] . ' vs ' .$last_child."<br />";
                        $row['home'] = $array[0];
                        $row['away'] = $last_child;
                    }
                }
                if($matchday_id){
                    if ($row['home'] && $row['away']) {
                        self::addMatch($row, $matchday_id, $intB);
                    }
                } 
                foreach($output1 as $op){
                    if($intRounds % 2 == 0){
                        //echo $op . ' vs ' .$output2[$intB]."<br />";
                        $row['home'] = $op;
                        $row['away'] = $output2[$intB];
                    }else{
                        //echo $output2[$intB] . ' vs ' .$op."<br />";
                        $row['home'] = $output2[$intB];
                        $row['away'] = $op;
                    }

                    $intB++;
                    if($matchday_id){
                        if ($row['home'] && $row['away']) {
                            self::addMatch($row, $matchday_id, $intB);
                        }
                    } 
                }
                //echo "<br /><br />";
                for($intC=0;$intC<$cirle-1;$intC++){
                    array_unshift($array, array_pop($array));
                }
                $round_day++;
            }
        }
        
        
        
        return '';
    }
    public static function addMatch($row, $matchday_id, $ordering)
    {
        $md_type = isset($_POST['md_type'])?  intval($_POST['md_type']):0;
        $seasonVar = isset($_POST['season_id'])?  sanitize_text_field($_POST['season_id']):0;
        if ($seasonVar == '0') {
            $season_id = 0;
            $group_id = 0;
        } else {
            $ex = explode('|', $seasonVar);
            $season_id = $ex[0];
            $group_id = $ex[1];
        }
        
        $post_name = '';
        
        $terms = wp_get_object_terms( $season_id, 'joomsport_tournament' );
        if( $terms ){
            $post_name .= $terms[0]->slug;
        }
        $post_name .= " ".get_the_title($season_id);

        $metadata = get_post_meta(intval($row['home']),'_joomsport_team_personal',true);
        $home_team = isset($metadata['middle_name'])?(sanitize_text_field($metadata['middle_name'])):"";

        $metadata = get_post_meta(intval($row['away']),'_joomsport_team_personal',true);
        $away_team = isset($metadata['middle_name'])?(sanitize_text_field($metadata['middle_name'])):"";
        if(!$home_team){
            $home_team = get_the_title(intval($row['home']));
        }
        if(!$away_team){
            $away_team = get_the_title(intval($row['away']));
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
        
        $post_id = wp_insert_post( $arr );

        if($post_id){
            update_post_meta($post_id, '_joomsport_home_team', intval($row['home']));
            update_post_meta($post_id, '_joomsport_away_team', intval($row['away']));
            update_post_meta($post_id, '_joomsport_home_score', '');
            update_post_meta($post_id, '_joomsport_away_score', '');
            update_post_meta($post_id, '_joomsport_groupID', $group_id);
            update_post_meta($post_id, '_joomsport_seasonid', $season_id);
            update_post_meta($post_id, '_joomsport_match_date', '');
            update_post_meta($post_id, '_joomsport_match_time', '');
            update_post_meta($post_id, '_joomsport_match_played', '0');
            
            

        }

        wp_set_post_terms( $post_id, array((int) $matchday_id), 'joomsport_matchday');

        jsHelperMatchesDB::updateMatchDB($post_id);

        return $post_id;
    }

    public static function algoritm_knock($format_post, $teams_knock)
    {
        $md_name = isset($_POST['mday_name'])?(sanitize_text_field($_POST['mday_name'])):'Matchday';
        $participiants = array();
        array_rand($teams_knock);
        $participiants = $teams_knock;
        $half = intval($format_post / 2);
        if (count($teams_knock) >= $format_post) {
            $participiants = array_slice($participiants, 0, $format_post);
            $duo_teams = array_chunk($participiants,  $half);
        }

        if (count($teams_knock) < $format_post) {
            $duo_teams = array_chunk($participiants,  $half);
            for ($intA = 0; $intA < $half; ++$intA) {
                if (!isset($duo_teams[1][$intA])) {
                    $duo_teams[1][$intA] = -1;
                }
            }
            for ($intA = 0; $intA < $half; ++$intA) {
                if(!($intA % 2)){
                    if (($duo_teams[1][$half - $intA - 1]) == -1) {
                        $duo_teams[1][$half - $intA - 1] = $duo_teams[1][$intA];
                        $duo_teams[1][$intA] = -1;
                    }

                }
            }

        }
        
        $matchday_id = self::create_mday($format_post, $md_name,0);
        if( is_wp_error( $matchday_id ) ) {
            return $matchday_id;
        }
        $matches_knock = array();
        for ($intA = 0; $intA < count($duo_teams[0]); ++$intA) {
            $row['home'] = $duo_teams[0][$intA];
            $row['away'] = $duo_teams[1][$intA];
            if($matchday_id){
                $matchID = self::addMatch($row, $matchday_id, $intA);
               
                $match = array();
                $match["match_id"] = array($matchID);
                $match["home"] = $row['home'];
                $match["away"] = $row['away'];
                $match["score1"] = array('');
                $match["score2"] = array('');
                $match["intA"] = $intA;
                $match["intB"] = 0;
                
                $matches_knock[0][$intA] = $match;
            }
        }
        //
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
        for($intA=1; $intA < intval($matrix_stages[$format_post]); $intA++){
            
            $matchesKnCount = $format_post/(4*$intA);
            for($intB=0;$intB<($matchesKnCount);$intB++){
                $row['home'] = 0;
                $row['away'] = 0;
                $matchID = self::addMatch($row, $matchday_id, 0);
               
                $match = array();
                $match["match_id"] = array($matchID);
                $match["home"] = 0;
                $match["away"] = 0;
                $match["score1"] = array('');
                $match["score2"] = array('');
                $match["intA"] = $intB*2*$intA;
                $match["intB"] = $intA;
                
                $matches_knock[$intA][$intB*2*$intA] = $match;
            }
        }        
        //
        //var_dump($matches_knock);die();
        $term_metas = get_option("taxonomy_{$matchday_id}_metas");
        if (!is_array($term_metas)) {
            $term_metas = Array();
        }
        $term_metas['knockout'] = $matches_knock;
        //var_dump($matches);
        update_option( "taxonomy_{$matchday_id}_metas", $term_metas );
    }

    public static function create_mday($format, $name, $ordering)
    {
        $seasonVar = isset($_POST['season_id'])?  sanitize_text_field($_POST['season_id']):0;
        if ($seasonVar == '0') {
            $season_id = 0;
            $group_id = 0;
        } else {
            $ex = explode('|', $seasonVar);
            $season_id = $ex[0];
            $group_id = $ex[1];
        }
        
        $md_type = isset($_POST['md_type'])?  intval($_POST['md_type']):0;
        
        $res = wp_insert_term($name, 'joomsport_matchday',array('slug'=>$name.'_'.$season_id.'_'.$group_id));

        if( is_wp_error( $res ) ) {
            //echo "<p class='notice notice-error'>".$res->get_error_message()."</p>";
            return $res;
        }
        if(isset($res['term_id']) && $res['term_id']){
            $term_id = $res['term_id'];
            $term_metas = array();
            $term_metas['season_id'] = $season_id;
            $term_metas['matchday_type'] = $md_type;
            if(isset($_POST['format_post']) && intval($_POST['format_post'])){
                $term_metas['knockout_format'] = intval($_POST['format_post']);
            }
            update_option( "taxonomy_{$term_id}_metas", $term_metas );
            return $term_id;
        }
        
        return 0;
        
    }
}