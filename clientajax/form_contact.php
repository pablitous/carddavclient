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

$urlVcf = $_POST['urlvcf'];
$name = '';
$nameVcf = '';
$company = '';
$department = '';
if($urlVcf!='new'){
	$filevcf = $serverurl.$urlVcf;
	//$contacts = $client->propfind($urlContacts, array(),1);
	$filevcf = $client->request('GET',$filevcf);

	$vcard = VObject\Reader::read(
	    $filevcf['body']
	);
	//echo $vcard->serialize();
	$rutaArchivo = explode('/',$urlVcf);
	$nameVcf = $rutaArchivo[count($rutaArchivo)-1];

	if($vcard->CATEGORIES != ''){
	    $cat = $vcard->CATEGORIES;
	}else{
	    $cat = '-';
	}
	if(isset($vcard->FN)){
		$name = $vcard->FN;
	}
	if(isset($vcard->ORG)){
		$companyDepartment = explode(';',$vcard->ORG);
		$company = $companyDepartment[0];
		if(isset($companyDepartment[1])){
			$department = $companyDepartment[1];
		}
	}
}

echo '
<div class="tabs-container">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab-general" aria-expanded="true">General</a></li>
        <li class=""><a data-toggle="tab" href="#tab-tel-web" aria-expanded="false">Tel & Web</a></li>
        <li class=""><a data-toggle="tab" href="#tab-adr" aria-expanded="false">Adr</a></li>
        <li class=""><a data-toggle="tab" href="#tab-email-im" aria-expanded="false">Email & IM</a></li>
        <li class=""><a data-toggle="tab" href="#tab-details" aria-expanded="false">Details</a></li>
        <li class=""><a data-toggle="tab" href="#tab-other" aria-expanded="false">Other</a></li>
    </ul>
    <div class="tab-content">
        <div id="tab-general" class="tab-pane active">
            <div class="panel-body">
            <input type="hidden" name="vcf" id="vcf" value="'.$nameVcf.'">';
echo '<div class="form-group"><label>Name</label> <input type="text" placeholder="Enter the name" class="form-control" name="nombre" value="'.$name.'"></div>';
echo '<div class="form-group"><label>Comapny</label> <input type="text" placeholder="Enter the company" class="form-control" name="company" value="'.$company.'"></div>';


echo '<div class="form-group"><label>Category</label>';
if($urlVcf!='new'){
	$selectedCategories = explode(',', $vcard->CATEGORIES);
}else{
	$selectedCategories = array();
}
echo returnCheckboxes('category',$arrayCatType,$selectedCategories);
//echo returnSelectMultiple('category',$arrayCatType,$selectedCategories);
echo '</div>';

echo '		</div>
        </div>
        <div id="tab-tel-web" class="tab-pane">
            <div class="panel-body phone-body"><label>Phones</label><a href="javascript:addPhone();"> <i class="fa fa-plus-square-o"></i></a>';
//tels

