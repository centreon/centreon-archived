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
        return;
    }

    $query = 'SELECT `value` FROM `informations` WHERE `key` = "version"';
    $res = $pearDB->query($query);
    if (PEAR::isError($res)) {
        return null;
    }
    $row = $res->fetchRow();

    return $row['value'];
}

function Mediawikiconfigexist($url)
{
    return true;
    $file_headers = @get_headers($url);
    if ($file_headers[0] == 'HTTP/1.1 404 Not Found') {
        return false;
    }

    return true;
}

function getWikiConfig($pearDB)
{
    if (is_null($pearDB)) {
        return;
    }

    $res = $pearDB->query("SELECT * FROM `options` WHERE options.key LIKE 'kb_%'");
    while ($opt = $res->fetchRow()) {
        $gopt[$opt["key"]] = html_entity_decode($opt["value"], ENT_QUOTES, "UTF-8");
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
