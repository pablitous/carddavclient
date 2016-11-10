<?php
include 'config.php';

include 'functions.php';

use Sabre\DAV\Client;
use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\DAV\PropPatch;
use Sabre\VObject;
use Sabre\HTTP;

include '../sabredav/vendor/autoload.php';
include 'session.php';
//include 'header.php';

$serverurl = $_SESSION['serverurl'];
$username = $_SESSION['username'];
$client = $_SESSION['client'];
if(isset($_GET['urlcontacts'])){
    $urlContacts = $_GET['urlcontacts'];
    $_SESSION['urlcontacts'] = $urlContacts;
}else{
    $urlContacts= $_SESSION['urlcontacts'];
}

if(!isset($_SESSION['addressbook'])){
    //para traerme todos las addressbooks
    $addressbooks = $client->propfind('/dav.php/addressbooks/'.$username.'/', array(
        '{DAV:}displayname',
        '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description',
        '{DAV:}sync-token'
    ),1);
    $_SESSION['addressbook'] = $addressbooks;
}else{
    $addressbooks =$_SESSION['addressbook'];
}
$syncToken = $addressbooks[$urlContacts]['{DAV:}sync-token'];
//include 'menu.php';
?>
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-9">
                    <h2>Contacts <a href="#" data-toggle="modal" data-target="#myModal" id="new"><i class="fa fa-plus-square" style="margin-left: 10px;"></i></a></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="./">Home</a>
                        </li>
                        <li>
                            AddressBooks
                        </li>
                        <li class="active">
                            <strong><?=$addressbooks[$urlContacts]['{DAV:}displayname']?></strong>
                        </li>
                    </ol>
                </div>
            </div>
                <?php
                
                //echo $urlContacts;
                
                if(!isset($_SESSION['contactlist'])){
                    $queryContacts = '<card:addressbook-query xmlns:d="DAV:" xmlns:card="urn:ietf:params:xml:ns:carddav">
                                <d:prop>
                                    <d:getetag />
                                    <card:address-data />
                                </d:prop>
                            </card:addressbook-query>';
                    $contacts = $client->request('REPORT',$urlContacts,$queryContacts,array('Depth'=>'1'));
                    $contacts = createContactsArray($client->parseMultiStatus($contacts['body']));
                    usort($contacts,'compare');
                    $_SESSION['contactlist'] = $contacts;
                }else{
                    $queryContactsChanged = '<d:sync-collection xmlns:d="DAV:" xmlns:card="urn:ietf:params:xml:ns:carddav">
                                                <d:sync-token>'.$syncToken.'</d:sync-token>
                                                <d:sync-level>1</d:sync-level>
                                                <d:prop>
                                                    <d:getetag/>
                                                    <card:address-data />
                                                </d:prop>
                                            </d:sync-collection>';
                    $contactsChanged = $client->request('REPORT',$urlContacts,$queryContactsChanged,array('Depth'=>'1'));
                    $contactsChanged = $client->parseMultiStatus($contactsChanged['body']);

                    $contacts = $_SESSION['contactlist'];
                    //print_r($contacts);
                    //print_r($_SESSION['contactlist']);
                    if (count($contactsChanged) != 0){

                        //update contactlist in session
                        foreach($contactsChanged as $key=>$value){
                            $clave = array_search(rtrim(ltrim($key)), array_column($contacts, 'vcf'));
                            if(count($contactsChanged[$key]) == 0){
                                //delete element from contactlist in session;
                                unset($contacts[$clave]);
                            }else{
                                //add new contactlist in session
                                $vcard = VObject\Reader::read(
                                    $value['200']['{urn:ietf:params:xml:ns:carddav}address-data']
                                );
                                if(!is_numeric($clave)){
                                    $contacts[] = array('vcf'=>$key,'name' => (string)$vcard->FN, 'vcard'=>$value['200']['{urn:ietf:params:xml:ns:carddav}address-data']);
                                }else{
                                    $contacts[$clave] = array('vcf'=>$key,'name' => (string)$vcard->FN, 'vcard'=>$value['200']['{urn:ietf:params:xml:ns:carddav}address-data']);
                                }
                                
                            }
                        }

                        usort($contacts,'compare');
                        //update the token
                        $addressbooks = $client->propfind('/dav.php/addressbooks/'.$username.'/', array(
                                                        '{DAV:}displayname',
                                                        '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description',
                                                        '{DAV:}sync-token'
                                                    ),1);
                        $_SESSION['addressbook'] = $addressbooks;
                        //sprint_r($contactsChanged);
                    $_SESSION['contactlist'] = $contacts;
                    }
                }
                //print_r($contacts);die();
                //search/filter
                if (isset($_GET['page']) && $_GET['page'] > 0 ){
                    $actualPage = $_GET['page'];
                }else{
                    $actualPage = 1;
                }
                echo '<input type="hidden" name="actualpage" id="actualpage" value="'.$actualPage.'">';

                //seach filter
                if(isset($_GET['searchfilter']) || isset($_SESSION['searchfilter'])){
                    if(isset($_GET['searchfilter'])){
                        $searchFilter = $_GET['searchfilter'];
                    }else{
                        $searchFilter = $_SESSION['searchfilter'];
                    }
                    if($searchFilter == ''){
                        $contacts = $_SESSION['contactlist'];
                        unset($_SESSION['contactsFiltered']);
                        unset($_SESSION['searchfilter']);
                    }else{
                        if(isset($_SESSION['searchfilter'])){
                            if($searchFilter==$_SESSION['searchfilter']){
                                $contacts = $_SESSION['contactsFiltered'];
                            }else{
                                //print_r($contacts);
                                $contacts = generateFilteredContacts($searchFilter);
                                //print_r(array_search(rtrim(ltrim($searchFilter)), array_column($contacts, 'vcard')));
                                $_SESSION['contactsFiltered'] = $contacts;
                            }
                        }else{
                            $_SESSION['searchFilter'] = $searchFilter;
                            $contacts = generateFilteredContacts($searchFilter);
                            //print_r(array_search(rtrim(ltrim($searchFilter)), array_column($contacts, 'vcard')));
                            $_SESSION['contactsFiltered'] = $contacts;
                        }
                    }
                    //$idsQuery = array_search(rtrim(ltrim($searchFilter)), array_column($contacts, 'vcard'));

                }
                //start letter filter
                if (isset($_GET['startcharfilter']) || isset($_SESSION['startcharfilter'])){
                    if(isset($_GET['startcharfilter'])){
                        $startcharfilter = $_GET['startcharfilter'];
                        $_SESSION['startcharfilter'] = $startcharfilter;
                    }else{
                        $startcharfilter = $_SESSION['startcharfilter'];
                    }
                    if($startcharfilter == ''){
                        unset($_SESSION['startcharfilter']);
                    }else{
                        if(isset($_SESSION['startcharfilter'])){
                                
                                $contacts = generateFilteredContactsStartChar($_SESSION['startcharfilter']);
                        }
                    }
                }
                echo '<input type="hidden" name="startcharfilter" id="startcharfilter" value="'.$startcharfilter.'">';
                
                function generateFilteredContacts($filter){
                    global $contacts;
                    $contactsFiltered = array_filter($contacts, function($v) { global $searchFilter; return strstr(strtolower($v['vcard']), strtolower($searchFilter)) !== false;});
                    usort($contactsFiltered,'compare');
                    return $contactsFiltered;
                }
                function generateFilteredContactsStartChar($startcharfilter){
                    global $contacts;
                    $contactsFiltered = array_filter($contacts, function($v) { global $startcharfilter; return substr(strtolower($v['name']),0,1) === $startcharfilter;});
                    usort($contactsFiltered,'compare');
                    return $contactsFiltered;

                }
