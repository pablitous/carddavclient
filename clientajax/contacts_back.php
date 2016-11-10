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
include 'header.php';

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
include 'menu.php';
?>
                <div class="col-lg-9">
                    <h2>Contacts</h2>
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
                $queryContacts = '<card:addressbook-query xmlns:d="DAV:" xmlns:card="urn:ietf:params:xml:ns:carddav">
                                <d:prop>
                                    <d:getetag />
                                    <card:address-data />
                                </d:prop>
                            </card:addressbook-query>';
                if(!isset($_SESSION['contactlist'])){
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
                $arrayCatType = array('Business'=>'Businesss','Home'=>'Home','Fun'=>'Fun','Personal'=>'Personal','School'=>'School','Sport'=>'Sport','Important'=>'Important','Phone Call'=>'Phone Call','Hot Contacts'=>'Hot Contacts','Key Customer'=>'Key Customer','Suppliers'=>'Suppliers',''=>'None');
                $arrayCatTypeColors = array('Business'=>'3EB7EE','Home'=>'BE4532','Fun'=>'40875B','Personal'=>'F09E31','School'=>'E42089','Sport'=>'303135','Important'=>'AF0000','Phone Call'=>'B18400','Hot Contacts'=>'7FB210','Key Customer'=>'AF0000','Suppliers'=>'E6AC00',''=>'fff');
                //print_r($contacts);
                if (isset($_GET['page']) && $_GET['page'] > 0 ){
                    $actualPage = $_GET['page'];
                }else{
                    $actualPage = 1;
                }
                $totalContacts = count($contacts);
                $adjacentLinks = 2;
                $totalPages = ceil($totalContacts/$contactsPerPage);

                $contactsMin = ($contactsPerPage * $actualPage) - $contactsPerPage;
                $contactsMax = $contactsPerPage * $actualPage;
                if($totalContacts < $contactsMax){
                    $contactsMax = $totalContacts;
                }
                $pagination = paginate($contactsPerPage, $actualPage, $totalContacts, $totalPages, './contacts.php');

                echo '  <div class="wrapper wrapper-content animated fadeInRight" style="padding:10px 10px 0px;">
                            <div class="row text-center">
                                <div class="" >';
                echo                $pagination;
                echo '          </div>';
                echo '           <div class=""><label>Showing: '.$contactsMin.' to '.$contactsMax.' of a total of '.$totalContacts.'</label></div>';
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
                        <div class="col-lg-4">
                            <div class="contact-box">
                                <a href="#" data-toggle="modal" data-target="#myModal" id="'.$contacts[$i]['vcf'].'">
                                    <div class="col-sm-4">
                                        <div class="text-center">
                                            <img alt="image" class="img-circle m-t-xs img-responsive" src="img/contact-logo.png">
                                            <div class="m-t-xs font-bold text-center">';
                    $selectedCategories = explode(',', $vcard->CATEGORIES);
                    foreach($selectedCategories as $cat){
                        echo '<div style="background-color:#'.$arrayCatTypeColors[$cat].';width:8px; display:inline-block;margin: 1px" align="center" alt="'.$arrayCatType[$cat].'">&nbsp;</diV>';
                    }

                    echo '                  </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <h3 id="name" value="'.$vcard->FN.'"><strong>'.$vcard->FN.'</strong></h3>
                                        <p><i class="fa fa-map-marker"></i> '.$vcard->ADR.'</p>
                                        <address>
                                        <strong>'.$vcard->ORG.'</strong><br>';
                                        //parseo de telefonos - muestra los primeros 3
                                        if(isset($vcard->TEL)){
                                            $qtel=0;
                                            foreach($vcard->TEL as $tel) {
                                                if($qtel<3)
                                                echo '<li class="phonenumbers"><span id="phonetype">'.$tel['TYPE'].'</span>: <span id="phonenumber">'.$tel.'</span>';
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
                ?>
            </div>
        </div>
<?php
echo '  <div class="wrapper wrapper-content animated fadeInRight" ">
                            <div class="row text-center">
                                <div class="" >';
echo                $pagination;
echo '          </div>';
echo '           <div class=""><label>Showing: '.$contactsMin.' to '.$contactsMax.' of a total of '.$totalContacts.'</label></div>';
echo '      </div>
        </div>';
modalContact();

function modalContact(){
    echo '  <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" style="width:650px;"">
            <div class="modal-content animated bounceInRight ">
                    <div class="modal-header" style="padding:8px 15px;">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    </div>
                    <div class="modal-body">
                       
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>';
    }
function paginate($item_per_page, $current_page, $total_records, $total_pages, $page_url)
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
            $pagination .= '<li class="first"><a href="'.$page_url.'?page=1" title="First">&laquo;</a></li>'; //first link
            $pagination .= '<li><a href="'.$page_url.'?page='.$previous_link.'" title="Previous">&lt;</a></li>'; //previous link
                for($i = ($current_page-2); $i < $current_page; $i++){ //Create left-hand side links
                    if($i > 0){
                        $pagination .= '<li><a href="'.$page_url.'?page='.$i.'">'.$i.'</a></li>';
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
                $pagination .= '<li><a href="'.$page_url.'?page='.$i.'">'.$i.'</a></li>';
            }
        }
        if($current_page < $total_pages){
                $next_link = $current_page + 1;
                $pagination .= '<li><a href="'.$page_url.'?page='.$next_link.'" >&gt;</a></li>'; //next link
                $pagination .= '<li class="last"><a href="'.$page_url.'?page='.$total_pages.'" title="Last">&raquo;</a></li>'; //last link
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
include 'footer.php';
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
})

</script>