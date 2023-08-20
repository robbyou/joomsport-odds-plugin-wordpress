<?php

/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

add_action('edited_joomsport_matchday', 'joomsport_prediction_mday_fire_save', 13, 2);
        
add_action( 'save_post', 'joomsport_prediction_fire_save', 13 );
/*<!--jsonlyinproPHP-->*/
add_action( 'js_match_prediction', 'joomsport_prediction_matchblock', 13, 1 );
/*<!--/jsonlyinproPHP-->*/
function joomsport_prediction_mday_fire_save($term_id){
    global $wpdb;
    
    $metaquery = array();
        
    $metaquery[] = 
        array(
            'relation' => 'AND',
                array(
            'key' => '_joomsport_round_knock_mday',
            'value' => $term_id,
            'compare' => '='
            ),


        ) ;


    $roundsPosts = new WP_Query(array(
        'post_type' => 'jswprediction_round',
        'posts_per_page'   => -1,
        'orderby' => 'post_date',
        'order'=>'ASC',
        'meta_query' => $metaquery   
    ));

    for($intA=0;$intA<count($roundsPosts->posts);$intA++){
        JSPredictionsCalc::calculateKnockRound($roundsPosts->posts[$intA]->ID, $term_id);
    }
    
    
    
    $matches = get_posts(array(
            'post_type' => 'joomsport_match',
            'posts_per_page' => -1,
            'offset'           => 0,
            'tax_query' => array(
                array(
                'taxonomy' => 'joomsport_matchday',
                'field' => 'term_id',
                'terms' => $term_id)
            )
        )
        );

    $rounds_calc = array();
    for($intA=0;$intA<count($matches);$intA++){
        $match_id = $matches[$intA]->ID;
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
        if($m_played == 1){
            $rounds = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE match_id={$match_id}");
            if(!count($rounds)) { continue;}
            foreach ($rounds as $round) {
                $round_id = (int)$round->round_id;

                JSPredictionsCalc::calculateMatch($match_id, $round_id);
                $rounds_calc[] = $round_id;

            }
        }
    }
    $rounds_calc = array_unique($rounds_calc);

    if(count($rounds_calc)){
        foreach($rounds_calc as $rc){
            JSPredictionsCalc::calculateRound($rc);
        }
    }
    jspw_recalcStartdate($rounds_calc);

}


function joomsport_prediction_fire_save($match_id) {
    global $wpdb,$post_type;
    $recalcrounds = array();
    if(get_post_type($match_id) == 'joomsport_match'){
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
        if($m_played == 1){
            $rounds = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE match_id={$match_id}");
            if(!count($rounds)) { return;}
            foreach ($rounds as $round) {
                $round_id = (int)$round->round_id;
                JSPredictionsCalc::calculateMatch($match_id, $round_id);
                JSPredictionsCalc::calculateRound($round_id);
                $recalcrounds[] = $round_id;
            }
        }
    }
    jspw_recalcStartdate($recalcrounds);
}

function jspw_recalcStartdate($rounds){
    if(!count($rounds)){return;}
    $rounds = array_unique($rounds);
    foreach ($rounds as $round_id){
        global $wpdb;

        $roundtype = get_post_meta($round_id, '_joomsport_round_roundtype', true);

        $match_date = '';
        if($roundtype == 1){
            $mday = (int) get_post_meta($round_id,'_joomsport_round_knock_mday',true);
            if($mday){
                $matches = new WP_Query(array(
                    'post_type' => 'joomsport_match',
                    'posts_per_page'   => -1,
                    'orderby' => 'id',
                    'order'=>'ASC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'joomsport_matchday',
                            'field' => 'term_id',
                            'terms' => $mday)
                    )

                ));
                $matches = $matches->posts;
                for($intA=0;$intA<count($matches);$intA++){
                    $m_date = get_post_meta( $matches[$intA]->ID, '_joomsport_match_date', true );
                    $m_time = get_post_meta( $matches[$intA]->ID, '_joomsport_match_time', true );
                    if($m_date){
                        if(!$match_date || $match_date > $m_date.' '.$m_time){

                            $match_date = $m_date.' '.$m_time;
                        }
                    }
                }

            }
        }else{
            $matches = $wpdb->get_col("SELECT match_id "
                . " FROM {$wpdb->jswprediction_round_matches}"
                . " WHERE round_id={$round_id}");


            for($intA=0;$intA<count($matches);$intA++){
                $m_date = get_post_meta( $matches[$intA], '_joomsport_match_date', true );
                $m_time = get_post_meta( $matches[$intA], '_joomsport_match_time', true );
                if($m_date){
                    if(!$match_date || $match_date > $m_date.' '.$m_time){
                        $match_date = $m_date.' '.$m_time;
                    }
                }
            }
        }



        update_post_meta($round_id, '_joomsport_round_start_match', $match_date);
    }
}

