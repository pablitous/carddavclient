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

$serverurl = $_SESSION['serverurl'];
$username = $_SESSION['username'];
$client = $_SESSION['client'];
$urlContacts = $_SESSION['urlcontacts'];
$names = array('Allison','Arthur','Ana','Alex','Arlene','Alberto','Barry','Bertha','Bill','Bonnie','Bret','Beryl','Chantal','Cristobal','Claudette','Charley','Cindy','Chris','Dean','Dolly','Danny','Danielle','Dennis','Debby','rin','Edouard','Erika','Earl','Emily','Ernesto','Felix','Fay','Fabian','Frances','Franklin','Florence','Gabielle','Gustav','Grace','Gaston','Gert','Gordon','Humberto','Hanna','Henri','Hermine','Harvey','Helene','Iris','idore','Isabel','Ivan','Irene','Isaac','Jerry','Josephine','Juan','Jeanne','Jose','Joyce','Karen','Kyle','Kate','Karl','Katrina','Kirk','Lorenzo','Lili','Larry','Lisa','Lee','Leslie','Michelle','Marco','Mindy','Maria','Michael','oel','Nana','Nicholas','Nicole','Nate','Nadine','Olga','Omar','Odette','Otto','Ophelia','Oscar','Pablo','Paloma','Peter','Paula','Philippe','Patty','Rebekah','Rene','Rose','Richard','Rita','Rafael','Sebastien','Sally','Sam','Shary','Stan','Sandy','Tanya','Teddy','Teresa','Tomas','Tammy','Tony','Van','Vicky','Victor','Virginie','Vince','Valerie','Wendy','Wilfred','Wanda','Walter','Wilma','William','Kumiko','Aki','Miharu','Chiaki','Michiyo','Itoe','Nanaho','Reina','Emi','Yumi','Ayumi','Kaori','Sayuri','Rie','Miyuki','Hitomi','Naoko','Miwa','Etsuko','Akane','Kazuko','Miyako','Youko','Sachiko','Mieko','Toshie','Junko');

for($j=0;$j<4096;$j++){
	$vcard = new VObject\Component\VCard([
	    'FN'  => generateRandomNames(),
	    'TEL' => generateRandomNumber()
	]);
	$vcard->VERSION = '3.0';
	$vcard->add(
	    'EMAIL',
	    generateRandomEmail().'@gmail.com',
	    [
	        'type' => ['home', 'work'],
	        'pref' => 1,
	    ]
	);
	//echo $vcard->serialize();
	$nameVcf = generate_vcard_id();

	$new = $client->request('PUT',$urlContacts.$nameVcf,$vcard->serialize());
//print_r($new);
}
function generate_vcard_id(){
	global $serverurl;
	global $client;
	global $urlContacts;
	$vcard_id = null;
	$vcard_id_chars = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f');
	for ($number = 0; $number < 32; $number ++)
	{
		if ($number == 8 || $number == 13 || $number == 18 || $number == 23)
		{
			$vcard_id .= '-';
		}
		else
		{
			$vcard_id .= $vcard_id_chars[mt_rand(0, (count($vcard_id_chars) - 1))];
		}
	}
	try
	{
		$filevcf = $serverurl.$urlContacts.$vcard_id.'.vcf';
		$result = $client->request('GET',$filevcf);
		//print_r($result);die();
		//$result = $carddav->query($this->url . $vcard_id . '.vcf', 'GET');
		if ($result['statusCode'] !== 404)
		{
			$vcard_id = generate_vcard_id();
		}
		return $vcard_id.'.vcf';
	}
	catch (Exception $e)
	{
		throw new Exception($e->getMessage(), self::EXCEPTION_COULD_NOT_GENERATE_NEW_VCARD_ID);
	}
}

function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateRandomNames() {
	global $names;
    $namesLength = count($names);
    $randomString = $names[rand(0, $namesLength - 1)];
    $randomString .= ' '.$names[rand(0, $namesLength - 1)];
    return $randomString;
}
function generateRandomEmail() {
	global $names;
    $namesLength = count($names);
    $randomString = $names[rand(0, $namesLength - 1)];
    $randomString .= '.'.$names[rand(0, $namesLength - 1)];
    return $randomString;
}

function generateRandomNumber($length = 10) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}