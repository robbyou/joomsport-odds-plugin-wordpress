<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
/*<!--jsonlyinproPHP-->*/



        $btwWin = $btwDraw = $btwLost = 0;

        for ($intA = 0; $intA < count($rows->lists["btwMatchesAll"]); $intA++) {
            $mtch = $rows->lists["btwMatchesAll"][$intA];
            $home_score = get_post_meta($mtch->ID, '_joomsport_home_score', true);
            $away_score = get_post_meta($mtch->ID, '_joomsport_away_score', true);
            $home_team = get_post_meta($mtch->ID, '_joomsport_home_team', true);
            $away_team = get_post_meta($mtch->ID, '_joomsport_away_team', true);

            if ($home_score != '' && $home_score == $away_score) {
                $btwDraw++;
            } elseif($home_score != '') {

                if (($home_team == $partic_home->object->ID && $home_score > $away_score)
                    || ($away_team == $partic_home->object->ID && $home_score < $away_score)) {
                    $btwWin++;
                } else {
                    $btwLost++;
                }
            }
        }

        $ovr = $btwWin + $btwDraw + $btwLost;

        if($ovr){
            $homeWin = round(($btwWin*360) / $ovr);
            $awayWin = round(($btwLost*360) / $ovr);
        }else{
            $homeWinLocal = $awayWinLocal = 0;
        }

        //h/a
        $btwWinLocal = $btwDrawLocal = $btwLostLocal = 0;
        for ($intA = 0; $intA < count($rows->lists["btwMatchesLocal"]); $intA++) {
            $mtch = $rows->lists["btwMatchesLocal"][$intA];
            $home_score = get_post_meta($mtch->ID, '_joomsport_home_score', true);
            $away_score = get_post_meta($mtch->ID, '_joomsport_away_score', true);
            $home_team = get_post_meta($mtch->ID, '_joomsport_home_team', true);
            $away_team = get_post_meta($mtch->ID, '_joomsport_away_team', true);

            if ($home_score != '' && $home_score == $away_score) {
                $btwDrawLocal++;
            } elseif($home_score != '') {
                if (($home_team == $partic_home->object->ID && $home_score > $away_score)
                    || ($away_team == $partic_home->object->ID && $home_score < $away_score)) {
                    $btwWinLocal++;
                } else {
                    $btwLostLocal++;
                }
            }
        }

        $ovr2 = $btwWinLocal + $btwDrawLocal + $btwLostLocal;

        if($ovr2){
            $homeWinLocal = round(($btwWinLocal*360) / $ovr2);
            $awayWinLocal = round(($btwLostLocal*360) / $ovr2);
        }else{
            $homeWinLocal = $btwWinLocal?360:0;
            $awayWinLocal = $btwLostLocal?360:0;
        }

        echo '<div class="jsHHAnalytics clearfix">';
        echo '<div class="jsMatchStatHeader jspBlockTitle jscenter"><h3>' . __('Head to Head Analytics', 'joomsport-sports-league-results-management') . '</h3><i class="fa fa-chevron-up" aria-hidden="true"></i></div>';
        echo '<div class="jspBlockSection clearfix">';


