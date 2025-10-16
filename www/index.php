<?php
    $title="VIRUS: Visualizing Irregular Research Under Suspicion";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/orderby.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
    if(isset($_GET['generateCsv']) && $_GET['generateCsv'] && $_GET['generateCsv']==1) {
        $query="SELECT a.`doi`, a.`title`, j.`name` as journal, a.`pubDate`, a.`citation`, a.`altmetrics`,  a.`pubpeer`, a.`pubpeerCommentcount`, e.`eoc`, r.`retraction` FROM article a INNER join `journal` j ON j.`id`=a.`iDJournal` LEFT OUTER JOIN `eoc` e ON e.`doi`=a.`doi` LEFT OUTER JOIN `retraction` r ON r.`doi`=a.`doi` GROUP BY a.`doi`";
        $mys=mysqli_connect($dbhost,$dbuser,$dbpass,$dbbd);
        $articles=mysqli_query($mys, $query);
        $listArticles=mysqli_fetch_all($articles,MYSQLI_ASSOC);
        $csv="Line,DOI,Title,Authors,Journal_Name,Date,DOI_Status,Status,Citations,Altmetrics,EoC_link,Retraction_link,Pubpeer_comments_count,Pubpeer_link\r\n";
        $n=0;
        foreach($listArticles as $a) {
		        $n++;
                $doi_status='';
                $authors_list='';
		        $status="Online";
                if($a['eoc']) {
                    $status='EoC';
                    $doi_status=$a['eoc'];
                }
                if($a['retraction']) {
                    $status='Retracted';
                    $doi_status=$a['retraction'];
                }
                $query_authors="SELECT * FROM `author` a INNER JOIN  `articleAuthor` aa on aa.`idAuthor`=a.`id` where aa.`doiArticle`='".$a['doi']."';";
                $authors=mysqli_query($mys, $query_authors);
                $listAuthors=mysqli_fetch_all($authors,MYSQLI_ASSOC);
                foreach($listAuthors as $au) {
                    $authors_list.=$au['name']." ".$au['firstname'].' - ';
                }
                if($authors_list) {
                    $authors_list=substr($authors_list,0,strlen($authors_list)-3);
                }
                $pubpeer='';
                $pubpeerCount='';
                if($a['pubpeerCommentcount'] && $a['pubpeerCommentcount'] && is_numeric($a['pubpeerCommentcount']) && $a['pubpeerCommentcount']>0) {
                    $pubpeerCount=$a['pubpeerCommentcount'];
                    $pubpeer=$a['pubpeer'];
                }
		        $csv.=$n.','
			        .'"'.str_replace('"'.$n.'"'.','.'"'.'https://doi.org/','',$a['doi']).'"'.','.'"'.str_replace('"','\"',$a['title']).'"'.','.'"'.$authors_list.'"'.','.'"'.$a['journal'].'"'.','.'"'.$a['pubDate'].'"'.','.','.'"'.$doi_status.'"'.',"'.$status.'"'.','.'"'.$a['citation'].'"'.','.'"'.$a['altmetrics'].'"'.','.'"'.$a['eoc'].'"'.','.'"'.$a['retraction'].'","'.$pubpeerCount.'","'.$pubpeer.'"'."\r\n";
        }
        $inf=fopen('data.csv', 'w');
        fwrite($inf,$csv);
        fclose($inf);
    }
