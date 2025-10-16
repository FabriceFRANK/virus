<?php
    $title="List of VIRUS data - Article retracted and/or with EoC";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/orderby.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
?>
<?php
    $filter=array();
    $join=array();
    $perPage=20;
    $start=0;
    $perPageValues='';
    $txtSearch='';
    $url=$_SERVER['SCRIPT_NAME'];
    $params='';
    $joinEoc=" LEFT OUTER JOIN ";
    $joinRetraction=" LEFT OUTER JOIN ";
    if(isset($_GET['txtSearch']) && $_GET['txtSearch']!='') {
        $txtSearch=str_replace('https://doi.org/','',strtolower($_GET['txtSearch']));
        $params.='txtSearch='.urlencode($txtSearch).'&';
    }
    if(isset($_GET['perPage']) && is_numeric($_GET['perPage'])) {
        $perPage=$_GET['perPage'];
        $params.='perPage='.$perPage.'&';
    }
    else {
        if(isset($_COOKIE['virus_perPage']) && $_COOKIE['virus_perPage']) {
            $perPage=$_COOKIE['virus_perPage'];
        }
    }
    foreach(array(20,50,100) as $v) {
        $select='';
        if($perPage==$v) {
            $select=' selected ';
        }
        $perPageValues.='<option '.$select.' value="'.$v.'">'.$v.'</option>';
    }
    if(isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start=$_GET['start'];
    }
    if(isset($_GET['filterAuthor']) && is_numeric($_GET['filterAuthor'])) {
        $join[]=" INNER JOIN `articleAuthor` aa on aa.`doiArticle`=a.`doi` ";
        $filter[]=" aa.`idAuthor`=".$_GET['filterAuthor']." ";        
        $params.='filterAuthor='.$_GET['filterAuthor'].'&';
    }
    if(isset($_GET['filterJournal']) && is_numeric($_GET['filterJournal'])) {
        $filter[]=" j.`id`=".$_GET['filterJournal']." ";   
        $params.='filterJournal='.$_GET['filterJournal'].'&';
    }
    if(isset($_GET['type']) && $_GET['type']=="eoc") {
        $joinEoc=" INNER JOIN ";
        $params.='type=eoc&';
    }
    if(isset($_GET['type']) && $_GET['type']=="retraction") {
        $joinRetraction=" INNER JOIN ";
        $params.='type=retraction&';
    }
    $and='';
    $where='';
    $clauseJoin='';
    foreach($filter as $f) {
        $where.=$and.' '.$f.' ';
        $and=" and ";
    }
    foreach($join as $j) {
        $clauseJoin=$j;
    }
    if($txtSearch) {
        if($where!='') {
            $where.=' and ';
        }
        $where.=" (a.`doi` LIKE '%".$txtSearch."%' OR a.`title` LIKE '%".$txtSearch."%' OR a.`pubDate` LIKE '%".$txtSearch."%' or a.`pubpeer` LIKE '%".$txtSearch."%' OR r.`doi` LIKE '%".$txtSearch."%' OR r.`retraction` LIKE '%".$txtSearch."%' OR r.`retractionDate` LIKE '%".$txtSearch."%' OR e.`doi` LIKE '%".$txtSearch."%' OR e.`eoc` LIKE '%".$txtSearch."%' OR e.`eocDate` LIKE '%".$txtSearch."%'  OR j.`name` LIKE '%".$txtSearch."%') ";
    }
    if($where!='') {
        $where=" WHERE ".$where;
    }
    $queryAll="SELECT a.*, r.retraction, r.`doi` as retractionDoi, r.source as retractionSource, r.reason as retractionReason, r.retractionDate, e.eoc, e.`doi` as eocDoi, e.eocDate, e.source as eocSource, e.reason as eocReason, j.name as journal  
            FROM `article` a 
            INNER JOIN `journal` j ON j.`id`=a.`idJournal` 
            ".$joinRetraction." `retraction` r ON r.`doi`=a.`doi` 
            ".$joinEoc." `eoc` e ON e.`doi`=a.`doi` 
            ".$clauseJoin." ".$where;
    $query=$queryAll.' ORDER BY '.$orderby." LIMIT ".$start.", ".$perPage;
    $articlesAll=mysqli_query($mys, $queryAll);
    $nbresults=mysqli_num_rows($articlesAll);
    $articles=mysqli_query($mys, $query);
    $list_articles=mysqli_fetch_all($articles,MYSQLI_ASSOC);
    $n=0;
    $queryAuthors="SELECT * FROM `author` order by `name` ASC";
    $authors=mysqli_query($mys, $queryAuthors);
    $list_authors=mysqli_fetch_all($authors,MYSQLI_ASSOC);
    $optionAuthors='<option value="">Author</option>';
    foreach($list_authors as $a) {
        $select='';
        if(isset($_GET['filterAuthor']) && $_GET['filterAuthor']==$a['id']) {
            $select=' selected ';
        }
        $optionAuthors.='<option '.$select.' value="'.$a['id'].'">'.$a['name'].' '.$a['firstname'].'</option>';
    }
    $queryJournals="SELECT * FROM `journal` order by `name` ASC";
    $journals=mysqli_query($mys, $queryJournals);
    $list_journals=mysqli_fetch_all($journals,MYSQLI_ASSOC);
    $optionJournals='<option value="">Journal</option>';
    foreach($list_journals as $j) {
        $select='';
        if(isset($_GET['filterJournal']) && $_GET['filterJournal']==$j['id']) {
            $select=' selected ';
        }
        $optionJournals.='<option '.$select.' value="'.$j['id'].'">'.$j['name'].'</option>';
    }
    if($params) {
        $url.='?'.$params;
    }
    $end=$start+$perPage;
    if($end>$nbresults) {
        $end=$nbresults;
    }
