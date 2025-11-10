<?php
    if((isset($_COOKIE['virus_username']) and $_COOKIE['virus_username'] and $_COOKIE['virus_username']!='') and $_SERVER['SCRIPT_NAME']!='/login.php') {
?>
<div class="menuArea">
    <div class="menuToggle">
      <div class="bar1"></div>
      <div class="bar2"></div>
      <div class="bar3"></div>
    </div>
    <div id="menu">
        <div class="menuContainer">
            <div class="menuInside">
              <h2><a href="/">Visualization</a></h2>
              <h2><a href="/list.php">List of articles</a></h2>
              <h2><a href="/retracted.php">Citing retracted</a></h2>
              <h2><a href="/retractionsPerYear.php">Retractions per year</a></h2>
              <h2><a href="/retractionsPerJournal.php">Retractions per journal</a></h2>
            </div>
            <div class="menuUser">
                <div class="menuUsername">
                    User <i><?php echo $userData[0]['firstname'].' '.$userData[0]['name']; ?></i>
                </div>
                <div class="menuLogout">
                    <a href="/login.php?logout=1">
                        <img class="nohover" src="/images/logout.svg" alt="Log out" title="Log out" />
                        <img class="hover" src="/images/logoutHover.svg" alt="Log out" title="Log out" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
    }
?>