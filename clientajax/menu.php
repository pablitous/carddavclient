<nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header" style="text-align:center;">
                    <div class="dropdown profile-element"> <span>
                            <img alt="image" class="img-circle" src="img/contact-logo.png" width="48px;" />
                             </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?=$_SESSION['username']?></strong>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">
                        eb+
                    </div>
                </li>
                <li>
                    <a href="#"><i class="fa fa-users"></i> <span class="nav-label">AddressBooks</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse in">
                    <?php
                    $i=0;
                    foreach($addressbooks as $key=>$addressbook){
                        if($i>0){
                            echo '<li class="active"><a href="javascript:ajaxContacts(\''.$key.'\');">'.$addressbook['{DAV:}displayname'].'</a></li>';
                            if ($i=1){
                                echo '<input type="hidden" name="firstaddressbook" id="firstaddressbook" value="'.$key.'">';
                            }
                        }
                        $i++;
                    }
                    ?>

                    </ul>
                </li>
                 <li>
                    <a href="#"><i class="fa fa-calendar"></i> <span class="nav-label">Calendars</span> <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                    <li><a href="calendars.php?cal=cal1">Cal1</a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </nav>

<div id="page-wrapper" class="gray-bg">
    <div class="row border-bottom">
        <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                <form role="search" class="navbar-form-custom" action="#">
                    <div class="form-group">
                        <input type="text" placeholder="Search for something..." class="form-control" name="contact-search" id="contact-search">
                    </div>
                </form>
            </div>
            <ul class="nav navbar-top-links navbar-right">
                <li>
                    <span class="m-r-sm text-muted welcome-message">Welcome eb+ .</span>
                </li>
                

                <li>
                    <a href="logout.php">
                        <i class="fa fa-sign-out"></i> Log out
                    </a>
                </li>
            </ul>

        </nav>
    </div>
    <div  id="contenteb">
    </div>