<?php
ini_set("display_errors", "On");

$centreon_path = realpath(dirname(__FILE__) . '/../../../../');
require_once $centreon_path . "/config/centreon.config.php";
set_include_path(get_include_path() . PATH_SEPARATOR . $centreon_path .
    "www/class/centreon-knowledge/" . PATH_SEPARATOR . $centreon_path . "www/");
require_once "class/centreonDB.class.php";
$modules_path = $centreon_path . "www/include/configuration/configKnowledge/";
require_once $modules_path . 'functions.php';


/*
 * DB connexion
 */
$pearDB = new CentreonDB();

// get wiki info
$conf = getWikiConfig($pearDB);
$apiWikiURL = $conf['kb_wiki_url'] . '/api.php';
$wikiVersion = getWikiVersion($apiWikiURL);
$login = $conf['kb_wiki_account'];
$pass = $conf['kb_wiki_password'];
$title = $_POST['title'];

$path_cookie = '/tmp/connexion_temporaire.txt';
if (!file_exists($path_cookie)) {
    touch($path_cookie);
}

//////////////////////////////////////////////////////////////////////////
//                           Get Connexion Cookie/Token
//////////////////////////////////////////////////////////////////////////
$postfields = array(
    'action' => 'login',
    'format' => 'json',
    'lgname' => $login,
    'lgpassword' => $pass
);

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, $apiWikiURL);
curl_setopt($curl, CURLOPT_COOKIESESSION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($curl, CURLOPT_COOKIEJAR, $path_cookie); // you put your cookie in the file
$connexion = curl_exec($curl);
$json_connexion = json_decode($connexion, true);
$tokenConnexion = $json_connexion['login']['token']; // you take the token and keep it in a var for your second login

// /!\ don't close the curl connection or initialize a new one or your session id will change !

//////////////////////////////////////////////////////////////////////////
//                           Launch Connexion
//////////////////////////////////////////////////////////////////////////

$postfields = array(
    'action' => 'login',
    'format' => 'json',
    'lgtoken' => $tokenConnexion,
    'lgname' => $login,
    'lgpassword' => $pass

);

curl_setopt($curl, CURLOPT_URL, $apiWikiURL);
curl_setopt($curl, CURLOPT_COOKIESESSION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($curl, CURLOPT_COOKIEFILE, $path_cookie); //get the previous cookie
$connexionToken = curl_exec($curl);
$json_connexion = json_decode($connexionToken, true);
$resultLogin = $json_connexion['login']['result'];

if ($resultLogin != 'Success') {
    die(json_encode(array('result' => $resultLogin)));
}

//////////////////////////////////////////////////////////////////////////
//                           Get Delete Token
//////////////////////////////////////////////////////////////////////////

if ($wikiVersion >= 1.20) {
    $postfields = array(
        'action' => 'tokens',
        'type' => 'delete',
        'format' => 'json'
    );
} else {
    $postfields = array(
        'action' => 'query',
        'prop' => 'info',
        'intoken' => 'delete',
        'format' => 'json',
        'titles' => $title
    );
}


curl_setopt($curl, CURLOPT_URL, $apiWikiURL);
curl_setopt($curl, CURLOPT_COOKIESESSION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($curl, CURLOPT_COOKIEFILE, $path_cookie); //get the previous cookie
$deleteToken = curl_exec($curl);
$json_delete = json_decode($deleteToken, true);

if ($wikiVersion >= 1.20) {
    $tokenDelete = $json_delete['tokens']['deletetoken'];
} else {
    $tokenDelete = $json_delete['query']['pages'][2]['deletetoken'];
}


//////////////////////////////////////////////////////////////////////////
//                           Delete Page
//////////////////////////////////////////////////////////////////////////

$postfields = array(
    'action' => 'delete',
    'title' => $title,
    'token' => $tokenDelete
);

curl_setopt($curl, CURLOPT_URL, $apiWikiURL);
curl_setopt($curl, CURLOPT_COOKIESESSION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($curl, CURLOPT_COOKIEFILE, $path_cookie); //get the previous cookie
$delete = curl_exec($curl);

$json_delete = json_decode($delete, true);
$tokenDelete = $json_delete['tokens']['deletetoken'];

// close the curl connection
curl_close($curl);
die(json_encode(array('result' => 'delete')));
