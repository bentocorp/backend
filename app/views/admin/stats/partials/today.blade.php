
<!--
******************************************************************************
Today's Stats
******************************************************************************
-->
<?php
use Bento\core\Stats\Stats;

$todaysStats = Stats::getTodaysStats();
?>

<h1>Today's Stats</h1>

<?php #var_dump($todaysStats); ?>

<ul>
    <?php
    foreach($todaysStats as $key => $val)
    {
        echo "<li><cap>$key</cap></li>";
        echo "<ul>";
        foreach ($val as $thingSold) {
            echo "<li>$thingSold->item_type: $thingSold->TotalSold</li>";
        }
        echo "</ul>";
    }
    ?>
</ul>

