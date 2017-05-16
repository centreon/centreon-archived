<?php
/**
 * CENTREON
 *
 * Source Copyright 2005-2015 CENTREON
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */

function versionCentreon($pearDB)
{
    if (is_null($pearDB)) {
        throw new \Exception('No Database connect available');
    }

    $query = 'SELECT `value` FROM `informations` WHERE `key` = "version"';
    $res = $pearDB->query($query);
    if (PEAR::isError($res)) {
        return null;
    }
    $row = $res->fetchRow();

    return $row['value'];
}

function getWikiConfig($pearDB)
{
    $errorMsg = 'MediaWiki is not installed or configured. Please refer to the ' .
        '<a href="https://documentation-fr.centreon.com/docs/centreon-knowledge-base/en/latest/" target="_blank" >' .
        'documentation.</a>';

    $mandatoryConfigKey = array('kb_db_name', 'kb_db_host', 'kb_wiki_url');
    if (is_null($pearDB)) {
        throw new \Exception($errorMsg);
    }

    $res = $pearDB->query("SELECT * FROM `options` WHERE options.key LIKE 'kb_%'");

    if ($res->numRows() == 0) {
        throw new \Exception($errorMsg);
    }

    $gopt = array();
    while ($opt = $res->fetchRow()) {

        if (!empty($opt["value"])) {
            $gopt[$opt["key"]] = html_entity_decode($opt["value"], ENT_QUOTES, "UTF-8");
        } else {
            if (in_array($opt["key"], $mandatoryConfigKey)) {
                throw new \Exception($errorMsg);
            }
        }
    }

    $pattern = '#^http://|https://#';
    $WikiURL = $gopt['kb_wiki_url'];
    $checkWikiUrl = preg_match($pattern, $WikiURL);

    if (!$checkWikiUrl) {
        $gopt['kb_wiki_url'] = 'http://' . $WikiURL;
    }

    $res->free();
    return $gopt;
}


function getWikiVersion($apiWikiURL)
{
    if (is_null($apiWikiURL)) {
        return;
    }

    $post = array(
        'action' => 'query',
        'meta' => 'siteinfo',
        'format' => 'json',
    );

    $data = http_build_query($post);

    $httpOpts = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded",
            'content' => $data,
        )
    );

    /* Create context */
    $httpContext = stream_context_create($httpOpts);

    /* Get contents */
    $content = @file_get_contents($apiWikiURL, false, $httpContext);
    $content = json_decode($content);

    $wikiStringVersion = $content->query->general->generator;
    $wikiDataVersion = explode(' ', $wikiStringVersion);
    $wikiVersion = (float)$wikiDataVersion[1];

    return $wikiVersion;
}
