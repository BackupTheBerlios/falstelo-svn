<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 * Contient du code GPL provenant de www.libretudes.org et de lolut.utbm.info
 */

require("adodb.inc.php");

if (file_exists("usrlib/variables.php"))
{
  require("usrlib/variables.php");
}
else
{
  require("variables.php");
}

require("rs2xml/rs2xml.inc.php");

class Ttransformation
{
  var $cache_path = "cache/";
  var $xml_path = "";
  var $xslt_path = "";
  var $temps_cache = 0;
  var $voir_xml = false;
  var $type_mime = "text/html";

  var $requetes_sql = array();
  var $requetes_sql_champs_xml = array();
  var $fichiers_xml = array();
  var $array_nodexml = array();
  var $fichier_xslt = null;
  var $xslt_params = array();
  
  var $USE_SABLOTRON = true;

  function Ttransformation() //constructeur
  {

  }

  function get()
  {
    if ( $this->fichiers_xml == null)
      {
	return "Erreur : appel de TTransoformation:get() sans fichiers_xml attribué";
      }
    else
      {
	return $this->transformer();
      }
  }

  function transformer()
  {
    if ( $this->fichier_xslt == "" || $this->fichier_xslt == null )
      {
	// si aucun fichier xslt n'est défini, on affiche le fichier xml tel quel.
	$this->voir_xml = true;
      }

    if ($this->voir_xml == true)
      {
	$this->type_mime = "text/xml";
      }

    foreach($this->fichiers_xml as $key => $fichier_xml) {
      $this->fichiers_xml[$key] = $this->xml_path . $fichier_xml;
    }
    $fichier_xslt = $this->xslt_path . $this->fichier_xslt;
    $html = "";

    //$nodexml_session = $this->session_vers_xml();
    //$nodexml_categories = $this->categories_vers_xml();
    $nodexml_fichiers = $this->fichiers_vers_xml($this->fichiers_xml);
    $nodexml_sql = $this->sql_vers_xml($this->requetes_sql);
    array_push($this->array_nodexml, $nodexml_fichiers);
    array_push($this->array_nodexml, $nodexml_sql);
    $domxml = $this->agreger_xml($this->array_nodexml);
    if ( $this->voir_xml != true ){
      $html = $this->xml_vers_html($domxml, $fichier_xslt);
    }
    else{
      $html = $domxml->dump_mem(true,"UTF-8");
    }
    return $html;
  }

  function sql_vers_xml($requetes)
  {
    $dbhost = $GLOBALS["dbhost"];
    $dbname = $GLOBALS["dbname"];
    $dbuser = $GLOBALS["dbuser"];
    $dbpass = $GLOBALS["dbpass"];
    $dbtype = $GLOBALS["dbtype"];
    $dbencoding = $GLOBALS["dbencoding"];

    if ( ! is_array($requetes) )
      {
	return null;//"<erreur>Erreur, l'argument $sql n'est pas un tableau</erreur>";
	//exit;
      }
    
    if (sizeof($requetes)>0){

      
      $db = &ADONewConnection($dbtype); // create a connection
      $connexion_result = $db->PConnect($dbhost,$dbuser,$dbpass,$dbname); // Connexion à la base
      
      if ($connexion_result == false)
	{
	  $xml = "<"."?"."xml version='1.0' encoding='UTF-8' ?".">\n";
	  $xml .= "<erreur>Erreur de connexion à la base de donnée</erreur>";
	}
      else
	{
	  $xml = "<"."?"."xml version='1.0' encoding='$dbencoding' ?".">\n";
	  $xml .= "<requetes>";
	  foreach($requetes as $key => $sql)
	    {
	      $champs_xml = $this->requetes_sql_champs_xml[$key];
	      if ($champs_xml == null){
		$champs_xml = array();
	      }
	      $recordset = $db->Execute($sql);
	      if ( $recordset == false )
		{
		  $xml .= "<erreur>" .  $db->ErrorMsg() . " ($sql)</erreur>";
		}
	      else
		{
		  $xml .= rs2xml($recordset,'', array("key" => "$key"), $champs_xml);
		}
	    }
	  $xml .= "</requetes>\n";
	}
      $domxml = domxml_open_mem($xml);
      return $domxml->document_element();
    }
    else{
      return null;
    }
  }

  function fichiers_vers_xml($array_fichiers){
    $dom_all = domxml_new_doc("1.0");
    $root_all = $dom_all->create_element("fichiers");

    foreach($array_fichiers as $key => $fichier_xml){
      $dom = domxml_open_file($fichier_xml);
      $root = $dom->document_element();
      $root_all->append_child($root->clone_node(true));
    }
    $dom_all->append_child($root_all);
    return $root_all;
  }

  function agreger_xml($array_nodexml){
    $dom = domxml_new_doc("1.0");
    $root = $dom->create_element("page");
    foreach($array_nodexml as $key=>$node){
      if ($node != null){
	$root->append_child($node->clone_node(true));
      }
    }
    $dom->append_child($root);
    return $dom;
  }

  function xml_vers_html($domxml, $fichier_xslt)
  {
    if (! $this->USE_SABLOTRON ){
      $xsltproc = domxml_xslt_stylesheet_file($fichier_xslt);
      $result = $xsltproc->process($domxml, $this->xslt_params);
      return $result->dump_mem();
    //return $xsltproc->result_dump_mem($result);
    }
    else{
      $xml = $domxml->dump_mem(true,"UTF-8");
      $arguments = array('/_xml' => $xml);
      $xp = xslt_create();
      $result = xslt_process($xp, 'arg:/_xml', $fichier_xslt, NULL, $arguments, $this->xslt_params);
      xslt_free($xp);
      return $result;
    }
  }

  /*
	function fichier_xml_vers_html($fichier_xml, $fichier_xslt)
	{
		if (file_exists ($fichier_xml) == true)
		{
			$xp = xslt_create();
			$result = xslt_process($xp, $fichier_xml, $fichier_xslt);
			xslt_free($xp);
		}
		else
		{
			die("Le fichier $fichier_xml n'existe pas !");
		}
		return $result;
	}
  */
}
?>