//die();

returnContacts();
modalContact();

function paginateAbc($startcharfilter,$filter){
    $arrAbc = array("all","a","b","c","d","e","f","g","h","i","j","k","l","m","n","ñ","o","p","q","r","s","u","v","w","x","y","z","0","1","2","3","4","5","6","7","8","9");
    $return = '<ul class="pagination" style="margin:0;">';
    foreach ($arrAbc as $eachAbc){
        if($eachAbc == "all"){
            $eachAbcKey = '';
        }else{
            $eachAbcKey = $eachAbc;
        }
        if($startcharfilter == $eachAbcKey){
            $return .= '<li class="active"><a href="javascript:ajaxContacts(null,null,\''.$filter.'\',\''.$eachAbcKey.'\')">'.mb_strtoupper($eachAbc).'</a></li>';
        }else{
            $return .= '<li><a href="javascript:ajaxContacts(null,null,\''.$filter.'\',\''.$eachAbcKey.'\')">'.mb_strtoupper($eachAbc).'</a></li>';
        }
       
    }
    $return .= '</ul>';
    return $return;
}

function returnContacts(){
    global $arrayCatTypeColors;
    global $arrayCatType;
    global $contacts;
    global $actualPage;
    global $contactsPerPage;
    global $searchFilter;
    global $arrayTelType;
    global $startcharfilter;

    $totalContacts = count($contacts);
    $adjacentLinks = 2;
    $totalPages = ceil($totalContacts/$contactsPerPage);

    $contactsMin = ($contactsPerPage * $actualPage) - $contactsPerPage;
    $contactsMax = $contactsPerPage * $actualPage;
    if($totalContacts < $contactsMax){
        $contactsMax = $totalContacts;
    }

    $pagination = paginate($contactsPerPage, $actualPage, $totalContacts, $totalPages, './contacts.php',$searchFilter, $startcharfilter);

    echo '  <div class="wrapper wrapper-content animated fadeInRight" style="padding:10px 10px 0px;">
                <div class="row text-center">
                    <div class="" >';
    echo                $pagination;
    echo '          </div>';
    echo '           <div class=""><label>Showing: '.$contactsMin.' to '.$contactsMax.' of a total of '.$totalContacts.'</label></div>';
    echo '          <div class="" >';
    echo                paginateAbc($startcharfilter, $searchFilter);
    echo '          </div>';
    echo '      </div>
            </div>';
            
    echo '  <div class="wrapper wrapper-content animated fadeInRight" style="padding:10px 20px 10px;">
                 <div class="row contactoos">';
 
    for($i=$contactsMin;$i<$contactsMax;$i++){
    //foreach($contacts as $keyConctact=>$contact){
        //print_r($contact['200']['{urn:ietf:params:xml:ns:carddav}address-data']);die();
        /*$filevcf = $serverurl.$keyConctact;
        $filevcf = $client->request('GET',$filevcf);*/
        /*
        $vcard = VObject\Reader::read(
            $filevcf['body']
        );*/

        $vcard = VObject\Reader::read(
            //$contact['200']['{urn:ietf:params:xml:ns:carddav}address-data']
            $contacts[$i]['vcard']
        );
        /*
        $rutaArchivo = explode('/',$keyConctact);
        $nombreVcf = $rutaArchivo[count($rutaArchivo)-1];
        */

        echo '
            <div class="col-lg-4" style="padding-right: 5px; padding-left: 5px; min-height:165px;">
                <div class="contact-box" style="min-height:160px;">';
        if($vcard->CATEGORIES!= ''){
            $selectedCategories = explode(',', $vcard->CATEGORIES);
            $countCat = count($selectedCategories);
            $catWidth = 100/$countCat;
            $keyCat = 0;
            if ($countCat == 1){
                $selectedCategories[] = $selectedCategories[0];
                //$selectedCategories[] = $selectedCategories[0];
                $countCat = 2;
                $catWidth = 100/$countCat;
            }
            if($countCat > 0){
                foreach($selectedCategories as $cat){
                    $left = '';
                    $right = '';
                    switch($keyCat){
                        case 0:
                            $left = 'left: 5px;';
                            break;
                        case $countCat-1:
                            $right = 'right: 5px';
                            break;
                        default:
                            $left = 'left: '.($keyCat*$catWidth).'%';
                            break;
                    }
                    
                        echo '<div class="categories" style="background-color:#'.$arrayCatTypeColors[$cat].'; width:'.$catWidth.'%;'.$left.$right.'" align="center" title="'.$arrayCatType[$cat].'">&nbsp;</diV>';
                    
                    $keyCat++;
                }
            }
        }
        echo '      <a href="#" data-toggle="modal" data-target="#myModal" id="'.$contacts[$i]['vcf'].'">
                        <div class="col-sm-4">
                            <div class="text-center">';
        /*if (isset($vcard->PHOTO)){
            //echo $vcard->PHOTO;
            //$photo = 'data:image/JPEG;base64,' . base64_encode($vcard->PHOTO);
            echo  '<img class="img-circle m-t-xs img-responsive" src="'.$vcard->PHOTO.'" style="height:165.3px; width:165.3px; border-radius: 50%"/>';
        }else{*/
            echo  '<img alt="image" class="img-circle m-t-xs img-responsive" src="img/contact-logo.png">';
        //}
                                
        echo '               </div>
                            <div class="text-center">';
        echo                 date('d/m/Y - H:i:s',strtotime($vcard->REV) - 3*60*60);
        echo '              </div>            
                        </div>
                        <div class="col-sm-8">
                            <h3 id="name" value="'.$vcard->FN.'"><strong>'.$vcard->FN.'</strong></h3>
                            <p><i class="fa fa-email"></i> '.$vcard->EMAIL.'</p>
                            <address>';
                            //echo '<strong>'.substr($vcard->ORG, 0, -1).'</strong><br>';
                            //parseo de telefonos - muestra los primeros 3
                            if(isset($vcard->TEL)){
                                $qtel=0;
                                foreach($vcard->TEL as $tel) {
                                    if($tel['TYPE']==''){
                                        $teltype = 'OTHER';
                                    }else{
                                        $teltype = $arrayTelType[(string)$tel['TYPE']];
                                    }
                                    if($qtel<2){
                                        echo '<span id="phonetype">· '.$teltype.'</span>: <span id="phonenumber">'.$tel.'</span><br>';
                                    }
                                    $qtel++;
                                }
                            }
                            
        echo '              </address>';
        //pareso de direcciones
        /*
        if (isset($vcard->ADR)){
            foreach ($vcard->ADR as $value) {
                echo '      <div class="m-t-xs font-bold">'.$value['TYPE'].$value.'</div>';
            }
        }
        */
        echo '
                        </div>
                        <div class="clearfix"></div>
                    </a>                                   
                </div>
            </div>
        ';
    }
echo '      </div>
         </div>';
echo '  <div class="wrapper wrapper-content animated fadeInRight" ">
                            <div class="row text-center">
                                <div class="" >';
echo                $pagination;
echo '          </div>';
echo '           <div class=""><label>Showing: '.$contactsMin.' to '.$contactsMax.' of a total of '.$totalContacts.'</label></div>';
echo '      </div>
        </div>';


}



