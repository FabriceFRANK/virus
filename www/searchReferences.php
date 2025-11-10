<?php                  
    $title="Search retracted references";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/orderby.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
    $value="";
    if(isset($_POST['doi'])) {
        $value=$_POST['doi'];
    }
?>
        <div id="mainContainer">
            <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
            <div id="main">
                <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/logo.php'); ?>
                <h1>Search retracted references</h1>
                <div class="clear"></div>
                <form action="/searchReferences.php" method="POST" id="searchReferences">
                    <input type="text" placeholder="doi" id="doi" name="doi"  value="<?php echo $value; ?>"/>
                    <input type="submit" value="search" />
                </form>
                <div class="searchResults">
<?php
    if(isset($_POST['doi'])) {
?>
                <h2>Search result for <i><a href="<?php echo $value; ?>" target="_blank"><?php echo $value; ?></a></i></h2>
                <div id="searchResultsContainer">
                    <div class="loading-spinner"></div>
                </div>
                <script>
                jQuery(document).ready(function() {
                    searchResults('<?php echo $value; ?>');
                })
                </script>
<?php
    }
?>   
                </div>
            </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>
    </body>
</html>
