<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 * Contient du code GPL provenant de www.libretudes.org et de lolut.utbm.info
 */

require("adodb.inc.php");
require("variables.php");
require("rs2xml/rs2xml.inc.php");
require("tsession.php");

class Ttransformation
{
  var $cache_path = "cache/";
  var $xml_path = "";
  var $xslt_path = "";
  var $peut_etre_en_cache = true;
  var $voir_xml = false;

  var $requetes_sql = array();
  var $fichiers_xml = array();
  var $fichier_xslt = null;

  var $USE_SABLOTRON = true;

  var $iSession = null;
	
  function Ttransformation() //constructeur
  {
    session_start();// TODO: restreindre le path d'action du cookie. (par défaut "/"... pas top)
    if ($_SESSION['isession'] == null)
      {
	$this->iSession = new Tsession();
	$_SESSION['isession'] = &$this->iSession;
      }
    else
      {
	$this->iSession = &$_SESSION['isession']; 
      }
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
    else
      {
	$this->type_mime = "text/html";
      }

    foreach($this->fichiers_xml as $key => $fichier_xml) {
      $this->fichiers_xml[$key] = $this->xml_path . $fichier_xml;
    }
    $fichier_xslt = $this->xslt_path . $this->fichier_xslt;
    $html = "";

    $nodexml_session = $this->session_vers_xml();
    $nodexml_categories = $this->categories_vers_xml();
    $nodexml_fichiers = $this->fichiers_vers_xml($this->fichiers_xml);
    $nodexml_sql = $this->sql_vers_xml($this->requetes_sql);
    $array_nodexml = array($nodexml_session, $nodexml_categories, $nodexml_fichiers, $nodexml_sql);
    $domxml = $this->agreger_xml($array_nodexml);
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

    if ( ! is_array($requetes) )
      {
	return null;//"<erreur>Erreur, l'argument $sql n'est pas un tableau</erreur>";
	//exit;
      }
    $db = &ADONewConnection($dbtype); // create a connection
    $connexion_result = $db->PConnect($dbhost,$dbuser,$dbpass,$dbname); // Connexion à la base

    $xml = "<"."?"."xml version='1.0' encoding='UTF-8' ?".">\n";
    if ($connexion_result == false)
      {
	$xml .= "<erreur>Erreur de connexion à la base de donnée</erreur>";
      }
    else
      {
	$xml .= "<requetes>";
	foreach($requetes as $key => $sql)
	  {
	    $recordset = $db->Execute($sql);
	    if ( $recordset == false )
	      {
		$xml .= "<erreur>" .  $db->ErrorMsg() . " ($sql)</erreur>";
	      }
	    else
	      {
		$xml .= rs2xml($recordset,'');
	      }
	  }
	$xml .= "</requetes>\n";
      }
    $domxml = domxml_open_mem($xml);
    return $domxml->document_element();
  }

  function session_vers_xml(){
    $dom = domxml_new_doc("1.0");
    $session = $dom->create_element("session");
    $dom->append_child($session);
    if ( $this->iSession->login != null){
      $session->set_attribute("login", $this->iSession->login);
    }
    $panier = $dom->create_element("panier");
    foreach($this->iSession->panier as $id => $quantite){
      $produit = $dom->create_element("produit");
      $produit->set_attribute("id", $id);
      $produit->set_attribute("quantite", $quantite);
      //todo: faire une requete SQL pour extraire le prix de l'article
      $panier->append_child($produit);
    }
    $session->append_child($panier);
    //$xml = $dom->dump_mem(true, "UTF-8");
    return $session;
  }
	
  function categories_vers_xml(){
    $dbhost = $GLOBALS["dbhost"];
    $dbname = $GLOBALS["dbname"];
    $dbuser = $GLOBALS["dbuser"];
    $dbpass = $GLOBALS["dbpass"];
    $dbtype = $GLOBALS["dbtype"];  
    $db = &ADONewConnection($dbtype); // create a connection
    $connexion_result = $db->PConnect($dbhost,$dbuser,$dbpass,$dbname); // Connexion à la base
    $xml = "<"."?"."xml version='1.0' encoding='UTF-8' ?".">\n";
    if ($connexion_result == false)
      {
	$xml .= "<erreur>Erreur de connexion à la base de donnée</erreur>";
      }  
    else
      {
	$lang = $this->iSession->lang;
	$requete = "SELECT categorie_id, titre.ressource_texte, descr.ressource_texte FROM (categorie INNER JOIN ressource as titre ON categorie_titre = titre.ressource_id) INNER JOIN ressource as descr ON descr.ressource_id = categorie_description WHERE titre.ressource_langue_code = '$lang' AND descr.ressource_langue_code = '$lang'";
	$recordset = $db->Execute($requete);
	$xml .= "<categories>";
	    
	if ( $recordset == false )
	  {
	    $xml.= "<erreur>" .  $db->ErrorMsg() . " ($requete)</erreur>";
	  }
	else
	  {
	    $xml .= rs2xml($recordset,'');
	  }
	$xml .= "</categories>\n";
      }
    $domxml = domxml_open_mem($xml);
    return $domxml->document_element();
  }

  function fichiers_vers_xml($array_fichiers){
    $dom_all = domxml_new_doc("1.0");
    $root_all = $dom_all->create_element("fichiers");
    $dom_all->append_child($root_all);
    foreach($array_fichiers as $key => $fichier_xml){
      $dom = domxml_open_file($fichier_xml);
      $root = $dom->document_element();
      $root_all->append_child($root->clone_node(true));
    }
    return $root_all;
  }

  function agreger_xml($array_nodexml){
    $dom = domxml_new_doc("1.0");
    $root = $dom->create_element("page");
    $dom->append_child($root);
    foreach($array_nodexml as $key=>$node){
      if ($node != null){
	$root->append_child($node->clone_node(true));
      }
    }
    return $dom;
  }

  function xml_vers_html($domxml, $fichier_xslt)
  {
    if (! $this->USE_SABLOTRON ){
      $xsltproc = domxml_xslt_stylesheet_file($fichier_xslt);
      $result = $xsltproc->process($domxml);
      return $xsltproc->result_dump_mem($result);
    }
    else{
      $xml = $domxml->dump_mem(true,"UTF-8");
      $arguments = array('/_xml' => $xml);
      $xp = xslt_create();
      $result = xslt_process($xp, 'arg:/_xml', $fichier_xslt, NULL, $arguments);
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
