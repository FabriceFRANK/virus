<?php  
    $orderby="a.citation desc";
    if(isset($_COOKIE['virus_orderby']) and $_COOKIE['virus_orderby']) {
        $orderby=$_COOKIE['virus_orderby'];
    }
    if(isset($_GET['orderby']) && $_GET['orderby']!='') {
        $orderby=urldecode($_GET['orderby']);
        setcookie("virus_orderby", $orderby, time() + 3600*24*30, null,null, false, true);
        
    }
?>
