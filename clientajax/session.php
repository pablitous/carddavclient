<?php
session_start();
if (!isset($_SESSION['username'])){
	header("Location: login.php");
}else{
	$user_last_access = $_SESSION["user_last_access"];
	$now = date("Y-n-j H:i:s");
	$time_elapsed = (strtotime($now)-strtotime($user_last_access));

    if($time_elapsed >= 7200) 
    {     
      session_destroy();
      header("Location: login.php");
    }else{   
    	$_SESSION["user_last_access"] = $now;
    }
}
?>