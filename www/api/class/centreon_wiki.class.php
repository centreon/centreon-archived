<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/include/configuration/configKnowledge/functions.php";
require_once dirname(__FILE__) . "/webService.class.php";

class CentreonWiki extends CentreonWebService
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function postCheckConnection()
    {
        $sql_host = $_POST['host'];
        $sql_user = $_POST['user'];
        $sql_pwd = $_POST['pwd'];
        $sql_name = $_POST['name'];

        try {
            $dbh = new PDO('mysql:host=' . $sql_host . ';dbname=' . $sql_name, $sql_user, $sql_pwd);
            die(json_encode(array('outcome' => true)));
        } catch (PDOException $ex) {
            die(json_encode(array('outcome' => false, 'message' => $ex->getMessage())));
        }
    }

    public function postDeletePage()
    {
// get wiki info
        $conf = getWikiConfig($this->pearDB);
        $apiWikiURL = $conf['kb_wiki_url'] . '/api.php';
        $wikiVersion = getWikiVersion($apiWikiURL);
        $login = $conf['kb_wiki_account'];
        $pass = $conf['kb_wiki_password'];
        $title = $_POST['title'];

        $path_cookie = '/tmp/temporary_wiki_connection.txt';
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
        $tokenConnexion = $json_connexion['login']['token'];
        // you take the token and keep it in a var for your second login

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
    }
}
