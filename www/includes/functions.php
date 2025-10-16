<?php
    function sorter($field,$url,$params,$orderby) {
        sorterArrow($field,$url,$params,'asc',$orderby);
        sorterArrow($field,$url,$params,'desc',$orderby);
    }
    function sorterArrow($field,$url,$params,$direction,$orderby) {
        $urlSort=$url.$params;
        if($params=='') {
            $urlSort.='?';
        }
        $urlSort.='orderby='.urlencode($field.' '.$direction);
        $active='';
        if($orderby==$field.' '.$direction) {
            $active=' active ';
        }
        
?>
    <a class="sortArrow sortArrow<?php echo $direction; ?> <?php echo $active; ?>" href="<?php echo $urlSort; ?>"><img src="/images/<?php echo $direction; ?>.svg" alt="Sort by <?php echo $field.' '.$direction; ?>" /></a>
<?php        
    }
?>
