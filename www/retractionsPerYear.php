<?php
    $title="Retracted articles per Publication Year";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/orderby.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
    $dataYears='';
    $dataRet='';
?>
        <div id="mainContainer">
            <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
            <div id="main">
                <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/logo.php'); ?>
                <h1>Retracted articles per Publication Year</h1>
    <?php
        $queryYear="SELECT COUNT(*) as nbArticles, YEAR(a.`pubDate`) as yearPub FROM `article` a INNER JOIN `retraction` r ON r.`doi`=a.`doi` WHERE YEAR(a.`pubDate`)<>0  GROUP BY yearPub ORDER BY yearPub";
        $years=mysqli_query($mys, $queryYear);
        $listYears=mysqli_fetch_all($years,MYSQLI_ASSOC);
    ?>
                <div class="clear"></div>
                <div id="tableYearsContainer">
                    <table class="tableYears">
                        <thead>
                            <tr>
                                <td>Year of publication</td>
                                <td>Number of retractions</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($listYears as $y) { 
                                $dataYears.=$y['yearPub'].',';
                                $dataRet.=$y['nbArticles'].',';
                            ?>
                            <tr>
                                <td><?php echo $y['yearPub']; ?></td>
                                <td><?php echo number_format($y['nbArticles'],0,',',' '); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <canvas id="graphYearsContainer">
                </canvas>
            </div>
        </div>
        <?php
            $dataYears=substr($dataYears,0,strlen($dataYears)-1);
            $dataRet=substr($dataRet,0,strlen($dataRet)-1);
        ?>
        <script>
            jQuery(document).ready(function() {
              const ctx = document.getElementById('graphYearsContainer');

              new Chart(ctx, {
                type: 'bar',
                data: {
                  labels: [<?php echo $dataYears; ?>],
                  datasets: [{
                    label: 'Number of retractions',
                    backgroundColor: '#fbb13c',
                    borderColor: '#fbb13c',
                    data: [<?php echo $dataRet; ?>],
                    borderWidth: 1
                  }]
                }
              });
            });
        </script>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>
    </body>
</html>