class JSPredictionsCalc{
    public static function calculateMatch($match_id, $round_id){
        global $wpdb;
        $allredycalc = array(); // Tableau pour stocker les scores déjà calculés pour éviter les redondances
        $leagueID = get_post_meta($round_id, '_joomsport_round_leagueid', true); // Récupère l'identifiant de la ligue à partir des métadonnées de la ronde
        $predLeague = get_post_meta($leagueID,'_jswprediction_league_points',true); // Récupère les points de prédiction de la ligue à partir des métadonnées de la ligue
        $path = JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR; // Chemin du dossier des classes de prédiction
        $match = JSPredictionsCalc::getMatch($match_id); // Récupère les scores du match à partir de la méthode getMatch

        if(count($predLeague)){
            $predictionsDBA = array();
            $predictionsDBAll = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_types}"); // Récupère toutes les définitions de types de prédiction depuis la table des types de prédiction
            for($intP=0;$intP<count($predictionsDBAll); $intP++){
                $predictionsDBA[$predictionsDBAll[$intP]->id] = $predictionsDBAll[$intP]; // Stocke les définitions de types de prédiction dans un tableau associatif indexé par leur identifiant
            }

            $results = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id); // Récupère toutes les prédictions des utilisateurs pour la ronde donnée
            
            for($intA=0;$intA<count($results);$intA++){
                $oddPoints = 0;
                $points = NULL; // Initialise les points à NULL pour chaque prédiction
                $pred = json_decode($results[$intA]->prediction,true); // Récupère les données de prédiction de l'utilisateur sous forme de tableau associatif
                //si l'utilisateur a predit la défaite de l'equipe à domicile, recuperer la metadonnée ' loose_odds', si l'utilisateur a predit la victoire de l'equipe à domicile, recuperer la metadonnée ' win_odds', sinon recupérer la métadonnée 'draw_odds'. 
                
                
                // ob_start();
                // if(isset($pred['score'][$match_id])){
                //     echo 'tableau' .$allredycalc[$pred['score'][$match_id]["score1"]."-".$pred['score'][$match_id]["score2"]];
                //     echo 'prediction score1 '.$pred['score'][$match_id]["score1"];
                //     var_dump($pred['score'][$match_id]["score2"]);
                //     echo 'prediction score2 '.$pred['score'][$match_id]["score2"];
                //     echo 'odd points'.$oddPoints;
                //     var_dump($oddPoints);
                //     $vcontents = ob_get_contents();
                // }
                // ob_end_clean();
                // error_log($vcontents);

                if(isset($pred['score'][$match_id])){ // Vérifie si l'utilisateur a prédit le score pour le match donné

                    if(isset($allredycalc[$pred['score'][$match_id]["score1"]."-".$pred['score'][$match_id]["score2"]])){
                        $points = $allredycalc[$pred['score'][$match_id]["score1"]."-".$pred['score'][$match_id]["score2"]]; // Récupère les points calculés précédemment pour éviter les redondances

                    }else {
                        // Parcourt les types de prédiction de la ligue et vérifie s'ils correspondent à la prédiction de l'utilisateur
                        foreach ($predLeague as $key => $value) {
                            if (isset($predictionsDBA[$key])) {
                                $predictionsDB = $predictionsDBA[$key]; // Récupère les détails du type de prédiction à partir du tableau des définitions de types de prédiction
                            } else {
                                die(); // Arrête l'exécution si un type de prédiction n'est pas trouvé
                            }

                            $classN = 'JSPT' . $predictionsDB->identif; // Construit le nom de la classe spécifique de prédiction en fonction de son identifiant
                            if (is_file($path . $classN . '.php')) { // Vérifie si le fichier de la classe existe
                                require_once $path . $classN . '.php'; // Charge le fichier de la classe de prédiction spécifique
                                if (class_exists($classN)) {
                                    $predObject = new $classN; // Crée une instance de la classe de prédiction spécifique
                                    if ($points === NULL) {
                                        $score_tmp = $predObject->getScore($match, $pred['score'][$match_id]); // Appelle la méthode getScore() de la classe de prédiction spécifique pour vérifier la prédiction
                                        if ($score_tmp === true) {
                                            if(($predictionsDB->identif == 'ScoreWinner')){
                                                if(($pred['score'][$match_id]["score1"]) - ($pred['score'][$match_id]["score2"]) < 0 ){
                                                    $oddPoints = floatval(get_post_meta($match_id,'loose_odds',true))*10;
                                                }
                                                if(($pred['score'][$match_id]["score1"]) - ($pred['score'][$match_id]["score2"]) > 0 ){
                                                    $oddPoints = floatval(get_post_meta($match_id,'win_odds',true))*10;
                                                }
                                                if(($pred['score'][$match_id]["score1"]) - ($pred['score'][$match_id]["score2"]) === 0){
                                                    $oddPoints = floatval(get_post_meta($match_id,'draw_odds',true))*10;
                                                }
                                                $points = $value + $oddPoints ; // Si la prédiction est correcte, attribue les points définis pour ce type de prédiction
                                            }
                                        }
                                            
                                    } elseif ($predictionsDB->identif == 'ScoreBonus') {
                                        $score_tmp = $predObject->getScore($match, $pred['score'][$match_id]); // Vérifie à nouveau la prédiction si le type de prédiction est un bonus de score
                                        if ($score_tmp === true) {
                                            $points += $value; // Si la prédiction est correcte, ajoute les points définis pour ce type de prédiction (score bonus)
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $points_before_joker = $points; // Stocke les points avant d'appliquer le joker (si nécessaire)
                    if(isset($pred['score'][$match_id]["joker"]) && $pred['score'][$match_id]["joker"] == 1 && $points){
                        $points *=2; // Double les points si le joker est activé pour cette prédiction
                    }

                    if ($points == NULL) {
                        $points = 0; // Si aucun point n'a été attribué, initialise à 0
                    }
                    if ($points !== NULL) {
                        $pred['score'][$match_id]['points'] = $points; // Met à jour le tableau des scores prédits avec les points attribués
                        $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET prediction='" . addslashes(json_encode($pred)) . "'  WHERE id=" . $results[$intA]->id); // Met à jour la base de données avec les nouvelles données de prédiction pour l'utilisateur
                    }
                    $allredycalc[$pred['score'][$match_id]["score1"] . "-" . $pred['score'][$match_id]["score2"]] = $points_before_joker; // Stocke les points calculés pour éviter les redondances
                }
            }
        }
    }
    public static function getMatch($match_id){
        global $wpdb;
        //$jsconfig =  new JoomsportSettings();
        $match = new stdClass();
        $jmscore = get_post_meta($match_id, '_joomsport_match_jmscore',true);

        if(JoomsportSettings::get('partdisplay_awayfirst',0) == 1){
            $match->score2 = get_post_meta($match_id, '_joomsport_home_score', true);
            $match->score1 = get_post_meta($match_id, '_joomsport_away_score', true);

            if(isset($jmscore["is_extra"]) && $jmscore["is_extra"] == 1){
                if(intval($jmscore["aet1"]) > 0){
                    $match->score2 -= $jmscore["aet1"];
                }
                if(intval($jmscore["aet2"]) > 0){
                    $match->score1 -= $jmscore["aet2"];
                }
            }



        }else{
            $match->score1 = get_post_meta($match_id, '_joomsport_home_score', true);
            $match->score2 = get_post_meta($match_id, '_joomsport_away_score', true);

            if(isset($jmscore["is_extra"]) && $jmscore["is_extra"] == 1){
                if(intval($jmscore["aet1"]) > 0){
                    $match->score1 -= $jmscore["aet1"];
                }
                if(intval($jmscore["aet2"]) > 0){
                    $match->score2 -= $jmscore["aet2"];
                }
            }

        }
        
                            
        return $match;
    }
    public static function calculateRound($round_id){
        global $wpdb;

        $calc_complete = false;
        
        $settings = get_option("joomsport_prediction_settings","");
        if(isset($settings["roundcalc"]) && $settings["roundcalc"] == "1"){
            $calc_complete = true;
        }
        
        $round_complete = true;
        
        $all_matches = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$round_id}");
        $matches = array();
        for($intB = 0 ; $intB < count($all_matches); $intB ++){
            $m_played = (int) get_post_meta( $all_matches[$intB]->match_id, '_joomsport_match_played', true );
            if($m_played == '1'){
                $matches[] = $all_matches[$intB];
            }else{
                $round_complete = false;
            }
            
        }
        
        
        if(!$calc_complete || $round_complete){
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            
            for($intA=0;$intA<count($results);$intA++){
                $points = 0;
                $filled = 0;
                $success = 0;
                $winner_side = 0;
                $diff = 0;
                $pred = json_decode($results[$intA]->prediction,true);
                for($intB = 0 ; $intB < count($matches); $intB ++){
                    $match_id = $matches[$intB]->match_id;
                    $matches_res = JSPredictionsCalc::getMatch($match_id);

                    if(isset($pred['score'][$match_id]['points'])){
                        $points += $pred['score'][$match_id]['points'];
                        $filled++;
                        
                        if(($matches_res->score1 == $pred['score'][$match_id]['score1'])
                                && ($matches_res->score2 == $pred['score'][$match_id]['score2'])){
                            $success++;
                        }else
                        if(($matches_res->score1 - $matches_res->score2)
                                == ($pred['score'][$match_id]['score1'] - $pred['score'][$match_id]['score2'])){
                            $diff++;
                        }else
                        if(($matches_res->score1 > $matches_res->score2) && ($pred['score'][$match_id]['score1'] > $pred['score'][$match_id]['score2'])
                                || ($matches_res->score1 < $matches_res->score2) && ($pred['score'][$match_id]['score1'] < $pred['score'][$match_id]['score2'])){
                            $winner_side++;
                        }    
                    }
                }
                
                $wpdb->query("UPDATE {$wpdb->jswprediction_round_users}"
                        . " SET points='".$points."', filled = {$filled}, success = {$success}, winner_side = {$winner_side}, score_diff = {$diff}"
                        . "  WHERE id=".$results[$intA]->id);
                        
                    
            }    
        }else{
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            for($intA=0;$intA<count($results);$intA++){
                $wpdb->query("UPDATE {$wpdb->jswprediction_round_users}"
                         . " SET points='0', filled = 0, success = 0, winner_side = 0, score_diff = 0"
                         . "  WHERE id=".$results[$intA]->id);
            }
        }    
       
      
    }
    
    public static function calculateKnockRound($round_id, $mday_id){
        global $wpdb;

        $calc_complete = false;

        $settings = get_option("joomsport_prediction_settings","");
        if(isset($settings["roundcalc"]) && $settings["roundcalc"] == "1"){
            $calc_complete = true;
        }
        
        $round_complete = false;
        
        $knock_settings = get_post_meta($round_id,'_joomsport_round_knock_points',true);
        
        $metas = get_option("taxonomy_{$mday_id}_metas");
        $knockoutView = $metas['knockout'];
        $kformat = $metas["knockout_format"];
        $winnerID = isset($metas['winner'])?$metas['winner']:0;
        
        $matrix_stages = array(
            2 => 1,
            4 => 2,
            8 => 3,
            16 => 4,
            32 => 5,
            64 => 6,
            128 => 7
        );
        
        $stages = $matrix_stages[$kformat];
        
        if($winnerID){
            $round_complete = true;
        }
        
        if(!$calc_complete || $round_complete){
            $uResults = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            
            for($intU=0;$intU<count($uResults);$intU++){
                $pts = 0;
                $success = 0;
                $filled = 0;
                
                $prediction = json_decode($uResults[$intU]->prediction,true);
                
                for($intA=0; $intA < intval($kformat/2); $intA++){
                
                for($intB=0; $intB < $stages; $intB ++){
                    if($intA == 0 || ($intA % (pow(2,$intB)))==0){
                        
                        $kvalues = array(
                            "home" => 0,
                            "away" => 0,
                            "score1" => "",
                            "score2" => "",
                            "match_id" => ""
                        );
                        
                        $ceilN = floor($intA/($intB+1) / 2);
                        $od = ($intA/($intB+1) % 2);
                        if($ceilN == 0){
                            $newdt = 0;
                        }else{
                            $newdt = $ceilN * pow(2,($intB+1));
                        }
                        
                        
                        
                        if(isset($knockoutView[$intB][$intA])){
                            $kvalues = array(
                                "home" => $knockoutView[$intB][$intA]["home"],
                                "away" => $knockoutView[$intB][$intA]["away"],
                                "score1" => $knockoutView[$intB][$intA]["score1"],
                                "score2" => $knockoutView[$intB][$intA]["score2"],
                                "match_id" => $knockoutView[$intB][$intA]["match_id"]
                            );
                        }
                        

                        
                        $arrV = isset($prediction['knockpartic_'.$intA.'_'.$intB])?$prediction['knockpartic_'.$intA.'_'.$intB]:array();
                            
                        
                        if(isset($arrV[0]) && $arrV[0]){
                            if(isset($knockoutView[$intB+1][$newdt][$od?"away":"home"])){
                                $arrVNext = isset($prediction['knockpartic_'.$newdt.'_'.($intB+1)])?$prediction['knockpartic_'.$newdt.'_'.($intB+1)]:array();
                                
                                if(isset($arrVNext[$od]) && $arrVNext[$od] && $knockoutView[$intB+1][$newdt][$od?"away":"home"]){

                                    $filled++;
                                    //var_dump($knockoutView[$newdt][$intB+1]);
                                    if($knockoutView[$intB+1][$newdt][$od?"away":"home"] == $arrVNext[$od]){
                                        $success++;
                                        if(isset($knock_settings[$kformat/(pow(2,($intB+1)))])){
                                            $pts += $knock_settings[$kformat/(pow(2,($intB+1)))];
                                        }
                                    } 
                                }
                            }  
                            
                            if($intB == $stages-1 && isset($metas['winner']) && isset($prediction["knockpartic_winner"])){
                                $filled++;
                                if($metas['winner'] == $prediction["knockpartic_winner"]){
                                    $success++;
                                    if(isset($knock_settings[1])){
                                        $pts += $knock_settings[1];
                                    }
                                }
                                
                            }
                            
                            
                        }

                    }
                }

                
            }
            $wpdb->query("UPDATE {$wpdb->jswprediction_round_users}"
                        . " SET points='".$pts."', filled = {$filled}, success = {$success}, winner_side = 0, score_diff = 0"
                        . "  WHERE id=".$uResults[$intU]->id);
            
            }
        }
        
    }    
    
    
    public function rankRound(){
        
    }
}

/*<!--jsonlyinproPHP-->*/
function joomsport_prediction_matchblock($match_id){
    global $wpdb;
    wp_enqueue_style('jsprediction',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/prediction.css');
            
    $blocks_settings = get_option("joomsport_prediction_blocks_settings","");
    $top_predictions = isset($blocks_settings["top_predictions"])?$blocks_settings["top_predictions"]:0;
    $top_prediction_num = isset($blocks_settings["top_prediction_num"])?$blocks_settings["top_prediction_num"]:0;
    $winner_side = isset($blocks_settings["winner_side"])?$blocks_settings["winner_side"]:0;
    $both_score = isset($blocks_settings["both_score"])?$blocks_settings["both_score"]:0;
    $score_over = isset($blocks_settings["score_over"])?$blocks_settings["score_over"]:0;
    $score_over_num = isset($blocks_settings["score_over_num"])?$blocks_settings["score_over_num"]:0;
    
    $rounds = $wpdb->get_col("SELECT round_id FROM {$wpdb->jswprediction_round_matches} WHERE match_id=".$match_id);
    
    $predictions = array();
    if(count($rounds)){
        $results = $wpdb->get_results("SELECT prediction, user_id FROM {$wpdb->jswprediction_round_users} WHERE round_id IN (".implode(",",$rounds).")");
        $wpdb->query("DELETE FROM {$wpdb->jswprediction_scorepredict} WHERE match_id = {$match_id}");
        
        for($intA=0;$intA<count($results);$intA++){
            $pred = json_decode($results[$intA]->prediction, true);
            if(isset($pred['score'][$match_id]) && $pred['score'][$match_id]['score1'] !== ''){
                $wpdb->query("INSERT INTO {$wpdb->jswprediction_scorepredict}(match_id,user_id,score1,score2)"
                         . " VALUES({$match_id},{$results[$intA]->user_id},".$pred['score'][$match_id]['score1'].",".$pred['score'][$match_id]['score2'].") ");
            }
        }
    }
    $result_html = '';

    
    if($top_predictions && $top_prediction_num){
        $popular = $wpdb->get_results("SELECT COUNT(id) as cnt,score1,score2 FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " GROUP BY score1,score2"
                . " ORDER BY cnt DESC"
                . " LIMIT ".$top_prediction_num);
        $all = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}");
        
        if(count($popular) && $all){
            $result_html .= '<h3>'.sprintf(__("Top %s predictions by score", 'joomsport-prediction'), $top_prediction_num).'</h3>';
            for($intA=0;$intA<count($popular);$intA++){
                $result_html .= '<div class="jspred_tops"><div class="jspred_match_top_num col-xs-1">'.($intA+1).'.</div><div class="jspred_match_score col-xs-4">'.$popular[$intA]->score1.' : '.$popular[$intA]->score2.'</div><div class="jspred_match_top_percent col-xs-7"><span class="jspred_match_perc" style="width:'.round($popular[$intA]->cnt * 100 / $all).'%;"></span>'.round($popular[$intA]->cnt * 100 / $all).'%</div></div>';
            }
        }
    }
    if($winner_side){
        
        $win = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND score1 > score2 ");
        $lost = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND score1 < score2 ");
        $draw = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND score1 = score2 ");
        if($win+$draw+$lost > 0){
            $result_html .= '<h3>'.__("Predictions: Winner side", 'joomsport-prediction').'</h3>';
            //$result_html .= '<div>'.round($win*100/($win+$draw+$lost)).'% : '.round($draw*100/($win+$draw+$lost)).'% : '.round($lost*100/($win+$draw+$lost)).'%</div>';
        
            $result_html .= '<table class="jspred_match_side_table">';
            $result_html .= '<tr><th>'.__("Home", 'joomsport-prediction').'</th><th>'.__("Draw", 'joomsport-prediction').'</th><th>'.__("Away", 'joomsport-prediction').'</th></tr>';
            $result_html .= '<tr><td>'.round($win*100/($win+$draw+$lost)).'%</td><td>'.round($draw*100/($win+$draw+$lost)).'%</td><td>'.round($lost*100/($win+$draw+$lost)).'%</td></tr>';
            $result_html .= '</table>';
        }
        
    }
    if($both_score){
        
        
        $both = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND score1 > 0 AND score2 > 0 ");
        $both_no = $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND (score1 = 0 OR score2 = 0) ");
        if($both+$both_no > 0){
            $result_html .= '<h3>'.__("Predictions: Both teams to score", 'joomsport-prediction').'</h3>';
            $result_html .= '<table class="jspred_match_both_score">';
            $result_html .= '<tr><th>'.__("Yes", 'joomsport-prediction').'</th><th>'.__("No", 'joomsport-prediction').'</th></tr>';
            $result_html .= '<tr><td>'.round($both*100/($both+$both_no)).'%</td><td>'.round($both_no*100/($both+$both_no)).'%</td></tr>';
            $result_html .= '</table>';
            
        }
        
    }
    if($score_over && $score_over_num){
            
        $more = $wpdb->get_var("SELECT COUNT(id) as cnt FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND (score1+score2) > ".($score_over_num));
        $less = $wpdb->get_var("SELECT COUNT(id) as cnt FROM {$wpdb->jswprediction_scorepredict}"
        . " WHERE match_id = {$match_id}"
        . " AND (score1+score2) <= ".($score_over_num));
        if($more+$less > 0){
            $result_html .= '<h3>'.sprintf(__("Predictions: Total score is over %s", 'joomsport-prediction'), $score_over_num).'</h3>';
            $result_html .= '<table class="jspred_match_total_score">';
            $result_html .= '<tr><th>'.__("Yes", 'joomsport-prediction').'</th><th>'.__("No", 'joomsport-prediction').'</th></tr>';
            $result_html .= '<tr><td>'.round($more*100/($more+$less)).'%</td><td>'.round($less*100/($more+$less)).'%</td></tr>';
            $result_html .= '</table>';
        }
        
    }

    if($result_html){
        echo '<div class="jspred_match_block">'.$result_html.'</div>';
    }
    //echo $result_html;

    //echo '---';
}
function jswprediction_invite_league_redirect() {

    global $post_type, $post, $wpdb;
    if($post_type == 'jswprediction_league'){
        if(isset($_GET['invitekey']) && $_GET['invitekey']) {


            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                    "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
                $_SERVER['REQUEST_URI'];

            $leagueID = $wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_private_league}"
                ." WHERE invitekey='".sanitize_text_field($_GET['invitekey'])."'");

            if(!get_current_user_id()){
                $settings = get_option("joomsport_prediction_settings","");
                $login_url = '';
                if(isset($settings["login_link"]) && $settings["login_link"]){
                    $login_url = get_site_url() . DIRECTORY_SEPARATOR . $settings["login_link"];

                    $login_url = add_query_arg( 'redirect_to', urlencode( get_permalink() ), $login_url );
                }
                if($login_url){
                    if (wp_redirect($login_url)) {
                        exit;
                    }
                }

            }else{

                jsPredictionHelper::addUserToPrivateLeague($leagueID,get_current_user_id(),1);

            }

            $settings = get_option("joomsport_prediction_settings","");
            if(isset($settings["plrivate_league_shortcode_link"]) && $settings["plrivate_league_shortcode_link"]){
                $link = get_site_url()."/".$settings["plrivate_league_shortcode_link"];
                if (wp_redirect($link)) {
                    exit;
                }
            }
            $link = get_permalink($post->ID);
            $link = add_query_arg( 'action', 'rounds', $link );

            if (wp_redirect($link)) {
                exit;
            }

        }
    }

}
add_action( 'template_redirect', 'jswprediction_invite_league_redirect' );
/*<!--/jsonlyinproPHP-->*/