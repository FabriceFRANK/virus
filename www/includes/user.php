<?php
    if(isset($_GET['logout']) && $_GET['logout']=="1") {
        setcookie("virus_username", "", time() + 3600*24);
        header("Location: /login.php", true, 302);
        die();        
    }
    else {
        if(isset($_POST['username']) && isset($_POST['password'])) {
            $username=$_POST['username'];
            $password=$_POST['password'];
            $sqlUser="SELECT * FROM `user` where `username`='".$username."' and `password`='".md5($password)."'";
            $user=mysqli_query($mys, $sqlUser);
            $nbUser=mysqli_num_rows($user);
            if($nbUser>0) {
                $_COOKIE['virus_authenticated']=1;
                setcookie("virus_username", $username, time() + 3600*24);
                header("Location: /", true, 302);
                die();
            }
            else {
                $message='<span class="loginError">Unknown username or password</span>';
                setcookie("virus_username", "", time() + 3600*24);
            }
        }
        if((!isset($_COOKIE['virus_username']) or !$_COOKIE['virus_username'] or $_COOKIE['virus_username']=="") and $_SERVER['SCRIPT_NAME']!='/login.php') {
            header("Location: /login.php", true, 302);
            die();
        }
        else {
            if(isset($_COOKIE['virus_username'])) {
                $sqlUser="SELECT * FROM `user` where `username`='".$_COOKIE['virus_username']."'";
                $user=mysqli_query($mys, $sqlUser);
                $userData=mysqli_fetch_all($user,MYSQLI_ASSOC);        
            }
        }
    }
?>
