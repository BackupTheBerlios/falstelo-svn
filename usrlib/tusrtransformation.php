<?php
require("tsession.php");
require("lib/ttransformation.php");

class Tusrtransformation extends Ttransformation
{
  var $iSession = null;
  var $fixed_xml_nodes = array();
  function Tusrtransformation()
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

    array_push($this->array_nodexml, $this->iSession->get_nodexml());
  }

  /*	
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
*/
}
?>