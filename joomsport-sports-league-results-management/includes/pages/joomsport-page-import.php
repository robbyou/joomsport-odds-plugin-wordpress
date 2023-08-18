<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

class JoomsportPageImport{
    public static function action(){
        global $wpdb;
        /*<!--jsonlyinproPHP-->*/
        $csv_fields = '';
        $error = '';
        $csvcat = filter_input(INPUT_POST, 'import_type');
        $season_id = filter_input(INPUT_POST, 'season_id');
        $action = filter_input(INPUT_POST, 'action');
        if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
            
            if($action == 'upload'){
            
                if(!empty($_FILES['csvupload']))
                {
                    $csv_lines = array_map('str_getcsv', file($_FILES['csvupload']['tmp_name']));
                    $path = get_temp_dir();
                    move_uploaded_file($_FILES['csvupload']['tmp_name'], $path."/1.csv");
                }
                switch ($csvcat) {
                    case '3': //fixtures
                        $fields = array();

                        //extra fields
                        $query = "SELECT id,name FROM {$wpdb->joomsport_ef} as ef WHERE ef.type = 3";
                        
                        $extra_s = $wpdb->get_results($query);

                        $fields[] = JoomSportHelperSelectBox::addOption("", __('Select', 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("m_date", __("Date", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("m_time", __("Time", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("team1_id", __("Home Team", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("team2_id", __("Away Team", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("score1", __("Home Score", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("score2", __("Away Score", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("md_name", __("Matchday", 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("venue", __("Venue", 'joomsport-sports-league-results-management'));
                        
                        if(count($extra_s)){
                            foreach($extra_s as $extra){
                                $fields[] = JoomSportHelperSelectBox::addOption("field_".$extra->id, $extra->name);
                            }
                        }
                        
                        $csv_fields = JoomSportHelperSelectBox::Simple('csv_fields[]',   $fields, '', '', false );

                        break;

                    case '1': //players

                        //extra fields
                        $query = "SELECT id,name FROM {$wpdb->joomsport_ef} as ef WHERE ef.type = 0";
                        if(!$season_id){
                            $query .= " AND season_related = '0'";
                        }
                        $extra_s = $wpdb->get_results($query);

                        $fields = array();
                        $fields[] = JoomSportHelperSelectBox::addOption("", __('Select', 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("first_name", __('First Name', 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("last_name", __('Last Name', 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("full_name", __('Full Name', 'joomsport-sports-league-results-management'));

                        $fields[] = JoomSportHelperSelectBox::addOption("team", __('Team', 'joomsport-sports-league-results-management'));

                        if(count($extra_s)){
                            foreach($extra_s as $extra){
                                $fields[] = JoomSportHelperSelectBox::addOption("field_".$extra->id, $extra->name);
                            }
                        }

                        $csv_fields = JoomSportHelperSelectBox::Simple('csv_fields[]', $fields,"",'',false);


                        break;
                    case '2': //teams

                        //extra fields
                        $query = "SELECT id,name FROM {$wpdb->joomsport_ef} as ef WHERE ef.type = 1";
                        if(!$season_id){
                            $query .= " AND season_related = '0'";
                        }
                        $extra_s = $wpdb->get_results($query);

                        $fields = array();
                        $fields[] = JoomSportHelperSelectBox::addOption("", __('Select', 'joomsport-sports-league-results-management'));
                        $fields[] = JoomSportHelperSelectBox::addOption("team", __('Team', 'joomsport-sports-league-results-management'));
                        
                        if(count($extra_s)){
                            foreach($extra_s as $extra){
                                $fields[] = JoomSportHelperSelectBox::addOption("field_".$extra->id, $extra->name);
                            }
                        }

                        $csv_fields = JoomSportHelperSelectBox::Simple('csv_fields[]', $fields,"",'',false);


                    break;    

                    default:
                        break;
                }
            }elseif($action == 'import'){
                //csv fields
                $csv_fields = isset($_REQUEST['csv_fields']) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['csv_fields'] ) ) : array();
                $dellines = isset($_REQUEST['dellines']) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['dellines'] ) ) : array();
                
                $db_match = array();
                
                for($intA = 0; $intA < count($csv_fields); $intA++){
                    $db_match[$csv_fields[$intA]] = $intA;
                }
                $path = get_temp_dir();
                $csv_lines = array_map('str_getcsv', file($path."/1.csv"));
                
                switch ($csvcat) {
                    case '3': //fixtures
                        $teamsID = get_post_meta($season_id,'_joomsport_season_participiants',true);
                        for($intA = 0; $intA < count($csv_lines); $intA++){
                                if(!in_array($intA, $dellines)){
                                    if(count($csv_lines[$intA])){
                                        $team1 = isset($db_match['team1_id'])?$csv_lines[$intA][$db_match['team1_id']]:null;
                                        $team2 = isset($db_match['team2_id'])?$csv_lines[$intA][$db_match['team2_id']]:null;
                                        $score1 = isset($db_match['score1'])?$csv_lines[$intA][$db_match['score1']]:null;
                                        $score2 = isset($db_match['score2'])?$csv_lines[$intA][$db_match['score2']]:null;
                                        $md_name = isset($db_match['md_name'])?$csv_lines[$intA][$db_match['md_name']]:null;
                                        $m_date = isset($db_match['m_date'])?$csv_lines[$intA][$db_match['m_date']]:null;
                                        $m_time = isset($db_match['m_time'])?$csv_lines[$intA][$db_match['m_time']]:null;
                                        $venue = isset($db_match['venue'])?$csv_lines[$intA][$db_match['venue']]:null;
                                        
                                        if($m_date){
                                            $tmp_date = explode('/', $m_date);
                                            $m_date = $tmp_date[2].'-'.$tmp_date[1].'-'.$tmp_date[0];
                                        }
                                        $matchday_id = 0;
                                        if($md_name){
                                            $term = get_term_by( 'slug', $md_name.'_'.$season_id, 'joomsport_matchday');
                                            if(!isset($term->term_id)){
                                                $res = wp_insert_term($md_name, 'joomsport_matchday',array('slug'=>$md_name.'_'.$season_id));

                                                if( is_wp_error( $res ) ) {
                                                     $error .= "Can't create matchday ".$md_name.". <br />";   
                                                }
                                                if(isset($res['term_id']) && $res['term_id']){
                                                    $term_id = $res['term_id'];
                                                    $term_metas = array();
                                                    $term_metas['season_id'] = $season_id;
                                                    $term_metas['matchday_type'] = 0;
                                                    
                                                    update_option( "taxonomy_{$term_id}_metas", $term_metas );
                                                    $matchday_id = $term_id;
                                                }
                                            }else{
                                                $matchday_id = $term->term_id;
                                            }
                                            
                                        }
                                        if(!$matchday_id){
                                            $error .= "Matchday not specified. <br />";
                                            continue;
                                        }
                                        if($team1){
                                            $team1_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='joomsport_team' LIMIT 1", $team1) );
                                            if($team1_id){
                                                if(!in_array($team1_id,$teamsID)){
                                                    $teamsID[] = $team1_id;
                                                    update_post_meta($season_id,'_joomsport_season_participiants',$teamsID);
                                                }
                                                
                                            }else{
                                                $error .= "Unknow team ".$team1.". <br />";
                                                continue;
                                            }
                                        }else{
                                            $error .= "Home team not specified. <br />";
                                            continue;
                                        }
                                        if($team2){
                                            $team2_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='joomsport_team' LIMIT 1", $team2) );
                                            if($team2_id){
                                                if(!in_array($team2_id,$teamsID)){
                                                    $teamsID[] = $team2_id;
                                                    update_post_meta($season_id,'_joomsport_season_participiants',$teamsID);
                                                }
                                                
                                            }else{
                                                $error .= "Unknow team ".$team2.". <br />";
                                                continue;
                                            }
                                        }else{
                                            $error .= "Away team not specified. <br />";
                                            continue;
                                        }
                                        $venue_id = 0;
                                        if($venue){
                                            $venue_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='joomsport_venue' LIMIT 1", $venue) );
                                            if(!$venue_id){
                                                $arr = array(
                                                        'post_type' => 'joomsport_venue',
                                                        'post_title' => wp_strip_all_tags( $venue ),
                                                        'post_content' => '',
                                                        'post_status' => 'publish',
                                                        'post_author' => get_current_user_id()
                                                );
                                                $venue_id = wp_insert_post( $arr );
                                            }
                                        }
                                        $match_id = 0;
                                        $metaquery = 
                                                array(
                                                    'relation' => 'AND',
                                                        array(
                                                    'key' => '_joomsport_home_team',
                                                    'value' => $team1_id
                                                    ),

                                                    array(
                                                    'key' => '_joomsport_away_team',
                                                    'value' => $team2_id
                                                    ) 
                                                ) ;
                                        
                                        $matches_old = get_posts(array(
                                            'post_type' => 'joomsport_match',
                                            'posts_per_page' => -1,
                                            'offset'           => 0,
                                            'tax_query' => array(
                                                array(
                                                'taxonomy' => 'joomsport_matchday',
                                                'field' => 'term_id',
                                                'terms' => $matchday_id)
                                            ),
                                            'meta_query' => $metaquery)
                                        );
                                        if(count($matches_old)){
                                            $match_id = $matches_old[0]->ID;
                                        }
                                        if(!$match_id){
                                            $title = $team1 .(empty(JoomsportSettings::get('jsconf_home_away_separator_vs')) ? ' vs ' : ' '.JoomsportSettings::get('jsconf_home_away_separator_vs').' ').$team2;
                                            $arr = array(
                                                'post_type' => 'joomsport_match',
                                                'post_title' => wp_strip_all_tags( $title ),
                                                'post_content' => '',
                                                'post_status' => 'publish',
                                                'post_author' => get_current_user_id()
                                            );


                                            $match_id = wp_insert_post( $arr );
                                        }
                                        
                                        if($match_id){
                                            update_post_meta($match_id, '_joomsport_home_team', intval($team1_id));
                                            update_post_meta($match_id, '_joomsport_away_team', intval($team2_id));
                                            update_post_meta($match_id, '_joomsport_home_score', sanitize_text_field($score1));
                                            update_post_meta($match_id, '_joomsport_away_score', sanitize_text_field($score2));
                                            $m_played = '0';
                                            if($score1 != '' && $score2 != ''){
                                                $m_played = '1';
                                            }    
                                            $match_date = get_post_meta( $match_id, '_joomsport_match_date', true );
                                            $match_time = get_post_meta( $match_id, '_joomsport_match_time', true );
                                            update_post_meta($match_id, '_joomsport_match_date', sanitize_text_field($m_date));
                                            update_post_meta($match_id, '_joomsport_match_time', sanitize_text_field($m_time));


                                            update_post_meta($match_id, '_joomsport_match_played', $m_played);
                                            wp_set_post_terms( $match_id, array((int)$matchday_id), 'joomsport_matchday');
                                            update_post_meta($match_id, '_joomsport_seasonid', $season_id);
                                            update_post_meta($match_id, '_joomsport_match_venue', $venue_id);
                                            
                                            for($intB = 0; $intB < count($csv_fields); $intB++){
                                                if(substr($csv_fields[$intB],0,6) == 'field_'){
                                                    $field_id = intval(str_replace('field_', '', $csv_fields[$intB]));
                                                    if($field_id){
                                                        
                                                        $inserted_value = addslashes($csv_lines[$intA][$db_match[$csv_fields[$intB]]]);
                                                        
                                                        $query = "SELECT field_type FROM {$wpdb->joomsport_ef} as ef WHERE id=%d";
                                                        $field_type = $wpdb->get_var($wpdb->prepare($query, $field_id));
                                                        
                                                        if($field_type == '3'){
                                                            $query = "SELECT id FROM {$wpdb->joomsport_ef_select} WHERE fid=%d AND sel_value=%s";
                                                            $extraselect = $wpdb->get_var($wpdb->prepare($query, $field_id, $inserted_value));
                                                            if($extraselect){
                                                                $inserted_value = $extraselect;
                                                            }else{
                                                                $error .= "Unknow extra option -  ".$inserted_value.". <br /> ";
                                                                continue;
                                                            }
                                                        }
                                                        $playerEF = get_post_meta($match_id,'_joomsport_match_ef',true);
                                                        if(!$playerEF){
                                                                $playerEF = array();
                                                            }
                                                        $playerEF[$field_id] = $inserted_value;
                                                        update_post_meta($match_id, '_joomsport_match_ef', $playerEF);
                                                        
                                                    }
                                                }
                                            }
                                            do_action("joomsport_pull_match", $match_id);
                                            
                                        }

                                        
                                           
                                    }
                                }
                            }
                        
                        break;

                    case '1': //players
                        
                        $teamsID = get_post_meta($season_id,'_joomsport_season_participiants',true);
                        if(!is_array($teamsID)){
                            $teamsID = array();
                        }
                        
                        for($intA = 0; $intA < count($csv_lines); $intA++){
                                if(!in_array($intA, $dellines)){
                                    if(count($csv_lines[$intA])){
                                        $first_name = isset($db_match['first_name'])?$csv_lines[$intA][$db_match['first_name']]:null;
                                        $last_name = isset($db_match['last_name'])?$csv_lines[$intA][$db_match['last_name']]:null;
                                        $full_name = isset($db_match['full_name'])?$csv_lines[$intA][$db_match['full_name']]:null;
                                        
                                        $player_name = $full_name?$full_name:($first_name?$first_name.' '.$last_name:$last_name);
                                        
                                        $team = isset($db_match['team'])?($csv_lines[$intA][$db_match['team']]):null;
                                        if($player_name){
                                            $player_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='joomsport_player' LIMIT 1", $player_name) );
                                            if(!$player_id){
                                                $arr = array(
                                                        'post_type' => 'joomsport_player',
                                                        'post_title' => wp_strip_all_tags( $player_name ),
                                                        'post_content' => '',
                                                        'post_status' => 'publish',
                                                        'post_author' => get_current_user_id()
                                                );
                                                $player_id = wp_insert_post( $arr );
                                            }
                                            $personal = array();
                                            if($first_name || $last_name){
                                                $personal['first_name'] = $first_name;
                                                $personal['last_name'] = $last_name;
                                                update_post_meta($player_id, '_joomsport_player_personal', $personal);
                                            }
                                            if($team){
                                                $team_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='joomsport_team' LIMIT 1", $team) );
                                                if($team_id){
                                                    if(!in_array($team_id,$teamsID)){
                                                        $teamsID[] = $team_id;
                                                        update_post_meta($season_id,'_joomsport_season_participiants',$teamsID);
                                                    }
                                                    $playersin = get_post_meta($team_id,'_joomsport_team_players_'.$season_id,true);
                                                    if(!$playersin || !is_array($playersin)){
                                                        $playersin = array();
                                                    }
                                                    if(!in_array($player_id,$playersin)){
                                                        $playersin[] = $player_id;
                                                        update_post_meta($team_id, '_joomsport_team_players_'.$season_id, $playersin);
                                                    }
                                                }else{
                                                    $error .= "Unknow team ".$team.". <br />";
                                                    continue;
                                                }
                                            }
                                                
                                            for($intB = 0; $intB < count($csv_fields); $intB++){
                                                if(substr($csv_fields[$intB],0,6) == 'field_'){
                                                    $field_id = intval(str_replace('field_', '', $csv_fields[$intB]));
                                                    if($field_id){
                                                        $query = "SELECT season_related FROM {$wpdb->joomsport_ef} as ef WHERE id=%d";
                                                        $season_related = $wpdb->get_var($wpdb->prepare($query, $field_id));
                                                        
                                                        $inserted_value = addslashes($csv_lines[$intA][$db_match[$csv_fields[$intB]]]);
                                                        
                                                        $query = "SELECT field_type FROM {$wpdb->joomsport_ef} as ef WHERE id=%d";
                                                        $field_type = $wpdb->get_var($wpdb->prepare($query, $field_id));
                                                        
                                                        if($field_type == '3'){
                                                            $query = "SELECT id FROM {$wpdb->joomsport_ef_select} WHERE fid=%d AND sel_value=%s";
                                                            $extraselect = $wpdb->get_var($wpdb->prepare($query, $field_id, $inserted_value));
                                                            if($extraselect){
                                                                $inserted_value = $extraselect;
                                                            }else{
                                                                $error .= "Unknow extra option -  ".$inserted_value.". <br /> ";
                                                                continue;
                                                            }
                                                        }
                                                        
                                                        
                                                        if($season_related == '1' && $season_id){
                                                            $playerEF = get_post_meta($player_id,'_joomsport_player_ef_'.$season_id,true);
                                                            if(!$playerEF || !is_array($playerEF)){
                                                                $playerEF = array();
                                                            }
                                                            $playerEF[$field_id] = $inserted_value;
                                                            update_post_meta($player_id, '_joomsport_player_ef_'.$season_id, $playerEF);
                                                        }elseif($season_related == '0'){
                                                            $playerEF = get_post_meta($player_id,'_joomsport_player_ef',true);
                                                            if(!$playerEF || !is_array($playerEF)){
                                                                $playerEF = array();
                                                            }
                                                            $playerEF[$field_id] = $inserted_value;
                                                            update_post_meta($player_id, '_joomsport_player_ef', $playerEF);
                                                        }
                                                        
                                                    }
                                                }
                                            }        
                                        }
                                    }
                                }
                            }
                            
                        break;
                        case '2': //teams
                        
                        
                        for($intA = 0; $intA < count($csv_lines); $intA++){
                                if(!in_array($intA, $dellines)){
                                    if(count($csv_lines[$intA])){
                                        
                                        $team = isset($db_match['team'])?($csv_lines[$intA][$db_match['team']]):null;
                                        
                                            
                                        if($team){
                                            $team_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='joomsport_team' LIMIT 1", $team) );
                                            if(!$team_id){
                                                $arr = array(
                                                    'post_type' => 'joomsport_team',
                                                    'post_title' => wp_strip_all_tags( $team ),
                                                    'post_content' => '',
                                                    'post_status' => 'publish',
                                                    'post_author' => get_current_user_id()
                                                );
                                                $team_id = wp_insert_post( $arr );
                                            }
                                            $metadata = get_post_meta($season_id,'_joomsport_season_participiants',true);
                                            if(!is_array($metadata)){
                                                $metadata = array();
                                            }
                                            if(!in_array($team_id,$metadata)){
                                                $metadata[] = $team_id;
                                                update_post_meta($season_id, '_joomsport_season_participiants', $metadata);
                                            }
                                                
                                            
                                                
                                            for($intB = 0; $intB < count($csv_fields); $intB++){
                                                if(substr($csv_fields[$intB],0,6) == 'field_'){
                                                    $field_id = intval(str_replace('field_', '', $csv_fields[$intB]));
                                                    if($field_id){
                                                        $query = "SELECT season_related FROM {$wpdb->joomsport_ef} as ef WHERE id=%d";
                                                        $season_related = $wpdb->get_var($wpdb->prepare($query, $field_id));
                                                        
                                                        $inserted_value = addslashes($csv_lines[$intA][$db_match[$csv_fields[$intB]]]);
                                                        
                                                        $query = "SELECT field_type FROM {$wpdb->joomsport_ef} as ef WHERE id=%d";
                                                        $field_type = $wpdb->get_var($wpdb->prepare($query, $field_id));
                                                        
                                                        if($field_type == '3'){
                                                            $query = "SELECT id FROM {$wpdb->joomsport_ef_select} WHERE fid=%d AND sel_value=%s";
                                                            $extraselect = $wpdb->get_var($wpdb->prepare($query, $field_id, $inserted_value));
                                                            if($extraselect){
                                                                $inserted_value = $extraselect;
                                                            }else{
                                                                $error .= "Unknow extra option -  ".$inserted_value.". <br /> ";
                                                                continue;
                                                            }
                                                        }
                                                        
                                                        
                                                        if($season_related == '1' && $season_id){
                                                            $playerEF = get_post_meta($team_id,'_joomsport_team_ef_'.$season_id,true);
                                                            if(!$playerEF){
                                                                $playerEF = array();
                                                            }
                                                            $playerEF[$field_id] = $inserted_value;
                                                            update_post_meta($team_id, '_joomsport_team_ef_'.$season_id, $playerEF);
                                                        }elseif($season_related == '0'){
                                                            $playerEF = get_post_meta($team_id,'_joomsport_team_ef',true);
                                                            if(!$playerEF){
                                                                $playerEF = array();
                                                            }
                                                            $playerEF[$field_id] = $inserted_value;
                                                            update_post_meta($team_id, '_joomsport_team_ef', $playerEF);
                                                        }
                                                        
                                                    }
                                                }
                                            } 
                                        }
                                        
                                    }
                                }
                            }
                            
                        break;

                    default:
                        break;
                }
                if ($error) {
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <?php echo esc_html($error);?>
                    </div>
                    <?php
                }else{
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <?php echo __("Data successfully imported",'joomsport-sports-league-results-management');?>
                    </div>
                    <?php
                }
            }
            
            
        }
        $import_type = array();
        $import_type[] = JoomSportHelperSelectBox::addOption("1", __('Players', 'joomsport-sports-league-results-management'));
        $import_type[] = JoomSportHelperSelectBox::addOption("2", __('Teams', 'joomsport-sports-league-results-management'));
        $import_type[] = JoomSportHelperSelectBox::addOption("3", __('Matches', 'joomsport-sports-league-results-management'));
        
        $seasons = JoomSportHelperObjects::getSeasons(-1, false);
        
        ?>
        <script>
        function Delete_tbl_row_csv(element, line) {
            var del_index = element.parentNode.parentNode.sectionRowIndex;
            var tbl_id = element.parentNode.parentNode.parentNode.parentNode.id;
            element.parentNode.parentNode.parentNode.deleteRow(del_index);

            var input_hidden = document.createElement("input");
            input_hidden.type = "hidden";
            input_hidden.name = "dellines[]";
            input_hidden.value = line;
            document.getElementById('div_lines_del').appendChild(input_hidden);
        }
        </script>
        <div class="jsSettingsPage">
            <div class="jsBepanel">
                    <div class="jsBEheader">
                        <?php echo __('Import data', 'joomsport-sports-league-results-management');?>
                    </div>
                    <div class="jsBEsettings">
                    <form method="post" id="jSportImportForm" name="jSportImportForm" enctype="multipart/form-data">
                    <table>
                        <tr>
                            <td style="padding: 5px 0px;" width="130">
                                <?php echo __("Type",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <?php 
                                
                                if(empty($csv_lines) || $action == 'import'){
                                    echo wp_kses(JoomSportHelperSelectBox::Simple('import_type', $import_type,$csvcat,' id="import_type_selid" onchange="importCSVExample();"',true), JoomsportSettings::getKsesSelect());
                                    echo '&nbsp;<a style="display:none;" id="exampleCSVPlayer" href="https://joomsport.com/media/com_hikashop/upload/example_player.csv">Players CSV example</a>
                                        <a style="display:none;" id="exampleCSVTeam" href="https://joomsport.com/media/com_hikashop/upload/example_team.csv">Teams CSV example</a>
                                        <a style="display:none;" id="exampleCSVMatch" href="https://joomsport.com/media/com_hikashop/upload/example_match.csv">Matches CSV example</a>';
                                }else{
                                    echo '<input type="hidden" name="import_type" value="'.esc_attr($csvcat).'" />';
                                    switch ($csvcat) {
                                        case 1:
                                            echo __('Players', 'joomsport-sports-league-results-management');

                                        break;
                                        case 2:
                                            echo __('Teams', 'joomsport-sports-league-results-management');

                                        break;
                                        case 3:
                                            echo __('Matches', 'joomsport-sports-league-results-management');

                                        break;

                                        default:
                                            break;
                                    }
                                }
                                    
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0px;" width="130">
                                <?php echo __("Season",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <?php
                                    echo wp_kses(JoomSportHelperSelectBox::Optgroup('season_id', $seasons,$season_id,' id="season_id"',true,''), JoomsportSettings::getKsesSelect());
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0px;" width="130">
                                <?php echo __("CSV file",'joomsport-sports-league-results-management');?>
                            </td>
                            <td style="padding: 5px 10px;">
                                <?php
                                if(empty($csv_lines) || $action == 'import'){
                                ?>
                                    <input type="file" name="csvupload" accept="text/csv" />
                                    <input class="button button-primary" type="submit" value="<?php echo esc_attr(__("Upload",'joomsport-sports-league-results-management'));?>" />
                                    <input type="hidden" name="action" value="upload" />
                                <?php 
                                }else{
                                ?>
                                    <input class="button button-primary" type="submit" value="<?php echo esc_attr(__("Import",'joomsport-sports-league-results-management'));?>" />
                                    <input type="hidden" name="action" value="import" />
                                <?php 
                                }
                                ?>
                            </td>
                        </tr>

                    </table>
                    
                    <div>
                        
                        <div>
                            
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
                        </div>
                        
                    </div>
                        
                        
                   <?php
                    if($action != 'import'){
                        $intA = 0;
                        if(!empty($csv_lines)){
                            echo '<table width="100%">';

                            foreach($csv_lines as $line){
                                if($intA == 0){
                                     echo '<tr>';
                                        echo '<th>&nbsp;</th>';
                                        foreach($line as $line_head){
                                            echo '<th>'.$csv_fields.'</th>';
                                        }
                                    echo '</tr>';
                                }
                                echo '<tr>';
                                echo '<td><input type="button" value="'.esc_attr(__("Delete",'joomsport-sports-league-results-management')).'" onclick="Delete_tbl_row_csv(this, '.$intA.');" /></td>';
                                if(count($line)){
                                    foreach ($line as $col){
                                        echo '<td>';
                                        echo $col;
                                        echo '</td>';
                                    }
                                }
                                echo '</tr>';
                                $intA ++;
                            }
                            echo '</table>';
                        }
                    }
                    ?>     
                    <div id="div_lines_del">
        
                    </div>    
                    </form>
                </div>
            </div>    
        </div>    
        <?php
        /*<!--/jsonlyinproPHP-->*/
        /*<!--jsaddlinkDIVPHP-->*/
    }
    
}