if(isset($vcard->TEL)){
	foreach($vcard->TEL as $tel) {
		if ($tel['TYPE']==''){
			$teltype = 'OTHER';
		}else{
			$teltype = $tel['TYPE'];
		}
		echo '<div class="form-inline">';
		echo returnSelect('teltype[]',$arrayTelType,$teltype);
	    echo '<input type="text" class="form-control" name="tel[]" value="'.$tel.'"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	}
}
echo '</div><div class="panel-body web-body"><label>Webs</label><a href="javascript:addWeb();"> <i class="fa fa-plus-square-o"></i></a>';
if(isset($vcard->URL)){
	foreach($vcard->URL as $url) {
		echo '<div class="form-inline">';
	    echo '<input type="text" class="form-control" name="url[]" value="'.$url.'" size="50"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	}
}
echo '		</div>
        </div>
        <div id="tab-adr" class="tab-pane">
            <div class="panel-body">
            	<div class="panel-group adr-group" id="accordion">
            	<h3>Addresses<a href="javascript:addAdr();"> <i class="fa fa-plus-square-o"></i></a></h3>';
 
        
if(isset($vcard->ADR)){
	$i = 0;
	foreach($vcard->ADR as $adr) {
		if ($adr['TYPE']==''){
			$adrtype = 'OTHER';
		}else{
			$adrtype = $adr['TYPE'];
		}
		($i==0)?$in='in':$in='';
				echo '<div class="panel panel-default">
					    <div class="panel-heading">
					        <h5 class="panel-title">
					            <a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$i.'" aria-expanded="true" class="">'.$arrayAdrType[(string)$adrtype].' </a><i class="fa fa-minus-square-o" parents="3"></i>
					        </h5>
					    </div>
					    <div id="collapse'.$i.'" class="panel-collapse collapse '.$in.'" aria-expanded="true">';
				$arrayAdr = explode(';',$adr);
				echo '	        <div class="panel-body">';
				echo '				<div class="form-inline"><label>Type</label> ';
				echo 					returnSelect('adrtype[]',$arrayAdrType,$adrtype);
				
			    echo '				</div>';
			    echo '				<div class="form-inline">';
			    echo '					<label>Street</label> <input type="text" class="form-control" name="street[]" value="'.$arrayAdr[2].'">';
			    echo '					<label>City</label> <input type="text" class="form-control" name="city[]" value="'.$arrayAdr[3].'">';
			    
			    echo '				</div>';
			    echo '				<div class="form-inline">';
			    echo '					<label>State</label> <input type="text" class="form-control" name="state[]" value="'.$arrayAdr[4].'">';
			    echo '					<label>ZIP</label> <input type="text" class="form-control" name="zip[]" value="'.$arrayAdr[5].'">';
			    echo '				</div>';
			    echo '				<div class="form">';
			    echo '					<label>Country</label> '.returnSelect('adrcountry[]',$countries,$arrayAdr[5]);
			    echo '				</div>';
				echo '			</div>
				    		</div>
						</div>';
					$i++;
	}
}

echo '			</div>
			</div>
        </div>
        <div id="tab-email-im" class="tab-pane">
            <div class="panel-body email-body"><label>Emails</label><a href="javascript:addEmail();"> <i class="fa fa-plus-square-o"></i></a>';

if(isset($vcard->EMAIL)){
	foreach($vcard->EMAIL as $email) {
		if ($email['TYPE']==''){
			$emailtype = 'OTHER';
		}else{
			$emailtype = $email['TYPE'];
		}
		echo '<div class="form-inline">';
		echo returnSelect('emailtype[]',$arrayEmailType,$emailtype);
	    echo '<input type="email" class="form-control" name="email[]" value="'.$email.'" size="44"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	}
}
echo '</div><div class="panel-body impp-body"><label>IM</label><a href="javascript:addIMPP();"> <i class="fa fa-plus-square-o"></i></a>';

if(isset($vcard->IMPP)){
	foreach($vcard->IMPP as $impp) {
		echo '<div class="form-inline">';
		$selectedImpp = explode(':', $impp);
		echo returnSelect('impptype[]',$arrayImppType,$selectedImpp[0]);
	    echo '<input type="text" class="form-control" name="impp[]" value="'.$selectedImpp[1].'" size="50"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	}
}
echo '		</div>
        </div>
        <div id="tab-details" class="tab-pane">
            <div class="panel-body">';
//bday comes like 986-12-17T19:48:18
//i need dd/mm/aaaa
$anniversary = '';
$bday = null;
$nickname = '';
$gender = '';
$note = '';
$assistant = '';
$manager = '';
$xspouse = '';
$child = '';
$XEMREFERREDBY = '';
$salutation = '';
$xmsinterests = '';
$xmsinterests = '';
$jobtitle = '';
$xmsinterests = '';
$office = '';
$profession = '';
$xembankaccount = '';
$caladruri = '';
$caluri = '';
$fburl = '';
$lang = '';
if($urlVcf!='new'){
	$bday = $vcard->BDAY;
	$bday = substr($bday, 0,10);
	$nickname = $vcard->NICKNAME;
	$gender = (string)$vcard->SEX;
	$note = $vcard->NOTE;
	if(isset($vcard->{'X-ANNIVERSARY'})){
		$anniversary = date('Y-m-d',strtotime($vcard->{'X-ANNIVERSARY'}));
	}
	if ($vcard->RELATED != ''){
		foreach($vcard->RELATED as $value) {
			switch($value['TYPE']){
				case 'assistant':
					$assistant = $value;
					break;
				case 'manager':
					$manager = $value;
					break;
				case 'x-spouse':
					$xspouse = $value;
					break;
				case 'child':
					$child = $value;
					break;
				case 'X-EM-REFERREDBY':
					$XEMREFERREDBY = $value;
					break;
			}
		}
	}
	if(isset($vcard->{'X-SALUTATION'})){
		$salutation = $vcard->{'X-SALUTATION'};
	}
	if(isset($vcard->{'X-MS-INTERESTS'})){
		$xmsinterests = $vcard->{'X-MS-INTERESTS'};
	}
	if(isset($vcard->TITLE)){
		$jobtitle = $vcard->TITLE;
	}
	if(isset($vcard->OFFICE)){
		$office = $vcard->OFFICE;
	}
	if(isset($vcard->ROLE)){
		$profession = $vcard->ROLE;
	}
	if(isset($vcard->{'X-EM-BANKACCOUNT'})){
		$xembankaccount = $vcard->{'X-EM-BANKACCOUNT'};
	}
	if(isset($vcard->CALADRURI)){
		$caladruri = $vcard->CALADRURI;
	}
	if(isset($vcard->CALURI)){
		$caluri = $vcard->CALURI;
	}
	if(isset($vcard->FBURL)){
		$fburl = $vcard->FBURL;
	}
	if(isset($vcard->LANG)){
		$lang = $vcard->LANG;
	}

}

echo '			<div class="col-md-6">';
echo '				<div class="form-group"><label>Nickname</label> <input type="text" placeholder="Enter the nickname" class="form-control" name="nickname" value="'.$nickname.'"></div>';
echo '				<div class="form-group date"><label>Birthday</label><input type="date" name="bday" class="form-control" value="'.$bday.'"></div>';
echo '				<div class="form-group date"><label>Anniversary</label><input type="date" name="anniversary" class="form-control" value="'.$anniversary.'"></div>';
echo '				<div class="form-group"><label>Gender</label>'. returnSelect('gender',$arrayGender,$gender).'</div>';
echo '			</div>';
echo '			<div class="col-md-6">';
echo '				<div class="form-group"><label>Salutation</label> <input type="text" placeholder="Enter the salutation" class="form-control" name="salutation" value="'.$salutation.'"></div>';
echo '				<div class="form-group"><label>Spouse</label> <input type="text" placeholder="Enter the spouse" class="form-control" name="x-spouse" value="'.$xspouse.'"></div>';
echo '				<div class="form-group"><label>Child</label> <input type="text" placeholder="Enter the child quantity" class="form-control" name="child" value="'.$child.'"></div>';
echo '				<div class="form-group"><label>Hobbies</label> <input type="text" placeholder="Enter the hobbies" class="form-control" name="xmsinterests" value="'.$xmsinterests.'"></div>';
echo '			</div>';
echo '			<div class="col-md-12">';
echo '				<div class="form-group"><label>Notes</label> <textarea class="form-control" name="notes" rows="5">'.$note.'</textarea></div>';
echo '			</div>';
echo '		</div>
        </div>';
echo '  <div id="tab-other" class="tab-pane">
            <div class="panel-body">';
echo '			<div class="col-md-6">';
echo '				<div class="form-group"><label>Assistant Name</label> <input type="text" placeholder="Enter the assistant name" class="form-control" name="assistant" value="'.$assistant.'"></div>';
echo '				<div class="form-group"><label>Manager Name</label> <input type="text" placeholder="Enter the manager name" class="form-control" name="manager" value="'.$manager.'"></div>';
echo '				<div class="form-group"><label>Department</label> <input type="text" placeholder="Enter the Department" class="form-control" name="department" value="'.$department.'"></div>';
echo '				<div class="form-group"><label>Job Title</label> <input type="text" placeholder="Enter the Job Title" class="form-control" name="jobtitle" value="'.$jobtitle.'"></div>';
echo '				<div class="form-group"><label>Office</label> <input type="text" placeholder="Enter the Office" class="form-control" name="office" value="'.$office.'"></div>';
echo '				<div class="form-group"><label>Referred by</label> <input type="text" placeholder="Enter the referred by" class="form-control" name="xemreferredby" value="'.$XEMREFERREDBY.'"></div>';
echo '			</div>';
echo '			<div class="col-md-6">';
echo '				<div class="form-group"><label>Profession</label> <input type="text" placeholder="Enter the Profession" class="form-control" name="profession" value="'.$profession.'"></div>';
echo '				<div class="form-group"><label>Bank Account</label> <input type="text" placeholder="Enter the bank account" class="form-control" name="xembankaccount" value="'.$xembankaccount.'"></div>';
echo '				<div class="form-group"><label>FreeBussy URL</label> <input type="text" placeholder="Enter the url" class="form-control" name="fburl" value="'.$fburl.'"></div>';
echo '				<div class="form-group"><label>Calendar URI</label> <input type="text" placeholder="Enter the url" class="form-control" name="caluri" value="'.$caluri.'"></div>';
echo '				<div class="form-group"><label>Calendar Request URI</label> <input type="text" placeholder="Enter the url" class="form-control" name="caladruri" value="'.$caladruri.'"></div>';
echo '				<div class="form-group"><label>Languages</label> <input type="text" placeholder="Enter the languages" class="form-control" name="lang" value="'.$lang.'"></div>';
echo '			</div>';

echo '		</div>
        </div>';
echo '</div>
    
</div>';


function returnSelect($name, $arrayType, $default=null){
	$select = '<select class="form-control" name="'.$name.'">';
	foreach($arrayType as $key => $value){
		if(isset($default)){
			if($default == $key){
				$select .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$select .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}else{
			$select = '<option value="'.$key.'">'.$value.'</option>';
		}
	}			
	$select .= '</select>';
	return $select;
}

function returnSelectMultiple($name, $arrayType, $defaultArray=null){
	$select = '<select class="form-control" name="'.$name.'" multiple size=12>';
	foreach($arrayType as $key => $value){
		if(isset($defaultArray)){
			if(in_array($key, $defaultArray)){
				$select .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$select .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}else{
			$select = '<option value="'.$key.'">'.$value.'</option>';
		}
	}			
	$select .= '</select>';
	return $select;
}

function returnCheckboxes($name, $arrayType, $defaultArray = null, $inline=0){
	if($inline ==1){
		$checkboxinline = 'checkbox-inline';
	}else{
		$checkboxinline= '';
	}
	$checkbox = '<fieldset>';
	foreach($arrayType as $key => $value){
		$checked = '';
		if(isset($defaultArray)){
			if(in_array($key, $defaultArray)){
				$checked = 'checked';
			}
		}
		$checkbox .= '	<div class="checkbox '.$checkboxinline.' checkbox-'.str_replace(' ', '', $key).'" >
				        <input id="'.$key.'" name="'.$key.'" type="checkbox" '.$checked.'>
				        <label for="'.$key.'">
				            '.$value.'
				        </label>
				    </div>';
	}			
	$checkbox .= '<fieldset>';
    return $checkbox;
}
?>
<script>
function loadRemove(){
	$(".fa-minus-square-o").click(function(){
		prenttoremove = $(this).attr('parents');
		$(this).parents()[prenttoremove-1].remove();
	});
}

loadRemove();

function addPhone(){
	input = '<div class="form-inline">';
	input += '<select class="form-control" name="teltype[]">';
	arrayTelTypeKey = ['CELL','WORK,VOICE','HOME,VOICE','CAR','WORK','OTHER','WORK,FAX','HOME,FAX','FAX','PAGER','ISDN']
	arrayTelTypeDescription = ['Mobile','Work','Home','Car','Company','Other','Fax','Fax Home','Fax Other','Pager','ISDN'];
	for (i = 0; i < arrayTelTypeKey.length; i++) { 
	    input += '<option value="'+arrayTelTypeKey[i]+'">'+arrayTelTypeDescription[i]+'</option>';
	}
	input +='</select>';
	input +='<input type="text" class="form-control" name="tel[]"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	$('.phone-body').append(input);

	loadRemove();
}
function addWeb(){
	input = '<div class="form-inline"><input type="text" class="form-control" name="url[]" size="50"> <i class="fa fa-minus-square-o" parents="1"></i></div>'
	$('.web-body').append(input);
	loadRemove();
}

function addEmail(){
	input = '<div class="form-inline">';
	input += '<select class="form-control" name="emailtype[]">';
	arrayEmailTypeKey = ['PREF','WORK','HOME'];
	arrayEmailTypeDescription = ['Correo Electrónico','Trabajo','Domicilio'];
	for (i = 0; i < arrayEmailTypeKey.length; i++) { 
	    input += '<option value="'+arrayEmailTypeKey[i]+'">'+arrayEmailTypeDescription[i]+'</option>';
	}
	input +='</select>';
	input +='<input type="text" class="form-control" name="email[]" size="44"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	$('.email-body').append(input);

	loadRemove();
}

function addIMPP(){
	input = '<div class="form-inline">';
	input += '<select class="form-control" name="impptype[]">';
	arrayEmailTypeKey =['xmpp','icq','skype','msn','aim','google','gadu','irc','ymsgr'];
	arrayEmailTypeDescription = ['Jabber','ICQ','Skype','MSN','AIM','GoogleTalk','GaduGadu','IRC','Yahoo'];
	for (i = 0; i < arrayEmailTypeKey.length; i++) { 
	    input += '<option value="'+arrayEmailTypeKey[i]+'">'+arrayEmailTypeDescription[i]+'</option>';
	}
	input +='</select>';
	input +='<input type="text" class="form-control" name="impp[]" size="50"> <i class="fa fa-minus-square-o" parents="1"></i></div>';
	$('.impp-body').append(input);

	loadRemove();
}
var qadr = 100;
function addAdr(){
	input = '<div class="panel panel-default">';
	input += '	<div class="panel-heading">';
	input += '		<h5 class="panel-title">';
	input += '			<a data-toggle="collapse" data-parent="#accordion" href="#collapse'+qadr+'" aria-expanded="true" class="">New </a><i class="fa fa-minus-square-o" parents="3"></i>';
	input += '		</h5>';
	input += '	</div>';
	input += '	<div id="collapse'+qadr+'" class="panel-collapse collapse " aria-expanded="true">';
	input += '		<div class="panel-body">';
	input += '			<div class="form-inline"><label>Type</label> ';
	input += '				<select class="form-control" name="adrtype[]">';
				arrayAdrTypeKey =['WORK','HOME','OTHER'];
				arrayAdrTypeDescription = ['Work','Home','Address'];
				for (i = 0; i < arrayAdrTypeKey.length; i++) { 
				   	input += '<option value="'+arrayAdrTypeKey[i]+'">'+arrayAdrTypeDescription[i]+'</option>';
				}
	input +='				</select>';
	input += '			</div>';
	input += '			<div class="form-inline">';
	input += '				<label>Street</label> <input type="text" class="form-control" name="street[]">';
	input += '				<label>City</label> <input type="text" class="form-control" name="city[]" >';
	input += '			</div>';
	input += '			<div class="form-inline">';
	input += '				<label>State</label> <input type="text" class="form-control" name="state[]">';
	input += '				<label>ZIP</label> <input type="text" class="form-control" name="zip[]">';
	input += '			</div>';
	input += '			<div class="form">';
	input += '				<label>Country</label> ';
	input += '				<select class="form-control" name="adrcountry[]">';
				arrayCountries =["Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"];

				for (i = 0; i < arrayCountries.length; i++) { 
				    input += '<option value="'+arrayCountries[i]+'">'+arrayCountries[i]+'</option>';
				}
	input +='				</select>';
	input +='			</div>';
	input +='		</div>';
	input +='	</div>';
	input +='</div>';

	$('.adr-group').append(input);
	qadr++;
	loadRemove();
}

</script>