?>
        <script>
            jQuery(document).ready(function() {
                jspDrowPie('jspCircle1', <?php echo esc_js($awayWin)?>, <?php echo esc_js($homeWin)?>);
            });
            jQuery(document).ready(function() {
                jspDrowPie('jspCircle2', <?php echo esc_js($awayWinLocal)?>, <?php echo esc_js($homeWinLocal)?>);
            });
        </script>

        <?php
        echo '<div class="centrikLDW jscenter">';
        echo '<div class="centrikLDWinnerTitle">';
        echo '<div class="divTabfade"><a href="javascript:void(0);" class="jsTabActive" onclick="jspTabsMajor(this, 3);">' . __('TOTAL', 'joomsport-sports-league-results-management') . '</a></div>';
        echo '<div class="divTabfade"><a href="javascript:void(0);" class="" onclick="jspTabsMajor(this, 4);">' . esc_html($partic_home_short) . ' '. __('HOME', 'joomsport-sports-league-results-management') . ' / ' . esc_html($partic_away_short) . ' '. __('AWAY', 'joomsport-sports-league-results-management') . '</a></div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="jsHHBlock col-xs-12">';
        echo '<div class="col-xs-12 col-sm-6 jsHHPercentage">';
        echo '<table class="evTblforTabs evTbl3">';
        echo '<tr>';
        echo '<td>';

        if($ovr) {
            echo '<div class="circleHmWinText"><div><span class="jsTeamName">' . esc_html($partic_home_middle) . '</span></div><span>' . __("Wins", "joomsport-sports-league-results-management") . ' ' . esc_html($btwWin) . '</span></div>';
            echo '<div class=\'circle\' id="jspCircle1">';
            echo '<div class="circleInnerDraw">' . esc_html($btwDraw) . ' ' . __("Draws", "joomsport-sports-league-results-management").'</div>';
            echo '</div>';
            echo '<div class="circleHmWinText"><div><span class="jsTeamName">' . esc_html($partic_away_middle) . '</span></div><span>' . __("Wins", "joomsport-sports-league-results-management") . ' ' . esc_html($btwLost) . '</span></div>';
        }

        echo '</td>';
        echo '</tr>';
        echo '</table>';

        echo '<table class="evTblforTabs evTbl4" style="display: none;">';
        echo '<tr>';
        echo '<td>';

        if($ovr2) {
            echo '<div class="circleHmWinText"><div><span class="jsTeamName">' . esc_html($partic_home_middle) . '</span></div><span>' . __("Wins", "joomsport-sports-league-results-management") . ' ' . esc_html($btwWinLocal) . '</span></div>';
            echo '<div class=\'circle\' id="jspCircle2">';
            echo '<div class="circleInnerDraw">' . $btwDrawLocal. ' ' . __("Draws", "joomsport-sports-league-results-management").'</div>';
            echo '</div>';
            echo '<div class="circleHmWinText"><div><span class="jsTeamName">' . esc_html($partic_away_middle) . '</span></div><span>' . __("Wins", "joomsport-sports-league-results-management") . ' ' . esc_html($btwLostLocal) . '</span></div>';
        }

        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-6 jsHHMatches">';
        echo '<table class="table evTblforTabs evTbl3">';

        $cnt = count($rows->lists["btwMatchesAll"]) > JSCONF_H2H_MAX_MATCH ? JSCONF_H2H_MAX_MATCH : count($rows->lists["btwMatchesAll"]);
        $prev_seasonid = 0;

        for ($i = 0; $i < $cnt; $i++) {
            $match = new classJsportMatch($rows->lists["btwMatchesAll"][$i]->ID, false);
            $matchOBJ = $match->getRowSimple();
            $m_date = get_post_meta($rows->lists["btwMatchesAll"][$i]->ID, '_joomsport_match_date', true);
            $m_time = get_post_meta($rows->lists["btwMatchesAll"][$i]->ID, '_joomsport_match_time', true);
            $match_date = classJsportDate::getDate($m_date, '');
            $btwParticHome = $matchOBJ->getParticipantHome();
            $btwParticAway = $matchOBJ->getParticipantAway();
            $season_id = JoomSportHelperObjects::getMatchSeason($rows->lists["btwMatchesAll"][$i]->ID);
            $name = '';
            $term_list = wp_get_post_terms($season_id, 'joomsport_tournament', array("fields" => "all"));

            if (count($term_list)) {
                $name .= esc_attr($term_list[0]->name) . ' ';
            }

            $name .= get_the_title($season_id);

            if ($prev_seasonid != $season_id) {
                echo '<tr class="jsSeasonName"><td colspan="4" class="jscenter">' . esc_html($name) . '</td></tr>';
                $prev_seasonid = $season_id;
            }
            ?>
            <tr>
                <td class="jsMatchDate">
                    <?php echo esc_html($match_date); ?>
                </td>
                <td class="jsMatchTeam jsHomeTeam">
                    <?php
                    if (is_object($btwParticHome)) {
                        echo($btwParticHome->getEmblem());
                    }

                    if (is_object($btwParticHome)) {
                        echo '<div class="jsMatchTeamName">';
                        echo jsHelper::nameHTML($btwParticHome->getName(true));
                        echo '</div>';
                    }
                    ?>
                </td>
                <td class="jsMatchPlayedScore">
                    <?php echo jsHelper::getScore($matchOBJ, ''); ?>
                </td>
                <td class="jsMatchTeam jsAwayTeam">
                    <?php
                    if (is_object($btwParticAway)) {
                        echo($btwParticAway->getEmblem());
                    }

                    if (is_object($btwParticAway)) {
                        echo '<div class="jsMatchTeamName">';
                        echo jsHelper::nameHTML($btwParticAway->getName(true));
                        echo '</div>';
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        echo '</table>';

        echo '<table class="table evTblforTabs evTbl4" style="display: none;">';
        $cnt = count($rows->lists["btwMatchesLocal"]) > JSCONF_H2H_MAX_MATCH ? JSCONF_H2H_MAX_MATCH : count($rows->lists["btwMatchesLocal"]);
        $prev_seasonid = 0;

        for ($i = 0; $i < $cnt; $i++) {
            $match = new classJsportMatch($rows->lists["btwMatchesLocal"][$i]->ID, false);
            $matchOBJ = $match->getRowSimple();
            $m_date = get_post_meta($rows->lists["btwMatchesLocal"][$i]->ID, '_joomsport_match_date', true);
            $m_time = get_post_meta($rows->lists["btwMatchesLocal"][$i]->ID, '_joomsport_match_time', true);
            $match_date = classJsportDate::getDate($m_date, '');
            $btwParticHome = $matchOBJ->getParticipantHome();
            $btwParticAway = $matchOBJ->getParticipantAway();
            $season_id = JoomSportHelperObjects::getMatchSeason($rows->lists["btwMatchesAll"][$i]->ID);
            $name = '';
            $term_list = wp_get_post_terms($season_id, 'joomsport_tournament', array("fields" => "all"));

            if (count($term_list)) {
                $name .= esc_attr($term_list[0]->name) . ' ';
            }
            $name .= get_the_title($season_id);

            if ($prev_seasonid != $season_id) {
                echo '<tr class="jsSeasonName"><td colspan="4" class="jscenter">' . esc_html($name) . '</td></tr>';
                $prev_seasonid = $season_id;
            }
            ?>
            <tr>
                <td class="jsMatchDate">
                    <?php echo esc_html($match_date); ?>
                </td>
                <td class="jsMatchTeam jsHomeTeam">
                    <?php
                    if (is_object($btwParticHome)) {
                        echo($btwParticHome->getEmblem());
                    }

                    if (is_object($btwParticHome)) {
                        echo '<div class="jsMatchTeamName">';
                        echo jsHelper::nameHTML($btwParticHome->getName(true));
                        echo '</div>';
                    }
                    ?>
                </td>
                <td class="jsMatchPlayedScore">
                    <?php echo jsHelper::getScore($matchOBJ, ''); ?>
                </td>
                <td class="jsMatchTeam jsAwayTeam">
                    <?php
                    if (is_object($btwParticAway)) {
                        echo($btwParticAway->getEmblem());
                    }

                    if (is_object($btwParticAway)) {
                        echo '<div class="jsMatchTeamName">';
                        echo jsHelper::nameHTML($btwParticAway->getName(true));
                        echo '</div>';
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';


/*<!--/jsonlyinproPHP-->*/

