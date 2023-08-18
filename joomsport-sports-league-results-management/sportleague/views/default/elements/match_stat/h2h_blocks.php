<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
/*<!--jsonlyinproPHP-->*/
require_once JOOMSPORT_PATH_SL_HELPERS . "js-helper-btw.php";
if(jsHelperBtw::showH2HBlock($rows->object->ID)) {

    $rows->h2h();

// League Analytics

    $optionsHome = jsHelperBtw::getColumnsOptions($rows->season_id, $partic_home->object->ID);

    $optionsAway = jsHelperBtw::getColumnsOptions($rows->season_id, $partic_away->object->ID);
    $partic_home_short = $partic_home->getName(false, 0, 2);
    $partic_away_short = $partic_away->getName(false, 0, 2);
    $partic_home_middle = $partic_home->getName(false, 0, 1);
    $partic_away_middle = $partic_away->getName(false, 0, 1);
    $matchesPlHome = jsHelperBtw::getPlayedMatches($partic_home->object->ID, $rows->season_id);
    $matchesPlAway = jsHelperBtw::getPlayedMatches($partic_away->object->ID, $rows->season_id);

    echo '<div class="jsHHMatchDiv table-responsive">';

    if (count($matchesPlHome) || count($matchesPlAway)) {
        require 'season_analytics.php';
    }

    $matchesPlHome = jsHelperBtw::getPlayedMatches($partic_home->object->ID, $rows->season_id);
    $matchesPlAway = jsHelperBtw::getPlayedMatches($partic_away->object->ID, $rows->season_id);

    if (count($matchesPlHome) || count($matchesPlAway)) {
        require 'averages_analytics.php';
    }

    if (count($rows->lists["btwMatchesAll"])) {
        require 'h2h_analytics.php';
    }
    echo '</div>';
}
/*<!--/jsonlyinproPHP-->*/

