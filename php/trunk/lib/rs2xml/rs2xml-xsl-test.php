<?php
/*--
  - Written by Toyvo Yanski <toyvo@inbox.ru>
  - Date: 2002-Jul-31
  -*/

//  This example demonstrates basic use of XML functionality. Requires PHP with
//  xslt extension (currently based on Sablotron XSLT processor).

// Change below to appropriate include path
    require_once('/modules/adodb/adodb.inc.php');
    require_once('rs2xml.inc.php');

// Change below to your database type
    $db  = ADONewConnection('mysql');

// Change below to your settings...
    $db -> Connect('localhost', '', '', 'test');

// Set the query here...
    $rs  = $db -> Execute('show tables');

    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $xml.= rs2xml($rs);
    $xsl = join(file('rs2xml-debug.xsl'),'');

    $arg = array (
        'xml'=>$xml, 
        'xsl'=>$xsl
    );

// $params is a hash of parameters to be passed to xsl stylesheet.
    $params = array (); 

    $xh  = xslt_create();
    $htm = xslt_process($xh, 'arg:xml', 'arg:xsl', NULL, $arg, $params);

    xslt_free($xh);        

    print $htm;
    
    $rs -> Close();
?>