function modalContact(){
    echo '  <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" style="width:650px;"">
                    <div class="modal-content animated bounceInRight ">
                        <div class="modal-header" style="padding:8px 15px;">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        </div>
                        <form name=""vcard_form" id="vcard_form">
                        <div class="modal-body">
                        
                        </div>
                        </form>
                        <div class="modal-footer row" style="margin: 0px;">
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger align-left" id="delete">Delete</button>
                            </div>
                            <div class="col-md-2 col-md-offset-6">
                                <button type="button" class="btn btn-white" data-dismiss="modal" id="closemodal">Close</button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-primary" id="submit">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }

function paginate($item_per_page, $current_page, $total_records, $total_pages, $page_url,$filter = null,$startcharfilter = null)
{
    
    $pagination = '';
    if($total_pages > 0 && $total_pages != 1 && $current_page <= $total_pages){ //verify total pages and current page number
        $pagination .= '<ul class="pagination" style="margin:0;">';
       
         $right_links    = $current_page + 3;
        $previous       = $current_page - 3; //previous link
        $next           = $current_page + 1; //next link
        $first_link     = true; //boolean var to decide our first link
       
        if($current_page > 1){
            $previous_link = $current_page - 1;
            $pagination .= '<li class="first"><a href="javascript:ajaxContacts(null,1,\''.$filter.'\',\''.$startcharfilter.'\')" title="First">&laquo;</a></li>'; //first link
            $pagination .= '<li><a href="javascript:ajaxContacts(null,'.$previous_link.',\''.$startcharfilter.'\')" title="Previous">&lt;</a></li>'; //previous link
                for($i = ($current_page-2); $i < $current_page; $i++){ //Create left-hand side links
                    if($i > 0){
                        $pagination .= '<li><a href="javascript:ajaxContacts(null,'.$i.',\''.$filter.'\',\''.$startcharfilter.'\')">'.$i.'</a></li>';
                    }
                }  
            $first_link = false; //set first link to false
        }
       
        if($first_link){ //if current active page is first link
            $pagination .= '<li class="active"><a>'.$current_page.'</a></li>';
        }elseif($current_page == $total_pages){ //if it's the last active link
            $pagination .= '<li class="active"><a>'.$current_page.'</a></li>';
        }else{ //regular current link
            $pagination .= '<li class="active"><a>'.$current_page.'</a></li>';
        }
               
        for($i = $current_page+1; $i < $right_links ; $i++){ //create right-hand side links
            if($i<=$total_pages){
                $pagination .= '<li><a href="javascript:ajaxContacts(null,'.$i.',\''.$filter.'\',\''.$startcharfilter.'\')">'.$i.'</a></li>';
            }
        }
        if($current_page < $total_pages){
                $next_link = $current_page + 1;
                $pagination .= '<li><a href="javascript:ajaxContacts(null,'.$next_link.',\''.$filter.'\',\''.$startcharfilter.'\')" >&gt;</a></li>'; //next link
                $pagination .= '<li class="last"><a href="javascript:ajaxContacts(null,'.$total_pages.',\''.$filter.'\',\''.$startcharfilter.'\')" title="Last">&raquo;</a></li>'; //last link
        }
       
        $pagination .= '</ul>';
    }
    return $pagination; //return pagination links
}

function createContactsArray($arrayContacts){
    foreach($arrayContacts as $keyConctact=>$contact){
        $vcard = VObject\Reader::read(
            $contact['200']['{urn:ietf:params:xml:ns:carddav}address-data']
        );
        $newArrayContacts[] = array('vcf'=>$keyConctact,'name' => (string)$vcard->FN, 'vcard'=>$contact['200']['{urn:ietf:params:xml:ns:carddav}address-data']);
        //$newArrayContacts[$keyConctact]['vcard'] = $contact['200']['{urn:ietf:params:xml:ns:carddav}address-data'];
    }

    return $newArrayContacts;
}

function compare($a, $b){
    return strcmp(strtolower($a["name"]), strtolower($b["name"]));
}
/*
function sortArrayContacts($contactsSort){
    return usort($contactsSort,'cmp');
}*/
//include 'footer.php';
?>
<script type="text/javascript">
$('#myModal').on('show.bs.modal', function (e) {
    var vari = e.relatedTarget;
    //console.log($(vari)); // do something...
    //alert($(vari).attr('id'));
    urlVcf = $(vari).attr('id');
    var data = {urlvcf: urlVcf};

    datos = $.post("form_contact.php", data,function(result) {
        $('.modal-body').html(datos.responseText);
    }).fail(function(a,b,c) {
        console.log(datos);
        });
/*
    $.post("form_contact.php", data,function(result) {
        $('.modal-body').html(datos.responseText);
    }).fail(function(a,b,c) {
        console.log(datos);
        });
*/
/*
    nombre = $(vari).find('#name').attr('value');
    $('.modal-body').html('<div class="form-group"><label>Nombre</label> <input type="text" placeholder="Enter your email" class="form-control" value="'+nombre+'"></div>');
    $('.modal-body').append('<div class="form-group"><label>Telefonos</label>');
    telephones = $(vari).find('.phonenumbers');
    telephones.each(function(){
        phoneType = $(this).children().eq(0).html();
        phoneNumber = $(this).children().eq(1).html();
        $('.modal-body').append('<div class="form-group"><label>'+phoneType+'</label> <input type="text" placeholder="Enter your email" class="form-control" value="'+phoneNumber+'"></div>');
    });
    */
    //$('.modal-body').append('<div class="form-group"><label>Nombre</label> <input type="txt" placeholder="Enter your email" class="form-control" value="'+nombre+'"></div>');
});

//$(document).on('keyup', '#contact-search', function (e) {
$(document).ready(function(){
        var delay = (function(){
          var timer = 0;
          return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
          };
        })();
        $('#contact-search').unbind('keyup');
        $('#contact-search').keyup(function(e){
            txt = $(this).val();
            startcharfilter = $('#startcharfilter').val();
            delay(function(){
              ajaxContacts(null,null,txt,startcharfilter);
            }, 1000 );
            /*
            if(txt==''){
                ajaxContacts(null,null,txt);
            }else{
                if(e.keyCode == 13){
                    ajaxContacts(null,null,txt);
                }
            }*/

        });
    });


$("#submit").on('click',function(){
    returned = $.post('save_vcard.php', $('#vcard_form').serialize());
    $('#closemodal').click();
    txt = $('#contact-search').val();
    actualpage = $('#actualpage').val();
    startcharfilter = $('#startcharfilter').val();
    console.log(returned);
    ajaxContacts(null,actualpage,txt,startcharfilter);

});
$("#delete").on('click',function(){
    returned = $.post('delete_vcard.php', $('#vcard_form').serialize());
    $('#closemodal').click();
    txt = $('#contact-search').val();
    actualpage = $('#actualpage').val();
    startcharfilter = $('#startcharfilter').val();
    ajaxContacts(null,actualpage,txt,startcharfilter);

});
</script>