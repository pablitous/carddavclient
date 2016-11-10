<?php
include 'config.php';

include 'functions.php';

use Sabre\DAV\Client;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAV\PropPatch;
use Sabre\VObject;

include '../sabredav/vendor/autoload.php';
include 'session.php';
include 'header.php';

$serverurl = $_SESSION['serverurl'];
$username = $_SESSION['username'];
$client = $_SESSION['client'];

//para traerme todos las addressbooks
$addressbooks = $client->propfind('/dav.php/addressbooks/'.$username.'/', array(
    '{DAV:}displayname',
    '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description'
),1);

include 'menu.php';


include 'footer.php';
?>
<script type="text/javascript">
function ajaxContacts(url=null, pag=null,searchFilter='',startcharfilter = null){
	//alert(url);
	if(!url){
		data = {page: pag, searchfilter:searchFilter, startcharfilter:startcharfilter};
	}else{
		data = {urlcontacts: url, page: pag, searchfilter:searchFilter, startcharfilter:startcharfilter};
	}
	datos = $.get('contacts.php', data,function(result) {
        $('#contenteb').html(datos.responseText);
    }).fail(function(a,b,c) {
        console.log(datos);
        });
}

url = $('#firstaddressbook').val();
ajaxContacts(url);
	
</script>>