?>
<!DOCTYPE html>
        <div id="mainContainer">
            <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
            <div id="main">
                <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/logo.php'); ?>
                <h1><?php echo $title; ?></h1>
                <button id="dark-mode-toggle">Toggle Dark Mode</button>
                <div id="general-info-box">
                    <p>The ratio of retracted papers to articles published is rising at an alarming rate and towers, nowadays, 0.2%. Most of these retractions are due to the tireless efforts of scientific and academic sleuths who find, track, and report questionable or outright problematic papers on Pubpeer and in correspondence with editors. Questionable or problematic papers can present in a variety of forms, ranging from self-plagiarism to completely fake papers written by ChatGPT or other language models, and include image or data manipulations, paper mill products detected through their use of tortured phrases, or the use of sneaked references.</p>
                    <p>This interactive visualization aims at providing useful information about large set of papers that are under Eoc or retracted.</p>
                    </div>
                    <div id="general-box">
                        <div id="interaction-info-container">
                          <div id="grouped-checkbox-container">
                            <h3> Filtering</h3>
                            <input type="checkbox" id="grouped-checkbox">
                            <label for="grouped-checkbox">Grouped</label>
                            
                            <input type="checkbox" id="log-scale-checkbox">
                            <label for="log-scale-checkbox">Use Log Scale</label>
                            <p>IRB Number: <select id="IRBNumberSelect">
                                <!-- Options will be added dynamically using D3.js -->
                            </select></p>
                            <p>Author: <select id="AuthorSelect">
                                <!-- Options will be added dynamically using D3.js -->
                            </select></p>
                            <p>Minimum number of papers for an author:
                              <select id="MinNumberPerAuthor">
                                <!-- Options will be added dynamically using D3.js -->
                              </select>
                            </p>
                            <p>Min # Papers: <select id="MinNumberSelect"></select>
                                <!-- Options will be added dynamically using D3.js -->
                               Max # Papers: <select id="MaxNumberSelect"></select>
                            </p>
                            <p>Citation Type:
                              <select id="citationType">
                                <option value="Citations">Citations</option>
                                <option value="Self_Citations">Self Citations</option>
                                <option value="Altmetrics">Altmetrics</option>
                              </select>
                            </p> 
                            <table id="slider-table">
                              <tr>
                                <td>Minimum Citation:</td>
                                <td>
                                  <input type="range" min="0" max="100" value="0" class="slider" id="min_citation_slider">
                                </td>
                                <td><span id="min_citation_value">0</span></td>
                              </tr>
                              <tr>
                                <td>Minimum Altmetric:</td>
                                <td>
                                  <input type="range" min="0" max="100" value="0" class="slider" id="min_altmetric_slider">
                                </td>
                                <td><span id="min_altmetric_value">0</span></td>
                              </tr>
                              <tr>
                                <td>Minimum Self-citation:</td>
                                <td>
                                  <input type="range" min="0" max="100" value="0" class="slider" id="min_self_slider">
                                </td>
                                <td><span id="min_self_value">0</span></td>
                              </tr>
                            </table>
                          </div>
                        </div>
                        <div id="info-box">
                          <h3> Paper information</h3>
                          <table id="info-table">
                            <tbody>
                              <tr>
                                <td class="title_td">DOI:</td>
                                <td id="DOI"></td>
                              </tr>
                              <tr>
                                <td class="title_td">Title:</td>
                                <td id="Title"></td>
                              </tr>
                              <tr>
                                <td class="title_td">Journal Name:</td>
                                <td id="Journal_Name"></td>
                              </tr>
                              <tr>
                                <td class="title_td">Status:</td>
                                <td id="Status"></td>
                              </tr>
                              <tr>
                                <td class="title_td">Citations:</td>
                                <td id="Citations"></td>
                              </tr>
                              <tr>
                                <td class="title_td">Altmetrics:</td>
                                <td id="Altmetrics"></td>
                              </tr>
                              <tr>
                                <td class="title_td">Link to Status Update:</td>
                                <td id="DOI_Status"></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div id="altmetric-container">
                          <!-- Altmetric donut will be embedded here -->
                          <div data-badge-type='medium-donut' class='altmetric-embed' data-badge-details='right' data-doi='10.1016/j.nmni.2016.06.003'></div>
                        </div>
                    </div>
                    <div id="graph-container">
                    <svg id="chart"></svg>
                    <div class="legend" id="legend"></div>
                    </div>
                    <script src="https://d3js.org/d3.v7.min.js"></script>
                    <script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>
                    <script src="/js/visualization.js"></script>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>
    </body>
</html>