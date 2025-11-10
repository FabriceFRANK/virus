<?php
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
    $doi=$_GET['doi'];
    $query="SELECT a.*, r.*, e.*, j.`name` as `journal` FROM `article` a INNER JOIN `journal` j on a.`idJournal`=j.`id` LEFT OUTER JOIN `retraction` r on r.`doi`=a.`doi` LEFT OUTER JOIN `eoc` e on e.`doi`=a.`doi` where a.`doi`='".$doi."'";
    $article=mysqli_query($mys, $query);
    $nbresults=mysqli_num_rows($article);
    if($nbresults==0) {
        echo '<br>br><h1>No result</h1>';
        die();
    }
    $article=mysqli_query($mys, $query);
    $dataArticle=mysqli_fetch_all($article,MYSQLI_ASSOC)[0];    
?>
<div class="articleDetailsClose">
    <div class="bar45-1"></div>
    <div class="bar45-2"></div>
</div>
<a href="<?php echo $dataArticle['doi']; ?>" target="_blank"><h1><?php echo $dataArticle['title']; ?></h1></a>
<h2 class="pubJournalDetails"><?php echo $dataArticle['journal']; ?></h2>
<?php if ($dataArticle['pubDate']!=Null && $dataArticle['pubDate']!='0000-00-00') { ?>
<p class="pubDateDetails">Publication date : <?php echo $dataArticle['pubDate']; ?></p>
<?php } ?>
<?php if ($dataArticle['citation']!=Null && $dataArticle['citation']!='0') { ?>
<p class="pubDateDetails">Citations : <?php echo $dataArticle['citation']; ?></p>
<?php } ?>
<?php if ($dataArticle['altmetrics']!=Null && $dataArticle['altmetrics']!='0') { ?>
<p class="pubDateDetails">Altmetrics : <?php echo $dataArticle['altmetrics']; ?></p>
<?php } ?>
<?php if ($dataArticle['retraction']!=Null && $dataArticle['retraction']!='') { ?>
<p class="pubDateDetails pubDetailsRetracted"><a href="<?php echo $dataArticle['retraction']; ?>" target="_blank">Retracted<?php if ($dataArticle['retractionDate']!=Null && $dataArticle['retractionDate']!='0000-00-00') { echo ' on '.$dataArticle['retractionDate']; } ?></a></p>
<?php } ?>
<?php if ($dataArticle['eoc']!=Null && $dataArticle['eoc']!='') { ?>
<p class="pubDateDetails pubDetailsEoc"><a href="<?php echo $dataArticle['eoc']; ?>" target="_blank">EoC<?php if ($dataArticle['eocDate']!=Null && $dataArticle['eocDate']!='0000-00-00') { echo ' on '.$dataArticle['eocDate']; } ?></a></p>
<?php } ?>
<?php if ($dataArticle['pubpeer']!=Null && $dataArticle['pubpeerCommentcount']!='0') { ?>
<a href="<?php echo $dataArticle['pubpeer']; ?>" target="_blank"><p class="pubDateDetails">Pubpeer comment<?php if($dataArticle['pubpeerCommentcount']>1) { echo 's'; } ?> : <?php echo $dataArticle['pubpeerCommentcount']; ?></p></a>
<?php } ?>
<?php if($dataArticle['pmid']!=Null && $dataArticle['pmid']!='') { ?>
    <iframe src="/includes/pmidContent.php?pmid=<?php echo $dataArticle['pmid']; ?>"></iframe>
<?php } ?>
<?php
    $queryCit="SELECT c.*, r.* from `citation`c LEFT OUTER JOIN `retraction` r on r.`doi`=c.`doi` WHERE c.`pubDoi`='".$doi."'";
    $citations=mysqli_query($mys, $queryCit);
    $nbresults=mysqli_num_rows($citations);
    if($nbresults!=0) {
?>
    <h3>Citations</h3>
    <ol class="ulReferences">
    <?php foreach($citations as $c) { ?>
        <li><a href="<?php echo $c['doi']; ?>" target="_blank"><?php echo $c['title']; ?> (<?php echo $c['pubDate']; ?>)</a><?php if($c['retraction']!=Null && $c['retraction']!='') { echo '&nbsp;<span class="pubDetailsRetracted"><a href="'.$c['retraction'].'" target="_blank">Retracted</a></span>'; } ?></li>
    <?php } ?>
    </ol>
<?php        
    }
    $queryRef="SELECT c.*, r.* from `reference` c LEFT OUTER JOIN `retraction` r on r.`doi`=c.`doi` WHERE `pubDoi`='".$doi."'";
    $references=mysqli_query($mys, $queryRef);
    $nbresults=mysqli_num_rows($references);
    if($nbresults!=0) {
?>
    <h3>References</h3>
    <ol class="ulReferences">
    <?php foreach($references as $r) { ?>
        <li><a href="<?php echo $r['doi']; ?>" target="_blank"><?php echo $r['title']; ?> (<?php echo $r['pubDate']; ?>)</a><?php if($r['retraction']!=Null && $r['retraction']!='') { echo '&nbsp;<span class="pubDetailsRetracted"><a href="'.$c['retraction'].'" target="_blank">Retracted</a></span>'; } ?></li>
    <?php } ?>
    </ol>
<?php        
    }
?>
