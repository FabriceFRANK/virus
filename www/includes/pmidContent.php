<?php
    $userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/113.0";
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, 'https://pubmed.ncbi.nlm.nih.gov/'.$_GET['pmid'].'/'); 
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent); 
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    $results = curl_exec($ch);
    if (!$results) {
        echo "Error querying API.\r\n";
        echo "    cURL error number:" . curl_errno($ch)."\r\n";
        echo "    cURL error:" . curl_error($ch)."\r\n";
        exit;
    }
    else {
        $results=str_replace('<a ','<a target="_blank" ', str_replace('<A ','<A target="_blank" ', $results));
        echo $results;
    }
    curl_close($ch);        
?>
