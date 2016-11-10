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

//print_r($_POST);
$fn = $_POST['nombre'];

$vcard = new VObject\Component\VCard([
	    'FN'  => $fn
	]);
$vcard->VERSION = '3.0';

//company
if(isset($_POST['company'])){
	$company = $_POST['company'];
	if($company!=''){
		$vcard->ORG = $company;
	}
}

//categories
foreach($arrayCatType as $key=>$cada){
	if(isset($_POST[str_replace(' ', '_', $key)])){
		$categories[] = $key;
	}
}
if(count($categories)!=0){
	$vcard->add('CATEGORIES',$categories);
}

//tels
if(isset($_POST['teltype'])){
	$teltypes = $_POST['teltype'];
	$tels = $_POST['tel'];
	foreach($teltypes as $key=>$teltype){
		$vcard->add('TEL', $tels[$key], ['TYPE' => $teltype]);
	}
}

//urls
if(isset($_POST['url'])){
	$urls=$_POST['url'];
	foreach($urls as $url){
		$vcard->add('URL', $url);
	}
}

//adr
if(isset($_POST['adrtype'])){
	$adrtypes = $_POST['adrtype'];
	$street = $_POST['street'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$adrcountry = $_POST['adrcountry'];
	foreach($adrtypes as $key=>$adrtype){
		$address = array('','',$street[$key], $city[$key], $state[$key], $zip[$key], $adrcountry[$key]);
		$vcard->add('ADR', $address, ['TYPE' => $adrtype]);
	}
}

//emails
if(isset($_POST['emailtype'])){
	$emailtypes = $_POST['emailtype'];
	$emails = $_POST['email'];
	foreach($emailtypes as $key=>$emailtype){
		$vcard->add('EMAIL', $emails[$key], ['TYPE' => $emailtype]);
	}
}


//impps
if(isset($_POST['impptype'])){
	$impptypes = $_POST['impptype'];
	$impps = $_POST['impp'];
	foreach($impptypes as $key=>$impptype){
		$vcard->add('IMPP', $impptype.':'.$impps[$key]);
	}
}
//nickname
if(isset($_POST['nickname'])){
	$nickname = $_POST['nickname'];
	if($nickname!=''){
		$vcard->NICKNAME = $nickname;
	}
}
//birthday
if(isset($_POST['bday'])){
	$bday = $_POST['bday'];
	if($bday != ''){
		$vcard->BDAY = $bday.'T00:00:00';
	}
}
//anniversary
if(isset($_POST['anniversary'])){
	$anniversary = str_replace('-', '',$_POST['anniversary']);
	if($anniversary != ''){

		//$vcard->add('ITEM1.X-ABDATE','','VALUE=DATE-AND-OR-TIME:'.$anniversary.'T000000');
		$vcard->add('ITEM1.X-ABDATE',$anniversary.'T000000',['VALUE'=>'DATE-AND-OR-TIME']);
		$vcard->add('ITEM1.X-ABLABEL','_$!!$_');
		$vcard->add('X-ANNIVERSARY',$anniversary.'T000000',['VALUE'=>'DATE-AND-OR-TIME']);
	}
}
//gender
if(isset($_POST['gender'])){
	$sex = $_POST['gender'];
	if($sex != ''){
		$vcard->SEX = $sex;
	}
}
//notes
if(isset($_POST['notes'])){
	$notes = $_POST['notes'];
	if($notes != ''){
		$vcard->NOTE = $notes;
	}
}
$vcard->REV = date('Ymd').'T'.date('His').'Z';

//salutation
if(isset($_POST['salutation'])){
	$salutation = $_POST['salutation'];
	if($salutation != ''){
		$vcard->{'X-SALUTATION'} = $salutation;
	}
}
//x-spouse
if(isset($_POST['x-spouse'])){
	$xspouse = $_POST['x-spouse'];
	if($xspouse != ''){
		$vcard->add('RELATED',$xspouse,['TYPE'=>'x-spouse']);
	}
}
//child
if(isset($_POST['child'])){
	$child = $_POST['child'];
	if($child != ''){
		$vcard->add('RELATED',$child,['TYPE'=>'child']);
	}
}
//xmsinterests
if(isset($_POST['xmsinterests'])){
	$xmsinterests = $_POST['xmsinterests'];
	if($xmsinterests != ''){
		$vcard->{'X-MS-INTERESTS'} = $xmsinterests;
	}
}
//assistant
if(isset($_POST['assistant'])){
	$assistant = $_POST['assistant'];
	if($assistant != ''){
		$vcard->add('RELATED',$assistant,['TYPE'=>'assistant']);
	}
}
//manager
if(isset($_POST['manager'])){
	$manager = $_POST['manager'];
	if($manager != ''){
		$vcard->add('RELATED',$manager,['TYPE'=>'manager']);
	}
}
//department
if(isset($_POST['department'])){
	$department = $_POST['department'];
	if($department != ''){
		$vcard->ORG .=';'.$department;
	}
}
//jobtitle
if(isset($_POST['jobtitle'])){
	$jobtitle = $_POST['jobtitle'];
	if($jobtitle != ''){
		$vcard->TITLE = $jobtitle;
	}
}
//office
if(isset($_POST['office'])){
	$office = $_POST['office'];
	if($office != ''){
		$vcard->OFFICE = $office;
	}
}
//xemreferredby
if(isset($_POST['xemreferredby'])){
	$xemreferredby = $_POST['xemreferredby'];
	if($xemreferredby != ''){
		$vcard->add('RELATED',$xemreferredby,['TYPE'=>'X-EM-REFERREDBY']);
	}
}
//profession
if(isset($_POST['profession'])){
	$profession = $_POST['profession'];
	if($profession != ''){
		$vcard->ROLE = $profession;
	}
}
//xembankaccount
if(isset($_POST['xembankaccount'])){
	$xembankaccount = $_POST['xembankaccount'];
	if($xembankaccount != ''){
		$vcard->{'X-EM-BANKACCOUNT'} = $xembankaccount;
	}
}
//fburl
if(isset($_POST['fburl'])){
	$fburl = $_POST['fburl'];
	if($fburl != ''){
		$vcard->FBURL = $fburl;
	}
}
//caluri
if(isset($_POST['caluri'])){
	$caluri = $_POST['caluri'];
	if($caluri != ''){
		$vcard->CALURI = $caluri;
	}
}
//caladruri
if(isset($_POST['caladruri'])){
	$caladruri = $_POST['caladruri'];
	if($caladruri != ''){
		$vcard->CALADRURI = $caladruri;
	}
}
//lang
if(isset($_POST['lang'])){
	$lang = $_POST['lang'];
	if($lang != ''){
		$vcard->LANG = $lang;
	}
}






//echo $vcard->serialize();
$nameVcf = $_POST['vcf'];
if($nameVcf == ''){
	$nameVcf = generate_vcard_id();
}
$new = $client->request('PUT',$urlContacts.$nameVcf,$vcard->serialize());


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
		throw new Exception($e->getMessage(), Vars::EXCEPTION_COULD_NOT_GENERATE_NEW_VCARD_ID);
	}
}