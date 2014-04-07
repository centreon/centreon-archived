<?php

// make website templates from HTML pages
// open the current directory

$langdir = dirname(__FILE__).'/../'.$argv[1];
$webdir = !empty($argv[2]) ? dirname(__FILE__).'/../'.$argv[2] : dirname(__FILE__).'/../htmlweb';

if(empty($argv[1]))
  die("usage: {$argv[0]} [manual-dir]\n");
if(!is_dir($langdir))
  die("manual dir ({$langdir})) must exist\n");

if(!is_dir($webdir))
  mkdir($webdir)||die("unable to make dir ({$webdir})\n");

$dhandle = new RecursiveDirectoryIterator($langdir);

foreach (new RecursiveIteratorIterator($dhandle) as $fpath) {
     if(substr($fpath,-4)=='.tpl') {
        $content = file_get_contents($fpath);
	$fname = basename($fpath);
	preg_match('!<title>(.*?)</title>!s',$content,$match);
	$title = $match[1];
	preg_match('!<body[^>]*>(.*?)</body>!s',$content,$match);
	$body = $match[1];
        $body = str_replace(array('{','}','@@LDELIM@@','@@RDELIM@@'),array('@@LDELIM@@','@@RDELIM@@','{ldelim}','{rdelim}'),$body);
        $title = str_replace(array('{','}','@@LDELIM@@','@@RDELIM@@'),array('@@LDELIM@@','@@RDELIM@@','{ldelim}','{rdelim}'),$title);
        $description = $title;
        $keywords = preg_replace(array('![^\w\s]+!','!\s+!'),array(' ',', '),strtolower(trim($title)));
        // fix link filenames, remove .tpl
	//$body = preg_replace('!href="(.*)\.tpl(#.*)?"!','href="$1$2"',$body);
        $template = "{extends file='layout.tpl'}\n";
        $template .= "{block name=title}@TITLE@{/block}\n";
        $template .= "{block name=description}@DESCRIPTION@{/block}\n";
        $template .= "{block name=keywords}@KEYWORDS@{/block}\n";
        $template .= "{block name=main_content}@BODY@{/block}\n";
        $template = str_replace(array('@TITLE@','@BODY@','@DESCRIPTION@','@KEYWORDS@'),array($title,$body,$description,$keywords),$template);
	if(!is_dir($webdir))
          mkdir($webdir,0755,true);
        file_put_contents($webdir.'/'.$fname,$template);
     }
}

?>
