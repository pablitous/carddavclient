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

//para editar
$client->proppatch('/dav.php/addressbooks/'.$username.'/default', array(
    '{DAV:}displayname' => 'lista de pablitous',
),1);

//recorro las adressbooks
$i=0;
foreach($addressbooks as $key=>$addressbook){
	//print_r($addressbook);
	if($i>0){
		echo '<a href="'.$serverurl.$key.'">'.$addressbook['{DAV:}displayname'].'</a><br>';
		$contacts = $client->propfind($key, array(),1);
		$j=0;
		$contactsCount = count($contacts)-1;
		//print_r($contacts);
		
		foreach($contacts as $keyConctact=>$contact){
			if($j>0){
				$filevcf = $serverurl.$keyConctact;
				//$filevcf = 'http://pablitous:ofertones@serverpps.tk'.$keyConctact;
				//echo $filevcf;
				$filevcf = $client->request('GET',$filevcf);
				//print_r($filevcf);die();
				$vcard = VObject\Reader::read(
					$filevcf['body']
					//fopen($filevcf,'r')
				);
				echo $vcard->FN.' - '.$vcard->EMAIL;
				//print_r($vcard->TEL);
				foreach($vcard->TEL as $tel) {
					//print_r($tel);
				    echo $vcard->{$tel->group . '.X-ABLABEL'}, ": ";
				    echo $tel, "\n";
				}
				//print_r($vcard);
				echo '<br>';
				echo '<br>';
			}
			$j++;
		}
	}
	/**/
	//echo $vcard->FN;
	echo '<br>';
	$i++;
}

//print_r($client->addressBooks);

//ver opciones
//print_r($client->request('GET'));

//ver opciones
//print_r($client->options());
//ver addessbook
//print_r($client->options());
?>