<?php                  
    $title="Publications citing more than 2 retracted articles";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/orderby.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
    $queryRetracted="SELECT c.`doi`, c.`title`, count(c.`pubDoi`) AS `nb` FROM `citation` c INNER JOIN `retraction` r ON r.`doi`=c.`pubDoi` WHERE c.`doi` NOT IN (SELECT `doi` FROM `retraction`) AND c.`doi` NOT IN (SELECT `doi` from `eoc`) AND c.`doi` NOT IN (SELECT `retraction` FROM `retraction`) AND c.`doi` NOT IN (SELECT `eoc` from `eoc`) GROUP BY c.`doi` HAVING count(c.`pubDoi`)>2 ORDER BY `nb` DESC";
    $retracted=mysqli_query($mys, $queryRetracted);
    $listRetracted=mysqli_fetch_all($retracted,MYSQLI_ASSOC);
    $n=1;
?>
        <div id="mainContainer">
            <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
            <div id="main">
                <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/logo.php'); ?>
                <h1>Publications citing more than 2 retracted articles</h1>
                <span class="results"><span style="position:relative;top:50px;"><?php echo number_format(count($listRetracted),0,',',' '); ?> results</span></span>
                <div class="clear"></div>
                <div id="tableRetractedContainer">
                    <table class="tableRetracted">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>doi</td>
                                <td>Title</td>
                                <td>Number of retrdacted article cited</td>
                                <td>Search on PubPeer</td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($listRetracted as $r) { ?>
                            <tr>
                                <td><?php echo $n; $n++; ?></td>
                                <td><a href="<?php echo $r['doi']; ?>" target="_blank"><?php echo str_replace('https://doi.org/','',$r['doi']); ?></a></td>
                                <td><?php echo $r['title']; ?></td>
                                <td><?php echo $r['nb']; ?></td>
                                <td>
                                    <a href="https://pubpeer.com/search?q=<?php echo urlencode($r['doi']); ?>" target="_blank" class="pubpeerLink">
                                        <img src="/images/pubpeer.svg" alt="PubPeer" class="nohover" />
                                        <img src="/images/pubpeerHover.svg" alt="PubPeer" class="hover" />
                                    </a>
                                </td>
                            </tr>                            
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>
    </body>
</html>
