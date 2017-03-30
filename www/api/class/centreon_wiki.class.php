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
        $sql_host = explode(':', $this->arguments['host']);
        $host = $sql_host[0];
        $port = isset($sql_host[1]) ? $sql_host[1] : '3306';
        $user = $this->arguments['user'];
        $password = $this->arguments['pwd'];
        $db = $this->arguments['name'];

        try {
            new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $password);
            $outcome = true;
            $message = _('Connection Successful');
        } catch (PDOException $e) {
            $outcome = false;
            $message = $e->getMessage();
        }

        return array(
            'outcome' => $outcome,
            'message' => $message
        );
    }

    public function postDeletePage()
    {
        // get wiki info
        $conf = getWikiConfig($this->pearDB);
        $apiWikiURL = $conf['kb_wiki_url'] . '/api.php';
        $wikiVersion = getWikiVersion($apiWikiURL);
        $login = $conf['kb_wiki_account'];
        $pass = $conf['kb_wiki_password'];
        $title = $this->arguments['title'];

        $path_cookie = '/tmp/temporary_wiki_connection.txt';
        if (!file_exists($path_cookie)) {
            touch($path_cookie);
        }

        // Get Connection Cookie/Token
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

        // Launch Connection
        $postfields = array(
            'action' => 'login',
            'format' => 'json',
            'lgtoken' => $tokenConnexion,
            'lgname' => $login,
            'lgpassword' => $pass
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        $connexionToken = curl_exec($curl);
        $json_connexion = json_decode($connexionToken, true);
        $resultLogin = $json_connexion['login']['result'];

        if ($resultLogin != 'Success') {
            return array(
                'result' => $resultLogin
            );
        }

        // Get Delete Token
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
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        $deleteToken = curl_exec($curl);
        $json_delete = json_decode($deleteToken, true);

        if ($wikiVersion >= 1.20) {
            $tokenDelete = $json_delete['tokens']['deletetoken'];
        } else {
            $page = array_pop($json_delete['query']['pages']);
            $tokenDelete = $page['deletetoken'];
        }

        // Delete Page
        $postfields = array(
            'action' => 'delete',
            'title' => $title,
            'token' => $tokenDelete
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        curl_exec($curl);

        // close the curl connection
        curl_close($curl);

        return array(
            'result' => 'delete'
        );
    }
}
