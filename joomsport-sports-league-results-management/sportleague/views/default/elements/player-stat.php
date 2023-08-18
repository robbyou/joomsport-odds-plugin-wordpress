<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$stdoptions = '';
/*<!--jsstdOptionsPHP-->*/
if($stdoptions == 'std'){
?>
<div class="table-responsive">
<table class="jsBoxStatDIvFE">
  <tbody>
      <?php
        if ($rows->lists['played_matches'] !== null) {
            ?>
            <tr>
                
                <td>
                    <strong><?php echo __('Match played','joomsport-sports-league-results-management');
            ?></strong>
                </td>
                <td>
                    <?php 
                        echo esc_html($rows->lists['played_matches']);
            ?>
                </td>
            </tr>
            <?php

        }
        if (count($rows->lists['events_col'])) {
            foreach ($rows->lists['events_col'] as $key => $value) {
                if (isset($rows->lists['players']->{$key})) {
                    ?>
                <tr>
                    
                    <td>
                        <?php echo wp_kses_post($value->getEmblem());?>
                        <strong>
                            <?php echo wp_kses_post($value->getEventName());
                    ?>
                        </strong>
                    </td>
                    <td>
                        <?php 

                        if (is_float(floatval($rows->lists['players']->{$key}))) {
                            echo round($rows->lists['players']->{$key}, 3);
                        } else {
                            echo floatval($rows->lists['players']->{$key});
                        }

                    ?>
                    </td>
                </tr>
                <?php

                }
            }
        }
    ?>
  </tbody>
</table>
</div>
<?php
}
/*<!--jsonlyinproPHP-->*/
?>
<div class="table-responsive">
<?php
    if (count($rows->lists['career'])) {
?>
  <table class="table table-striped jsTableCareer">
  <?php
  if (count($rows->lists['career_head'])) {
  ?>
    <thead>
        <tr>
        <?php
        foreach($rows->lists['career_head'] as $career) {
            
            echo '<th>'.wp_kses_post($career).'</th>';

        }
        
        ?>
        </tr>
    </thead>
  <?php  
  }
  ?>  
  <tbody>
      <?php
        foreach($rows->lists['career'] as $career) {
        ?>
        <tr>
            <?php
            for($intA=0;$intA<count($career);$intA++){
                echo '<td>'.wp_kses_post($career[$intA]).'</td>';
            }
            ?>
        </tr>    

        <?php
        }
        
    ?>
  </tbody>
</table>
<?php
}
?>
</div>
<?php
if($rows->lists['career_matches']){
?>
<div class="center-block jscenter">
    <h3 class="jsCreerMatchStath3"><?php echo __('Matches','joomsport-sports-league-results-management');?></h3>
</div>
<div class="table-responsive">
    <div class="jstable jsMatchDivMain">
        <?php echo ($rows->lists['career_matches']);?>
    </div>
</div>
<?php
}
/*<!--/jsonlyinproPHP-->*/
if(isset($rows->lists['boxscore']) && $rows->lists['boxscore']){
    echo '<div class="center-block jscenter">
                    <h3>'.__('Box Score','joomsport-sports-league-results-management').'</h3>
                </div>';
    echo $rows->lists['boxscore'];
}
if(isset($rows->lists['boxscore_matches']) && $rows->lists['boxscore_matches']){
    echo '<div class="center-block jscenter">
                    <h3>'.__('Match Box Score','joomsport-sports-league-results-management').'</h3>
                </div>';
    echo $rows->lists['boxscore_matches'];
}

?>
    