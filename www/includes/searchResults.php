<?php
  $doi=$_GET['doi'];
  $script=$_SERVER['DOCUMENT_ROOT'].'/includes/searchResults.py';
  $command = escapeshellcmd("python3 ".$script." ".$doi);
  $output=[];
  $returnStatus=false;
  try {
      exec($command." 2> /dev/null", $output,$returnStatus);
      if($returnStatus===0) {
          echo implode('<br>', $output);      
      }
      else {
          $error_details = implode("\n", $output);
          throw new Exception(
              "Python script failed (Exit Code: $return_status). Output/Error:\n$error_details"
          );
      }
  } catch (Exception $e) {
    echo "❌ An error occurred during Python execution.<br>";
    echo "Error Message: **" . $e->getMessage() . "**<br>";
    
    error_log("Python Script Error: " . $e->getMessage());
}
?>
