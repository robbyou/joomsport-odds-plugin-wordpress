<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomSportPredictionMetaRound {
    public static function output( $post ) {
        global $post, $thepostid, $wp_meta_boxes;
        
        
        $thepostid = $post->ID;

        wp_nonce_field( 'jswprediction_round_savemetaboxes', 'jswprediction_round_nonce' );
        ?>
        <div id="joomsportContainerBE">

                <?php
                do_meta_boxes(get_current_screen(), 'jswprediction_tab_round1', $post);
                unset($wp_meta_boxes[get_post_type($post)]['jswprediction_tab_round1']);
                ?>
            <?php
            $leagueid = isset($_REQUEST['leagueid'])?  intval($_REQUEST['leagueid']):0;
            $roundtype = isset($_REQUEST['roundtype'])?  intval($_REQUEST['roundtype']):0;
            if(!$leagueid){
                $leagueid = get_post_meta($thepostid, '_joomsport_round_leagueid', true);
            }
            if(!$leagueid){
                echo 'WRONG LEAGUE';
            }
            ?>
            
            <input type="hidden" name="leagueid" value="<?php echo $leagueid;?>" />
            <input type="hidden" name="roundtype" value="<?php echo $roundtype;?>" />
        </div>
        

        <?php
    }
        
        
    public static function js_meta_matches($post){
        global $wpdb;
        $leagueid = isset($_REQUEST['leagueid'])?  intval($_REQUEST['leagueid']):0;
        if(!$leagueid){
            $leagueid = get_post_meta($post->ID, '_joomsport_round_leagueid', true);
        }
        $roundtype = isset($_REQUEST['roundtype'])?  intval($_REQUEST['roundtype']):0;
        if(!isset($_REQUEST['roundtype'])){
            $roundtype = get_post_meta($post->ID, '_joomsport_round_roundtype', true);
        }
        
        $seasons = get_post_meta($leagueid,'_jswprediction_league_seasons',true);
        
        if($roundtype == '1'){
            //knockout tree
            $mday = (int) get_post_meta($post->ID,'_joomsport_round_knock_mday',true);
            
            $terms = get_terms(array(
                'taxonomy' => 'joomsport_matchday',
                'hide_empty' => false,
            ));
            $filtered_terms = array();
            if(count($terms)){
                foreach ( $terms as $term )
                {
                    $metas = get_option("taxonomy_{$term->term_id}_metas");
                    if(isset($metas['season_id']) && $metas['matchday_type'] == '1'){
                        $_seasonID = $metas['season_id'];
                        $seasonPost = get_post($_seasonID);
                        if($seasonPost->ID){
                            if(in_array($_seasonID, $seasons)){
                                $std = new stdClass();
                                $std->name = esc_attr($term->name);
                                $std->id = $term->term_id;
                                if(!isset($filtered_terms[esc_attr($seasonPost->post_title)])){
                                    $filtered_terms[esc_attr($seasonPost->post_title)] = array();
                                }
                                array_push($filtered_terms[esc_attr($seasonPost->post_title)], $std);
                            }   
                        }
                    }
                }
            }
            
            $knock_settings = get_post_meta($post->ID,'_joomsport_round_knock_points',true);
            
            if(!$knock_settings){
                $knock_settings = get_option("joomsport_prediction_knockout_settings","");
            }
            
            echo '<table>';
            echo '<tr>';
            echo '<td>'.__("Knockout matchday", "joomsport-prediction").'</td>';
            echo '<td>'.JoomSportHelperSelectBox::Optgroup("jsp_knock_round", $filtered_terms,$mday, '').'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td colspan="2" style="text-align:left;font-weight:bold;padding:10px 0px;">'.__('Points', 'joomsport-prediction').'</td>';
            echo '</tr>';
            ?>
            <tr>
                <td><?php echo __('Final', 'joomsport-prediction');?></td>
                <td>
                    <input type="number" min="0" name="knscore[1]" value="<?php echo isset($knock_settings["1"])?$knock_settings["1"]:0;?>" />
                </td>
            </tr>
            <tr>
                <td><?php echo __('Semifinal', 'joomsport-prediction');?></td>
                <td>
                    <input type="number" min="0" name="knscore[2]" value="<?php echo isset($knock_settings["2"])?$knock_settings["2"]:0;?>" />
                </td>
            </tr>
            <tr>
                <td><?php echo __('Quaterfinal', 'joomsport-prediction');?></td>
                <td>
                    <input type="number" min="0" name="knscore[4]" value="<?php echo isset($knock_settings["4"])?$knock_settings["4"]:0;?>" />
                </td>
            </tr>
            <tr>
                <td>1/8</td>
                <td>
                    <input type="number" min="0" name="knscore[8]" value="<?php echo isset($knock_settings["8"])?$knock_settings["8"]:0;?>" />
                </td>
            </tr>
            <tr>
                <td>1/16</td>
                <td>
                    <input type="number" min="0" name="knscore[16]" value="<?php echo isset($knock_settings["16"])?$knock_settings["16"]:0;?>" />
                </td>
            </tr>
            <tr>
                <td>1/32</td>
                <td>
                    <input type="number" min="0" name="knscore[32]" value="<?php echo isset($knock_settings["32"])?$knock_settings["32"]:0;?>" />
                </td>
            </tr>
            <tr>
                <td>1/64</td>
                <td>
                    <input type="number" min="0" name="knscore[64]" value="<?php echo isset($knock_settings["64"])?$knock_settings["64"]:0;?>" />
                </td>
            </tr>
            <?php
            echo '</table>';
            
        }else{
            // matches
            
            $matchesInc = $wpdb->get_col("SELECT match_id FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$post->ID}");
            
            //played != 1 AND season IN, Round?
            $metaquery = array();
            $metaquery[] = 
                    array(
                        'relation' => 'AND',
                            array(
                        'key' => '_joomsport_home_team',
                        'value' => 0,
                        'compare' => '>'
                        ),

                        array(
                        'key' => '_joomsport_away_team',
                        'value' => 0,
                        'compare' => '>'
                        ),
                        array(
                        'key' => '_joomsport_match_played',
                        'value' => 1,
                        'compare' => '!='
                        ),
                        array(
                        'key' => '_joomsport_seasonid',
                        'value' => $seasons,
                        'compare' => 'IN'
                        )

                    ) ;
            $matches = new WP_Query(array(
                'post_type' => 'joomsport_match',
                'posts_per_page'   => -1,
                'orderby' => 'id',
                'order'=>'ASC',
                'meta_query' => $metaquery   
            ));

            if(count($matches->posts)){

                echo '<div class="" style="padding-bottom:5px;">';
                $seasonsStd = array();
                if(count($seasons)){
                    foreach($seasons as $season){
                        $obj = new stdClass();
                        $obj->id = $season;
                        $obj->name = get_the_title($season);
                        $seasonsStd[] = $obj;
                    }
                }
                echo __('Filters:','joomsport-prediction')."&nbsp;";
                echo JoomSportHelperSelectBox::Simple('season_id', $seasonsStd,0,' id="jspred_fltr_season_id" onchange="JSPRED_filteredMatches(0,'.$leagueid.');"',__('Select season','joomsport-prediction'));
                echo JoomSportHelperSelectBox::Simple('matchday_id', array(),0,' id="jspred_fltr_matchday_id"  onchange="JSPRED_filteredMatches(1,'.$leagueid.');"',__('Select matchday','joomsport-prediction'));

                echo '&nbsp;<input type="button" id="jsprediction_matches_selectall" class="button" value="'.__('Select All','joomsport-prediction').'">';
                echo '</div>';

                echo '<select name="round_matches[]" id="round_matches_filter" class="jswf-chosen-select" data-placeholder="'.__('Add item','joomsport-prediction').'" multiple>';
                for($intA = 0; $intA < count($matches->posts); $intA ++){
                    $match = $matches->posts[$intA];
                    $m_date = get_post_meta( $match->ID, '_joomsport_match_date', true ).' ';

                    echo '<option value="'.$match->ID.'">'.$m_date.$match->post_title.'</option>';


                }
                echo '</select>';
                echo '<input style="margin-top:5px;" type="button" id="JSPRED_participiants_ADD" class="button js-button-success" value="'.__('Add','joomsport-prediction').'">';

            }

            do_action("jsprediction_custom_filter", $seasons);

            echo '<table class="table" id="jspred_round_matches">'
            . '<tbody>';
            if(is_array($matchesInc) && count($matchesInc)){

                for($intA = 0; $intA < count($matchesInc); $intA ++){
                    echo "<tr>";
                    echo "<td>";
                    echo '<a href="javascript:void(0);" onclick="javascript:(this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode));"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                    echo "<input type='hidden' name='matches_in_round[]' value='".$matchesInc[$intA]."' /></td>";
                    echo "<td>".get_the_title($matchesInc[$intA])."</td>";
                    echo "</tr>";
                }

            }
            echo '</tbody>';
            echo '</table>';
            echo '<div id="modalAj"><!-- Place at bottom of page --></div>';
        }
        
        
    }


    public static function jswprediction_round_save_metabox($post_id, $post){
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['jswprediction_round_nonce'] ) ? $_POST['jswprediction_round_nonce'] : '';
        $nonce_action = 'jswprediction_round_savemetaboxes';
 
        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }
 
        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
 
        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
 
        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
 
        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        if('jswprediction_round' == $_POST['post_type'] ){
            self::saveMetaLeague($post_id);
            self::saveMetaMatches($post_id);
        }
    }
    private static function saveMetaLeague($post_id){
        $meta_array = array();
        $meta_array = isset($_POST['leagueid'])?  intval($_POST['leagueid']):0;
        if($meta_array){
            update_post_meta($post_id, '_joomsport_round_leagueid', $meta_array);
        }
        $meta_array = array();
        $meta_array = isset($_POST['roundtype'])?  intval($_POST['roundtype']):0;
        if($meta_array){
            update_post_meta($post_id, '_joomsport_round_roundtype', $meta_array);
        }
    }
    private static function saveMetaMatches($post_id){
        global $wpdb;
        
        $roundtype = get_post_meta($post_id, '_joomsport_round_roundtype', true);
        
        if($roundtype == ''){
            $roundtype = isset($_REQUEST['roundtype'])?  intval($_REQUEST['roundtype']):0;
        }
        
        if($roundtype == '1'){
            //knockout tree
            $knscore = isset($_POST['knscore'])?$_POST['knscore']:array();
            
            update_post_meta($post_id, '_joomsport_round_knock_points', $knscore);
            $jsp_knock_round = isset($_REQUEST['jsp_knock_round'])?  intval($_REQUEST['jsp_knock_round']):0;
            update_post_meta($post_id, '_joomsport_round_knock_mday', $jsp_knock_round);
        }else{
            $matchesIn = array();
            $mathesin_round = isset($_POST['matches_in_round'])?$_POST['matches_in_round']:array();
            if(count($mathesin_round)){
                $matches_id = array_map( 'intval', $mathesin_round );
            }else{
                $matches_id = array();
            }
            for($intA=0;$intA<count($matches_id);$intA++){
                $match = $matches_id[$intA];
                if($match){
                    $is_exist = $wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$post_id} AND match_id={$match}");
                    if(!$is_exist){
                        $wpdb->insert($wpdb->jswprediction_round_matches,array("round_id"=>$post_id,"match_id"=>$match),array("%d","%d"));
                    }
                    $matchesIn[] = $match;
                }

            }
            if(count($matchesIn)){
                $ids = implode( ',', array_map( 'absint', $matchesIn ) );
                $wpdb->query( "DELETE FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$post_id} AND match_id NOT IN($ids)" );

            }else{
                $wpdb->query( "DELETE FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$post_id}" );

            }

            //set start date
            $matches = $wpdb->get_col("SELECT match_id "
                . " FROM {$wpdb->jswprediction_round_matches}"
                . " WHERE round_id={$post_id}");

            $match_date = '';

            for($intA=0;$intA<count($matches);$intA++){
                $m_date = get_post_meta( $matches[$intA], '_joomsport_match_date', true );
                $m_time = get_post_meta( $matches[$intA], '_joomsport_match_time', true );
                if($m_date){
                    if(!$match_date || $match_date > $m_date.' '.$m_time){
                        $match_date = $m_date.' '.$m_time;
                    }
                }
            }

            update_post_meta($post_id, '_joomsport_round_start_match', $match_date);
        }    
    }
    
}