<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
/*<!--jsonlyinproPHP-->*/
        echo '<div class="jsHHAvrgAnalytics clearfix">';
        echo '<div class="jsMatchStatHeader jspBlockTitle jscenter"><h3>' . __('Averages Analytics', 'joomsport-sports-league-results-management') . '</h3><i class="fa fa-chevron-up" aria-hidden="true"></i></div>';
        echo '<div class="clearfix">';
        echo '<div class="centrikLDW jscenter">';
        echo '<div class="centrikLDWinnerTitle">';
        echo '<div class="divTabfade"><a href="javascript:void(0);" class="jsTabActive" onclick="jspTabsMajor(this, 1);">' . __('SEASON TOTAL', 'joomsport-sports-league-results-management') . '</a></div>';
        echo '<div class="divTabfade"><a href="javascript:void(0);" class="" onclick="jspTabsMajor(this, 2);">' . esc_html($partic_home_short) . ' '. __('HOME', 'joomsport-sports-league-results-management') . ' / ' . esc_html($partic_away_short) . ' '. __('AWAY', 'joomsport-sports-league-results-management') . '</a></div>';
        echo '</div>';
        echo '</div>';


        $matchesIDSHome = jsHelper::getPostsAsArray($matchesPlHome);
        $evsHome = jsHelperBtw::getTeamStat($partic_home->object->ID, $rows->season_id, $matchesIDSHome);
        $mevsHome = jsHelperBtw::getTeamMatchStat(1, $rows->season_id, $matchesIDSHome);


        $matchesIDSAway = jsHelper::getPostsAsArray($matchesPlAway);
        $evsAway = jsHelperBtw::getTeamStat($partic_away->object->ID, $rows->season_id, $matchesIDSAway);
        $mevsAway = jsHelperBtw::getTeamMatchStat(1, $rows->season_id, $matchesIDSAway);


        $matchesPlHomePlace = jsHelperBtw::getPlayedMatches($partic_home->object->ID, $rows->season_id, 1);
        $matchesIDSHomePlace = jsHelper::getPostsAsArray($matchesPlHomePlace);
        $evsHomePlace = jsHelperBtw::getTeamStat($partic_home->object->ID, $rows->season_id, $matchesIDSHome);
        $mevsHomePlace = jsHelperBtw::getTeamMatchStat(1, $rows->season_id, $matchesIDSHomePlace);

        $matchesPlAwayPlace = jsHelperBtw::getPlayedMatches($partic_away->object->ID, $rows->season_id, 2);
        $matchesIDSAwayPlace = jsHelper::getPostsAsArray($matchesPlAwayPlace);
        $evsAwayPlace = jsHelperBtw::getTeamStat($partic_away->object->ID, $rows->season_id, $matchesIDSAwayPlace);
        $mevsAwayPlace = jsHelperBtw::getTeamMatchStat(1, $rows->season_id, $matchesIDSAwayPlace);

        $avgevents_events = JoomsportSettings::get('avgevents_events', array());

        if ($avgevents_events) {
            $avgevents_events = json_decode($avgevents_events, true);
            $avgevents_events = jsHelperBtw::sortEvents($avgevents_events);
        }

        echo '<div class="jsAnalyticBlock col-xs-12">';
        echo '<div class="jspBlockSection clearfix">';
        echo '<div class="jsEventsAnalytic col-xs-6 col-sm-5">';
        echo '<table class="table evTblforTabs evTbl1">';

        if (count($avgevents_events)) {
            foreach ($avgevents_events as $ev) {
                echo '<tr>';
                $objEvent = new classJsportEvent($ev);

                if (isset($mevsHome[$ev]) || isset($mevsAway[$ev])) {
                    echo '<td>' . (isset($mevsHome[$ev])?round($mevsHome[$ev] / count($matchesIDSHome), 1):"0") . '</td>';
                    echo '<td class="jsEventType">' . $objEvent->getEmblem(false) . '</td>';
                    echo '<td>' . (isset($mevsAway[$ev])?round($mevsAway[$ev] / count($matchesIDSAway), 1):"0") . '</td>';
                } elseif (isset($evsHome[$ev]) || isset($evsAway[$ev])) {

                    echo '<td>' . (isset($evsHome[$ev])?round($evsHome[$ev]->cnt / count($matchesIDSHome), 1):"0"). '</td>';
                    echo '<td class="jsEventType">' . $objEvent->getEmblem(false) . '</td>';
                    echo '<td>' . (isset($evsAway[$ev])?round($evsAway[$ev]->cnt / count($matchesIDSAway), 1):"0") . '</td>';
                }

                echo '</tr>';
            }
        }
        echo '</table>';
        echo '<table class="table evTblforTabs evTbl2" style="display:none;">';

        if (count($avgevents_events)) {
            foreach ($avgevents_events as $ev) {
                echo '<tr>';
                $objEvent = new classJsportEvent($ev);

                if (isset($mevsHomePlace[$ev]) && isset($mevsAwayPlace[$ev])) {
                    echo '<td>' . (isset($mevsHomePlace[$ev])?round($mevsHomePlace[$ev] / count($matchesIDSHomePlace), 1):"0") . '</td>';
                    echo '<td class="jsEventType">' . $objEvent->getEmblem(false) . '</td>';
                    echo '<td>' . (isset($mevsAwayPlace[$ev])?round($mevsAwayPlace[$ev] / count($matchesIDSAwayPlace), 1):"0") . '</td>';
                } elseif (isset($evsHomePlace[$ev]) && isset($evsAwayPlace[$ev])) {

                    echo '<td>' . (isset($evsHomePlace[$ev])?round($evsHomePlace[$ev]->cnt / count($matchesIDSHomePlace), 1):"0") . '</td>';
                    echo '<td class="jsEventType">' . $objEvent->getEmblem(false) . '</td>';
                    echo '<td>' . (isset($evsAwayPlace[$ev])?round($evsAwayPlace[$ev]->cnt / count($matchesIDSAwayPlace), 1):"0") . '</td>';
                }

                echo '</tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        $hmGoals = jsHelperBtw::getTeamGoals($partic_home->object->ID, $matchesIDSHome);
        $awGoals = jsHelperBtw::getTeamGoals($partic_away->object->ID, $matchesIDSAway);
        $hmGoalsPlace = jsHelperBtw::getTeamGoals($partic_home->object->ID, $matchesIDSHomePlace);
        $awGoalsPlace = jsHelperBtw::getTeamGoals($partic_away->object->ID, $matchesIDSAwayPlace);

        echo '<div class="jsGoalsAnalytic col-xs-6 col-sm-5 col-sm-offset-2">';
            echo '<table class="evTblforTabs evTbl1">';
                $scoredH = round($hmGoals["scored"] / count($matchesIDSHome), 1);
                $concH = round($hmGoals["conceeded"] / count($matchesIDSHome), 1);
                $scoredA = round($awGoals["scored"] / count($matchesIDSAway), 1);
                $concA = round($awGoals["conceeded"] / count($matchesIDSAway), 1);
                $homeTeamSum = $scoredH + $concH;
                $awayTeamSum = $scoredA + $concA;
                $maxVal = $homeTeamSum > $awayTeamSum?$homeTeamSum:$awayTeamSum;
                $pxGraphStep = $maxVal ? round(100 / $maxVal) : 0;

                echo '<tr>';
                    echo '<td class="jsVertHead"><span>' . __("Goals Scored", "joomsport-sports-league-results-management") . '</span></td>';
                    echo '<td class="jsHomeScoreAnalytic tdValignBottom">' . esc_html($scoredH) . '<div style="height:'.round($pxGraphStep*$scoredH).'px;" class="avgGoalBar"></div></td>';
                    echo '<td class="jsAwayScoreAnalytic tdValignBottom">' . esc_html($scoredA) . '<div style="height:'.round($pxGraphStep*$scoredA).'px;" class="avgGoalBar"></div></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td class="jsVertHead"><span>' . __("Goals Conceded", "joomsport-sports-league-results-management") . '</span></td>';
                    echo '<td class="jsHomeScoreAnalytic tdValignTop"><div style="height:'.round($pxGraphStep*$concH).'px;" class="avgGoalBar"></div>' . esc_html($concH) . '</td>';
                    echo '<td class="jsAwayScoreAnalytic tdValignTop"><div style="height:'.round($pxGraphStep*$concA).'px;" class="avgGoalBar"></div>' . esc_html($concA) . '</td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td class="tdAvgTotal">' . __("TOTAL", "joomsport-sports-league-results-management") . '</td>';
                    echo '<td class="tdAvgTotal">'.esc_html($homeTeamSum).'</td>';
                    echo '<td class="tdAvgTotal">'.esc_html($awayTeamSum).'</td>';
                echo '</tr>';
            echo '</table>';
            echo '<table class="evTblforTabs evTbl2" style="display: none;">';
$scoredH = count($matchesIDSHomePlace)?round($hmGoalsPlace["scored"] / count($matchesIDSHomePlace), 1):0;
$concH = count($matchesIDSHomePlace)?round($hmGoalsPlace["conceeded"] / count($matchesIDSHomePlace), 1):0;
$scoredA = count($matchesIDSAwayPlace)?round($awGoalsPlace["scored"] / count($matchesIDSAwayPlace), 1):0;
$concA = count($matchesIDSAwayPlace)?round($awGoalsPlace["conceeded"] / count($matchesIDSAwayPlace), 1):0;
                $homeTeamSum = $scoredH + $concH;
                $awayTeamSum = $scoredA + $concA;
                $maxVal = $homeTeamSum > $awayTeamSum?$homeTeamSum:$awayTeamSum;
                $pxGraphStep = $maxVal ? round(100 / $maxVal) : 0;

                echo '<tr>';
                    echo '<td class="jsVertHead"><span>' . __("Goals Scored", "joomsport-sports-league-results-management") . '</span></td>';
                    echo '<td class="jsHomeScoreAnalytic tdValignBottom">' . esc_html($scoredH) . '<div style="height:'.round($pxGraphStep*$scoredH).'px;" class="avgGoalBar"></div></td>';
                    echo '<td class="jsAwayScoreAnalytic tdValignBottom">' . esc_html($scoredA) . '<div style="height:'.round($pxGraphStep*$scoredA).'px;" class="avgGoalBar"></div></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td class="jsVertHead"><span>' . __("Goals Conceded", "joomsport-sports-league-results-management") . '</span></td>';
                    echo '<td class="jsHomeScoreAnalytic tdValignTop"><div style="height:'.round($pxGraphStep*$concH).'px;" class="avgGoalBar"></div>' . esc_html($concH) . '</td>';
                    echo '<td class="jsAwayScoreAnalytic tdValignTop"><div style="height:'.round($pxGraphStep*$concA).'px;" class="avgGoalBar"></div>' . esc_html($concA) . '</td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td class="tdAvgTotal">' . __("TOTAL", "joomsport-sports-league-results-management") . '</td>';
                    echo '<td class="tdAvgTotal">'.esc_html($homeTeamSum).'</td>';
                    echo '<td class="tdAvgTotal">'.esc_html($awayTeamSum).'</td>';
                echo '</tr>';
            echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

/*<!--/jsonlyinproPHP-->*/