?>
        <div id="mainContainer">
            <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
            <div id="main">
				<?php include($_SERVER['DOCUMENT_ROOT'].'/includes/logo.php'); ?>
                <h1>VIRUS data</h1>
                <div class="filters">
                    <form id="formFilters" action="/list.php" method="GET">
                        <select id="type" name="type"><option value="">Type</option><option value="eoc" <?php if(isset($_GET['type']) && $_GET['type']=="eoc") { echo ' selected '; } ?>>Expressions of Concern</option><option <?php if(isset($_GET['type']) && $_GET['type']=="retraction") { echo ' selected '; } ?> value="retraction">Retraction</option></select>
                        <select id="filterAuthor" name="filterAuthor"><?php echo $optionAuthors; ?></select>
                        <select id="filterJournal" name="filterJournal"><?php echo $optionJournals; ?></select>
                        <input type="submit" value="Filter" /> <a class="filterReset" title="Reset filters" href="/list.php"><img src="/images/remove.svg" alt="Reset filters" /></a>
                        <span class="results"><?php echo number_format($nbresults, 0,',',' '); ?> results</span>
                        <br>
                        <label class="perPageLabel">
                            <select id="perPage" name="perPage">
                                <?php echo $perPageValues; ?>
                            </select> entries per page
                        </label>
                        <label class="txtSearchLabel"> 
                            <input type="search" name="txtSearch" id="txtSearch" value="<?php echo $txtSearch; ?>" placeholder="Search"/>
                        </label>                
                    </form>
                </div>
                <div class="clear"></div>
                <table cellpadding="0" cellmargin="0" id="articles">
                    <thead>
                        <tr>
                            <td><span class="tableHeader">DOI</span><?php echo sorter('a.doi',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">Date</span><?php echo sorter('a.pubDate',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">Title</span><?php echo sorter('a.title',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader tableHeaderAuthors">Authors</span></td>
                            <td><span class="tableHeader">Journal</span><?php echo sorter('j.name',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">Retraction</span><?php echo sorter('r.retraction',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">EoC</span><?php echo sorter('e.eoc',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">Cited</span><?php echo sorter('a.citation',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">Altmetrics</span><?php echo sorter('a.altmetrics',$url,$params,$orderby); ?></td>
                            <td><span class="tableHeader">PubPeer</span><?php echo sorter('a.pubpeerCommentcount',$url,$params,$orderby); ?></td>
                        </tr>
                    </thead>
                    <tbody>
                <?php foreach($list_articles as $a) { 
                          $query_authors="SELECT a.* from `author` a INNER JOIN articleAuthor aa on aa.`idAuthor`=a.id where aa.`doiArticle`='".$a['doi']."' order by aa.id ASC";
                          $authors=mysqli_query($mys, $query_authors);
                          $list_authors=mysqli_fetch_all($authors,MYSQLI_ASSOC);
                          $authors_txt='';
                          foreach($list_authors as $aa) {
                              $authors_txt.=trim($aa['firstname'].' '.$aa['name']).', ';
                          }
                          if(strlen($authors_txt)) {
                              $authors_txt=substr($authors_txt,0,strlen($authors_txt)-2);
                          }
                          else {
                              $authors_txt='Unknown';
                          }
                          $retraction='';
                          $eoc='';
                          if($a['retractionDoi']) {
                              if($a['retraction']) {
                                  $retraction='<a href="'.$a['retraction'].'" target="_blank">'.str_replace('https://doi.org/','',$a['retraction']).'</a>';
                              }
                              else {
                                  $retraction="No notice";
                              }
                              if($a['retractionDate']) {
                                  $retraction.='<br>on '.$a['retractionDate'];
                              }
                          }
                          if($a['eocDoi']) {
                              if($a['eoc']) {
                                $eoc='<a href="'.$a['eoc'].'" target="_blank">'.str_replace('https://doi.org/','',$a['eoc']).'</a>';
                              }
                              else {
                                  $eoc="No notice";                                  
                              }
                              if($a['eocDate']) {
                                  $eoc.='<br>on '.$a['eocDate'];
                              }
                          }
                          $pubdate='';
                          if($a['pubDate']!='0000-00-00') {
                              $pubdate=$a['pubDate'];
                          }
                          $citation='';
                          if(!is_null($a['citation'])) {
                              $citation=$a['citation'];
                          }
                          $pubpeer='';
                          if(!is_null($a['pubpeer']) && $a['pubpeerCommentcount']!=0) {
                              $s='';
                              if($a['pubpeerCommentcount']>1) {
                                  $s='s';
                              }
                              $pubpeer='<a href="'.$a['pubpeer'].'" target="_blank">'.str_replace('https://pubpeer.com/','',$a['pubpeer']).' </a><br>('.$a['pubpeerCommentcount'].' comment'.$s.')';
                          }
                          $altmetrics='';
                          if(!is_null($a['altmetrics'])) {
                              $altmetrics=$a['altmetrics'];
                          }
                ?>
                        <tr>
                            <td class="doi"><a href="<?php echo $a['doi']; ?>" target="_blank"><?php echo str_replace('https://doi.org/','',$a['doi']); ?></a></td>
                            <td class="pubDate"><?php echo $pubdate; ?></td>
                            <td class="title"><?php echo $a['title']; ?></td>
                            <td class="authors"><?php echo $authors_txt; ?></td>
                            <td class="journal"><?php echo $a['journal']; ?></td>
                            <td class="retraction"><?php echo $retraction; ?></td>
                            <td class="eoc"><?php echo $eoc; ?></td>
                            <td class="citation"><?php echo $citation; ?></td>
                            <td class="altmetrics"><?php echo $altmetrics; ?></td>
                            <td class="pubpeer"><?php echo $pubpeer; ?></td>
                        </tr>
                <?php } ?>        
                    </tbody>
                </table>
                <p>Results <?php echo $start+1; ?> to <?php echo $end; ?> of <?php echo $nbresults; ?></p>
            <?php 
            if($nbresults>$perPage) { 
                $pages=ceil($nbresults/$perPage);
                $current=($start/$perPage);
                $first=$current-10;
                $startPoints='...';
                $endPoints='...';
                if($first<0) {
                    $first=0;
                }
                $last=$first+20;
                if($last>=($pages)) {
                    $last=$pages;
                }
                if($first==0) {
                    $startPoints='';
                }
                if($last==$pages) {
                    $endPoints='';
                }  
                $prev=$current-1;
                if($prev<0) {
                    $prev=0;
                }
                $next=$current+1;
                if($next>$pages) {
                    $next=$pages;
                }
                $lastStart=($pages-1)*$perPage;
                if($params) {
                    $prevPage=$url.'start='.($prev*$perPage);
                    $nextPage=$url.'start='.($next*$perPage);
                    $lastPage=$url.'start='.$lastStart;
                }
                else {
                    $prevPage=$url.'?start='.($prev*$perPage);
                    $nextPage=$url.'?start='.($next*$perPage);
                    $lastPage=$url.'?start='.$lastStart;
                }
            ?>
                <ul class="pagination">
                    <li><a href="<?php echo $url; ?>"><<</a></li>
                    <li><?php if($start!=0) { ?><a href="<?php echo $prevPage; ?>"><?php } ?><<?php if($start!=0) { ?></a><?php } ?></li>
                    <li><?php echo $startPoints; ?></li>
                    <?php for($i=$first;$i<$last;$i++) { 
                        $currentClass='';
                        $pageUrlClose='</a>';
                        if($params) {
                            $pageUrl='<a href="'.$url.'start='.($i*$perPage).'">';
                        }
                        else {
                            $pageUrl='<a href="'.$url.'?start='.($i*$perPage).'">';
                        }
                        if($i==$current) {
                            $pageUrl='';
                            $currentClass=' class="current" ';
                            $pageUrlClose='';
                        }
                    ?>
                    <li<?php echo $currentClass; ?>><?php echo $pageUrl; ?><?php echo $i+1; ?><?php echo $pageUrlClose; ?></li>
                    <?php } ?>
                    <li><?php echo $endPoints; ?></li>
                    <li><?php if($next<$page) { ?><a href="<?php echo $nextPage; ?>"><?php } ?>></a></li>
                    <li><a href="<?php echo $lastPage; ?>">>></a></li>
                </ul>
                <?php } ?>
            </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>
    </body>
</html>