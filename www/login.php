<?php
    $title="List of VIRUS data - Login form";
    include($_SERVER['DOCUMENT_ROOT'].'/includes/bddConnect.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/user.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/orderby.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');
    include($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
    $message='';
?>
        <div id="mainContainer">
            <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/menu.php'); ?>
            <div id="main">
                <h1>VIRUS login</h1>
                <div class="loginContainer">
                    <div class="loginWrapper">
                        <div class="loginArea">
                            <form id="loginForm" name="loginForm" action="/login.php" method="POST">
                                <input type="username" id="username" name="username" required placeholder="User name" />
                                <input type="password" id="password" name="password" required placeholder="Password" />
                                <input type="submit" value="Login" />
                                <?php echo $message; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
        <?php include($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>
    </body>
</html>