<?php

/*--
  - Experimental php script for converting ADODB recorset into XML
  - Written by Toyvo Yanski <toyvo@inbox.ru>
  - Date: 2002-Jul-31
  -*/


// RecordSet to XML
//------------------------------------------------------------
// Converts an ADODB recordset to an XML document.
//
// See the result format Document Type Definition in rs2xml.dtd;
//
//
// function rs2xml (&$rs, $attribs, $callback)
//
//  $rs              : the recordset
// [$callback]       : user-defined function to parse all db entries before they
//                     are rendered into the XML. The following parameters are
//                     passed: $callback($entry, $row, $col, $rs); $entry is the 
//                     pcdata of the entry, $row & $col - its coordinates (first
//                     entry is [0;0]), and $rs - the recordset.
// [$attribs]        : a hash array of xml attributes for the top-level element,
//                     e.g. 'array("nickname"=>"query1", "author"=>"Josh")' would
//                     produce <adodb_result nickname="query1" author="Josh">;
//
//  USAGE:
//  
//    require('../adodb/adodb.inc.php');
//    require('rs2xml.inc.php');
//
//    $db  = ADONewConnection('mysql');
//    $db -> Connect('localhost', 'userid', 'password', 'database');
//    $rs  = $db -> Execute('show tables');
//    $xml = rs2xml($rs);
//    print  nl2br(htmlspecialchars($xml));
//    $rs -> Close();
//  
//
//  RETURNS: a correct xml fragment


// {{{ rs2xml()

/**
 * Converts an ADODB recordset into an xml fragment.
 *
 * @param  mixed  $rs       The recordset, passed by reference.
 * @param  string $callback The name of the callback function, optional.
 * @param  array  $attribs  A hash of xml attributes for the root element.
 *
 * @access public
 * @return string           XML document fragment with root element 
 *                          <adodb_result> and children elements as defined
 *                          in rs2xml.dtd;
 */

function rs2xml(&$rs, $callback = '', $attribs = array())
{
    if ( $rs->RecordCount()>0 && !is_array($rs->fields)) {
        trigger_error("[rs2xml()] Supplied resource is not a valid recordset", 256);
    };
               
    $xml .= '<adodb_result';
    while ($e = each ($attribs)) {
        $xml .= ' '. $e[0] . '="' . cdata2pcdata($e[1]) . '"';
    };
    $xml .= '>';

    $xml .= "<query>".cdata2pcdata($rs->sql)."</query>";

    $rsCls  = $rs->FieldCount();
    for ($i = 0; $i < $rsCls; $i++) {
        $rsColumn = $rs->FetchField($i);
        $fName   = cdata2pcdata($rsColumn->name);
        $fType   = cdata2pcdata($rsColumn->type);
        $fMaxL   = cdata2pcdata($rsColumn->max_length);
        $fAdoT   = cdata2pcdata($rs->MetaType($rsColumn->type, 
                    $rsColumn->max_length, $rsColumn));
        $xml     .= "<field name=\"$fName\" type=\"$fType\" max_length=\"$fMaxL\" ado_type=\"$fAdoT\" />";
    };

    $rsArr  = $rs->GetArray();
    $rsRws  = count($rsArr);
    for ($i = 0; $i < $rsRws; $i++) {
        $xml .= "<row>";
        for ($k = 0; $k < $rsCls; $k++) {
            $rsColumn = $rs->FetchField($k);
            $fName   = cdata2pcdata($rsColumn->name);
            //$xml  .= "<e>";
            $xml .= "<".$fName.">";
            $entry = $rsArr[$i][$k];
            if ($callback) {
                $entry = $callback($entry, $i, $k, &$rs);
            } else {
                $entry = cdata2pcdata($entry);
            };
            //$xml .= "$entry</e>";
            $xml .= "$entry</$fName>";
        };
        $xml .= "</row>";
    };
    $xml .= "</adodb_result>";
    return $xml;
}
// }}}


// {{{ cdata2pcdata()

/**
 * Converts a string into XML pcdata by replacing characters [&<>"'] with the
 * appropriate XML entity references.
 *
 * @param  string $entry  the string to be converted
 * @return string         parsed character data (pcdata)
 * @access public
 */

function cdata2pcdata ($entry) {
    $entry = str_replace('&', '&amp;',  $entry);
    $entry = str_replace('<', '&lt;',   $entry);
    $entry = str_replace('>', '&gt;',   $entry);
    $entry = str_replace('"', '&quot;', $entry);
    $entry = str_replace("'", '&apos;', $entry);
    return $entry;
}
// }}}
