<?php
/*--
  - Written by Toyvo Yanski <toyvo@inbox.ru>
  - Date: 2002-Jul-31
  -*/

//  This example demonstrates basic use of rs2xml function. It outputs the xml
//  result into your browser as is.


// Change below to appropriate include path
    require_once('/modules/adodb/adodb.inc.php');
    require_once('rs2xml.inc.php');

// Change below to your database type
    $db  = ADONewConnection('mysql');

// Change below to your settings...
    $db -> Connect('localhost', '', '', 'test');

// Set the query here...
    $rs  = $db -> Execute('show tables');

    $xml = rs2xml($rs);
    print  nl2br(htmlspecialchars($xml));
    $rs -> Close();
?>