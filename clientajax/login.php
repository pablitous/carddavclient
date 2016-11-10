<?php
include 'config.php';

use Sabre\DAV\Client;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAV\PropPatch;
use Sabre\VObject;

include '../sabredav/vendor/autoload.php';

    if (isset($_POST['serverurl']) && isset($_POST['username']) && (isset($_POST['password']))){
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        $serverurl = $_POST['serverurl'];
        //print_r($_POST);

        $settings = array(
            'baseUri' => $serverurl.'/dav.php',
            'userName' => $username,
            'password' => $password
        );
        $client = new Client($settings);
        //print_r($client);die();
        if ($client->options()!=null){
            session_start();
            $_SESSION['user_last_access'] = date("Y-n-j H:i:s");
            $_SESSION['username'] = $username;
            $_SESSION['serverurl'] = $serverurl;
            $_SESSION['client'] = $client;
            //eliminar despues, es solo de prueba el ass en session
            //$_SESSION['password'] = $password;
            header("Location: index.php");
        }
    }
?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>eb+ | PHP webDav Client</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">

    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>

                <h1 class="logo-name">eb+</h1>

            </div>
            <h3>Welcome to eb+</h3>
            <p>The best webDav PHP client working for baikal powered by <a href="http://www.ebsolutions.com.ar" target="_blank">Even Better Solutions</a>
            </p>
            <p>Login in. To see it in action.</p>
            <form class="m-t" role="form" action="login.php" method="post">
                <div class="form-group">
                    <select class="form-control m-b" name="serverurl">
                        <?php
                        foreach($serversUrls as $serverUrl){
                            echo '<option value="'.$serverUrl.'">'.$serverUrl.'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Username" required="" name="username">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" required="" name="password">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>

                <!--<a href="#"><small>Forgot password?</small></a>
                <p class="text-muted text-center"><small>Do not have an account?</small></p>
                <a class="btn btn-sm btn-white btn-block" href="register.html">Create an account</a>-->
            </form>
            <p class="m-t"> <small>eb+ powered by <a href="http://www.ebsolutions.com.ar" target="_blank">Even Better Solutions</a> &copy; 2016</small> </p>
        </div>
    </div>
    
    <!-- Mainly scripts -->
    <script src="js/jquery-2.1.1.js"></script>
    <script src="js/bootstrap.min.js"></script>

</body>

</html>
