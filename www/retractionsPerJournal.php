<?php
    $title="Retracted articles per Journal";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
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
                <h1>Retracted articles per Journal</h1>
    <?php
        $queryJournal="SELECT COUNT(*) as nbArticles, j.`name` as journal FROM `article` a INNER JOIN `retraction` r ON r.`doi`=a.`doi` INNER JOIN `journal` j on j.`id`=a.`idJournal` WHERE j.`id`<>23 GROUP BY j.`id`  ORDER BY nbArticles DESC LIMIT 50";
        $journals=mysqli_query($mys, $queryJournal);
        $listJournals=mysqli_fetch_all($journals,MYSQLI_ASSOC);
    ?>
                <div class="clear"></div>
                <div id="tableYearsContainer">
                    <table class="tableYears">
                        <thead>
                            <tr>
                                <td>Journal</td>
                                <td>Number of retractions</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($listJournals as $j) { 
                                $dataJournals.="'".$j['journal']."',";
                                $dataRet.=$j['nbArticles'].',';
                            ?>
                            <tr>
                                <td><?php echo $j['journal']; ?></td>
                                <td><?php echo number_format($j['nbArticles'],0,',',' '); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <canvas id="graphJournalsContainer">
                </canvas>
            </div>
        </div>
        <?php
            $dataJournals=substr($dataJournals,0,strlen($dataJournals)-1);
            $dataRet=substr($dataRet,0,strlen($dataRet)-1);
        ?>
        <script>
            jQuery(document).ready(function() {
              const ctx = document.getElementById('graphJournalsContainer');

              new Chart(ctx, {
                type: 'bar',
                data: {
                  labels: [<?php echo $dataJournals; ?>],
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
