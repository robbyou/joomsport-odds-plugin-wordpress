<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
/*<!--jsonlyinproPHP-->*/

        echo '<div class="jsHHSeasonAnalytics clearfix">';
        echo '<div class="jsMatchStatHeader jspBlockTitle jscenter"><h3>' . __('Season Analytics', 'joomsport-sports-league-results-management') . '</h3><i class="fa fa-chevron-up" aria-hidden="true"></i></div>';
        echo '<div class="jspBlockSection col-xs-12">';
        ?>
        <div class="jsMatchStatTeams visible-xs clearfix">
        	<div class="row">
        		<?php
        		if(jsHelperBtw::showPositionBlock($rows->object->ID)) {
        			$teamBtnClass = 'col-xs-4';
        			$positionBtnClass = 'col-xs-4';
        		} else {
        			$teamBtnClass = 'col-xs-6';
        			$positionBtnClass = 'jsHHide';
        		}
        		?>
        		
        		<div class="<?php echo esc_attr($teamBtnClass); ?>">
        			<div class="jstable jsMatchTeam jsMatchStatHome jsactive" data-tab="jsHHMatchHome">
        				<div class="jstable-cell jsMatchTeamLogo">
        					<?php echo $partic_home ? wp_kses_post($partic_home->getEmblem(false, 0, '', $width)) : ''; ?>
        				</div>
        				<div class="jstable-cell jsMatchTeamName">
        					<div>
        						<span>
        							<?php echo ($partic_home) ? wp_kses_post($partic_home->getName(false)) : ''; ?>
        						</span>
        					</div>
        				</div>
        			</div>
        		</div>
        		<div class="<?php echo esc_attr($positionBtnClass); ?>">
        			<div class="jsMatchTeam jsMatchTeamPos row" data-tab="jsHHMatchPos">
        				<div>
        					<?php echo __('Positions', 'joomsport-sports-league-results-management'); ?>
        				</div>
        			</div>
        		</div>
        		<div class="<?php echo esc_attr($teamBtnClass); ?>">
        			<div class="jstable jsMatchTeam jsMatchStatAway" data-tab="jsHHMatchAway">
        				<div class="jstable-cell jsMatchTeamName">
        					<div>
        						<span>
        							<?php echo ($partic_away) ? wp_kses_post($partic_away->getName(false)) : ''; ?>
        						</span>
        					</div>
        				</div>
        				<div class="jstable-cell jsMatchTeamLogo">
        					<?php echo $partic_away ? wp_kses_post($partic_away->getEmblem(false, 0, 'emblInline', $width)) : ''; ?>
        				</div>
        			</div>
        		</div>
        	</div>
        </div>
        <?php
        echo '<div class="jsHomeTeamAnalytics jsTeamAnalytics jsactive col-xs-12 col-sm-5" data-tab="jsHHMatchHome">';
            echo '<div class="centrikLDW jscenter">';
                echo '<div class="centrikLDWinner">';
                    echo '<div class="centrikLDWinnerTitle">';
                        echo '<div class="divTabfade"><a href="javascript:void(0);" class="jsTabActive" onclick="jspTabs(this, 2);">' . __('SEASON TOTAL', 'joomsport-sports-league-results-management') . '</a></div>';
                        echo '<div class="divTabfade"><a href="javascript:void(0);" class="" onclick="jspTabs(this, 1);">' . esc_html($partic_home_short) . ' ' . __('HOME', 'joomsport-sports-league-results-management') . '</a></div>';
                    echo '</div>';
                    echo '<div class="centrikLDWinnerContainer" id="centrikLDWinnerContainer1"  style="display: none;">';
                        echo '<div class="divCntWLD winWLD">' . esc_html($optionsHome["winhome_chk"]) . '</div>';
                        echo '<div class="divCntWLD drawWLD">' . esc_html($optionsHome["drawhome_chk"]) . '</div>';
                        echo '<div class="divCntWLD lostWLD">' . esc_html($optionsHome["losthome_chk"]) . '</div>';
                    echo '</div>';
                    echo '<div class="centrikLDWinnerContainer" id="centrikLDWinnerContainer2">';
                        echo '<div class="divCntWLD winWLD">' . esc_html($optionsHome["winaway_chk"] + $optionsHome["winhome_chk"]) . '</div>';
                        echo '<div class="divCntWLD drawWLD">' . esc_html($optionsHome["drawaway_chk"] + $optionsHome["drawhome_chk"]) . '</div>';
                        echo '<div class="divCntWLD lostWLD">' . esc_html($optionsHome["lostaway_chk"] + $optionsHome["losthome_chk"]) . '</div>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';

            echo '<div class="jspBlockTitleSmall jscenter"><h4>' . __('Last matches', 'joomsport-sports-league-results-management') . '</h4></div>';

            echo '<table class="table divLastMatches divLastMatches2">';
            $lastH = $rows->getLastMatchesH();

            for ($intA = 0; $intA < count($lastH); $intA++) {
                $lMatch = $lastH[$intA];
                $m_date = get_post_meta($lMatch->id, '_joomsport_match_date', true);
                $m_time = get_post_meta($lMatch->id, '_joomsport_match_time', true);

                if ($lMatch->opposite == 1) {
                    $LMpartic = $lMatch->getParticipantAway();
                } else {
                    $LMpartic = $lMatch->getParticipantHome();
                }

                $match_date = classJsportDate::getDate($m_date, '');
                echo '<tr>';
                require 'last_matches.php';
                echo '</tr>';
            }
            echo '</table>';
            echo '<table class="table divLastMatches divLastMatches1" style="display:none;">';
            $lastH = $rows->getLastMatchesH(1);

            for ($intA = 0; $intA < count($lastH); $intA++) {
                $lMatch = $lastH[$intA];
                $m_date = get_post_meta($lMatch->id, '_joomsport_match_date', true);
                $m_time = get_post_meta($lMatch->id, '_joomsport_match_time', true);

                if ($lMatch->opposite == 1) {
                    $LMpartic = $lMatch->getParticipantAway();
                } else {
                    $LMpartic = $lMatch->getParticipantHome();
                }

                $match_date = classJsportDate::getDate($m_date, '');
                echo '<tr>';
                require 'last_matches.php';
                echo '</tr>';
            }
            echo '</table>';
        echo '</div>';

        $position = jsHelperBtw::getPositions($rows->season_id, $partic_home->object->ID, $partic_away->object->ID);
        $colors = jsHelperBtw::getStandingColors($rows->season_id);

        echo '<div class="jsTeamPosAnalytics col-xs-12 col-sm-2" data-tab="jsHHMatchPos">';
        if(jsHelperBtw::showPositionBlock($rows->object->ID)) {
            echo '<div class="divLeaguePos">';
            echo '<div class="divLeaguePosHT" style="top:' . (intval($position->teamHomePosition * 15) - 15) . 'px;">' . $position->teamHomePosition . '</div>';
            echo '<div class="divLeaguePosAT" style="top:' . (intval($position->teamAwayPosition * 15) - 15) . 'px;">' . $position->teamAwayPosition . '</div>';

            for ($i = 0; $i < $position->maxPosition; $i++) {
                echo '<div class="posDivelContainer">';
                    echo '<div class="posDivelLeft" ' . (isset($colors[$i + 1]) && $colors[$i + 1] ? ' style="background-color:' . $colors[$i + 1] . '"' : "") . '></div>';
                    echo '<div class="posDivel"></div>';
                // echo '<div class="posDivelRight"></div>';
                echo '</div>';
            }

            echo '</div>';
            echo '<div class="jscenter">' . __('Position', 'joomsport-sports-league-results-management') . '</div>';
        }
        echo '</div>';
        echo '<div class="jsAwayTeamAnalytics jsTeamAnalytics col-xs-12 col-sm-5" data-tab="jsHHMatchAway">';
        echo '<div class="centrikLDW jscenter">';
            echo '<div class="centrikLDWinner">';
                echo '<div class="centrikLDWinnerTitle">';
                    echo '<div class="divTabfade"><a href="javascript:void(0);" class="jsTabActive" onclick="jspTabs(this, 4);">' . __('SEASON TOTAL', 'joomsport-sports-league-results-management') . '</a></div>';
                    echo '<div class="divTabfade"><a href="javascript:void(0);" class="" onclick="jspTabs(this, 3);">' . esc_html($partic_away_short) . ' ' . __('AWAY', 'joomsport-sports-league-results-management') . '</a></div>';
                echo '</div>';
                echo '<div class="centrikLDWinnerContainer" id="centrikLDWinnerContainer3"  style="display: none;">';
                    echo '<div class="divCntWLD winWLD">' . esc_html($optionsAway["winaway_chk"]) . '</div>';
                    echo '<div class="divCntWLD drawWLD">' . esc_html($optionsAway["drawaway_chk"]) . '</div>';
                    echo '<div class="divCntWLD lostWLD">' . esc_html($optionsAway["lostaway_chk"]) . '</div>';
                echo '</div>';
                echo '<div class="centrikLDWinnerContainer" id="centrikLDWinnerContainer4">';
                    echo '<div class="divCntWLD winWLD">' . esc_html($optionsAway["winaway_chk"] + $optionsAway["winhome_chk"]) . '</div>';
                    echo '<div class="divCntWLD drawWLD">' . esc_html($optionsAway["drawaway_chk"] + $optionsAway["drawhome_chk"]) . '</div>';
                    echo '<div class="divCntWLD lostWLD">' . esc_html($optionsAway["lostaway_chk"] + $optionsAway["losthome_chk"]) . '</div>';
                echo '</div>';
            echo '</div>';
        echo '</div>';

        echo '<div class="jspBlockTitleSmall jscenter"><h4>' . __('Last matches', 'joomsport-sports-league-results-management') . '</h4></div>';

        echo '<table class="table divLastMatches divLastMatches4">';
        $lastA = $rows->getLastMatchesA();

        for ($intA = 0; $intA < count($lastA); $intA++) {
            $lMatch = $lastA[$intA];
            $m_date = get_post_meta($lMatch->id, '_joomsport_match_date', true);
            $m_time = get_post_meta($lMatch->id, '_joomsport_match_time', true);

            if ($lMatch->opposite == 1) {
                $LMpartic = $lMatch->getParticipantAway();
            } else {
                $LMpartic = $lMatch->getParticipantHome();
            }

            $match_date = classJsportDate::getDate($m_date, '');
            echo '<tr>';
            require 'last_matches_reverse.php';
            echo '</tr>';
        }
        echo '</table>';

        $lastA = $rows->getLastMatchesA(2);
        echo '<table class="table divLastMatches divLastMatches3" style="display: none;">';

        for ($intA = 0; $intA < count($lastA); $intA++) {
            $lMatch = $lastA[$intA];
            $m_date = get_post_meta($lMatch->id, '_joomsport_match_date', true);
            $m_time = get_post_meta($lMatch->id, '_joomsport_match_time', true);
            if ($lMatch->opposite == 1) {
                $LMpartic = $lMatch->getParticipantAway();
            } else {
                $LMpartic = $lMatch->getParticipantHome();
            }

            $match_date = classJsportDate::getDate($m_date, '');
            echo '<tr>';
            require 'last_matches_reverse.php';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';


/*<!--/jsonlyinproPHP-->*/

