<?php
/*
 * (c) Nicolas Bouillon
 * Ce fichier est distribué selon la licence GNU GPL.
 * Contient du code GPL provenant de www.libretudes.org et de lolut.utbm.info
 */

require_once("adodb.inc.php");
require_once("rs2xml/rs2xml.inc.php");

require_once("variables.php");
if (file_exists("usrlib/variables.php"))
{
  require_once("usrlib/variables.php");
}

class Ttransformation{

  /**
  Contient une liste des requetes SQL à executer, sous forme de liste associative
  Exemple :
      $this->requetes_sql["requete1"] = "SELECT champ1, champ2, COUNT(champ3) champ4, ... FROM .... ;"
      $this->requetes_sql["requete2"] = "SELECT ... FROM .... ;"
      $this->requetes_sql["requete3"] = "SELECT ... FROM .... ;"
   */
  var $requetes_sql = array();//

  /**
  Si dans le resultat d'une requete SQL, certains champs contienent du XML valide.
  Exemple de contenu :
      $this->requetes_sql_champs_xml["requete1"] = array("champ1, champ4");
      $this->requetes_sql_champs_xml["requete3"] = array("champ2");
  */
  var $requetes_sql_champs_xml = array();

  /**
  Liste des fichiers XML a utiliser. Le chemin des fichiers est relatif à la racine du site, plus XML_PATH (voir dans variables.php)
  Exemple:
      array_push($this->fichiers_xml, "repertoire/fichier.xml");
  */
  var $fichiers_xml = array();

  /**
  Chaque item du tableau contient un document XML DOM qui sera utilisé pour la transformation. Les fichiers XML et les requetes SQL seront placés à la fin de ce tableau avant transformation. On peut alors ajouter en tete de tableau des documents DOM XML générés manuellement pour une page donnée, ou pour toutes les pages via tusrtransformation (exemple typique : les variables de sessions de l'utilsateur : login, contenu du panier, ...)
  */
  var $array_nodexml = array();

  /**
  Contient le chemin vers le fichier XSLT utilsé pour la transformation. A définir depuis un fichier php surchargeant ttransformation ou tusrtransformation. Depuis un fichier .xml statique, cet attribut sera affecté selon la balise [?xml-stylesheet href...?] du document XML. Si aucun fichier XSLT n'est donné (autant pour les xml statiques que pour les fichiers php, le document XML sera affiché sans transformation.
  Exemple :
      $this->fichier_xslt = "xslt/xhtml2latex.xsl";
  */
  var $fichier_xslt = null;

  /**
  Contient la liste des parametres XSLT a fournir au processeur XSLT. Utilisé via tusrtransformation pour les parametres toujours utiles, ou par un fichier php classique pour des parametres pontuels.
  Exemple :
      $this->xslt_params["baseurl"] = "http://" . $_SERVER['SERVER_NAME'] . "/";
  */
  var $xslt_params = array();
  
  /**
  Chemin relatif de la page appellée par rapport à la racine du site
  */
  var $chemin_relatif = "./";
  
  
  /**
  Valeurs par défaut qui seront  écrasées par lib/variables.php et usrlib/variables.php.
  Voir lib/variables.php pour la signification des ces variables.
  */
  var $USE_SABLOTRON = false;
  var $type_mime = "text/html";
  var $temps_cache = 0;
  var $cache_path = "cache/";
  var $xml_path = ""; //sera ajouté au début de chaque chemin XML
  var $xslt_path = ""; //idem pour les XSLT 
  var $voir_xml = false; // est ce qu'on effectue la transformation XSLT

  var $db_host = "";
  var $db_name = "";
  var $db_user = "";
  var $db_pass = "";
  var $db_type = "";
  var $db_encoding = "";

  /**
  Variables internes de travail
  */
  var $page = ""; //page affichée
  var $page_demandee = ""; //page demandée, peut etre differente, par exemple page_demandee = "nonexist" et page = "404"

  /**
  Constructeur de l'objet.
  */
  function Ttransformation($page_affichee, $page_demandee)
  {
    // récupération des préférences de l'utilsateur
    global $GLOBALS;
    $this->USE_SABLOTRON = $GLOBALS["USE_SABLOTRON"];
    $this->type_mime = $GLOBALS["TYPE_MIME"];
    $this->temps_cache = $GLOBALS["TEMPS_CACHE"];
    $this->cache_path = $GLOBALS["CACHE_PATH"];
    $this->xml_path = $GLOBALS["XML_PATH"];
    $this->xslt_path = $GLOBALS["XSLT_PATH"];
    $this->voir_xml = $GLOBALS["VOIR_XML"];

    //connexion à la base de donnée
    $this->db_host = $GLOBALS["dbhost"];
    $this->db_name = $GLOBALS["dbname"];
    $this->db_user = $GLOBALS["dbuser"];
    $this->db_pass = $GLOBALS["dbpass"];
    $this->db_type = $GLOBALS["dbtype"];
    $this->db_encoding = $GLOBALS["dbencoding"];

    $this->page_affichee = $page_affichee;
    $this->page_demandee = $page_demandee;
    
    $this->xslt_params['dbencoding'] = $this->db_encoding;
  }

  
  function __extract_processing_instruction(&$dom)
  {
    $xpth = $dom->xpath_new_context();
    $pis = $xpth->xpath_eval("//self::processing-instruction()");
    return $pis;
  }

  function __extract_stylesheet_filename($fichier_xml)
  {
    // ! Le fichier xml ne doit avoir qu'une seule processing instruction
    $dom = domxml_open_file(getcwd() . "/" . $fichier_xml);
    $pis = $this->__extract_processing_instruction($dom);
    $node = $pis->nodeset[0];
    if ( $node != null)
    {
      preg_match("/href=['\"](.+?)['\"]/", $node->data(), $match);
      return $match[1];
    }
    else
    {
      return null;
    }
  }
  
  /**
  Retourne la page demandée, ou bien la page d'erreur $page_erreur si la page n'existe pas. Accessoirement prends le fichier du cache s'il existe et s'il n'est pas expiré, sinon écrit le fichier résultat dans le cache.
  @returns String contenant le code à afficher.
  */
  function get()
  {
    $this->xslt_params["chemin_relatif"] = $this->chemin_relatif;
    if (($this->simple_html == true) && ($voir_xml == false))
    {
      // recherche le nom de la feuille de style
      $fichier_xslt_relativetodir = $this->__extract_stylesheet_filename($this->fichiers_xml[0]);  // le nom de fichier de la feuille de style est relati\f au chemin du fichier xml.
      $this->fichier_xslt = dirname($this->fichiers_xml[0]) . "/" .  $fichier_xslt_relativetodir;
      if (!file_exists($this->fichier_xslt) || filetype($this->fichier_xslt) != "file")
      {
        $this->fichier_xslt = null;
      }
    }
    $fichier_cache = $this->cache_path . $this->page_affichee . ".html";
    if (file_exists($fichier_cache) && (filemtime($fichier_cache) + $this->temps_cache > time()) && filemtime($fichier_cache) > filemtime($this->fichier_xslt))
    {
      // on utilise la version qui vient du cache
      return file_get_contents($fichier_cache);
    }
    else
    {
      // on genere la page
      $html = $this->transformer();
      
      // si temps_cache > 0, alors on écrit le fichier dans le cache.
      if ( $this->temps_cache > 0)
      {
        //on verifie d'abord si le repertoire d'accueil existe. Sinon on le cree.
        if ( ! file_exists(dirname($fichier_cache)) )
        {
          mkdir(dirname($fichier_cache), umask(), true);
        }
        $fp = @fopen ($fichier_cache, "w");
        if ($fp)
        {
          fputs ($fp, $html . "<!--Fichier extrait du cache (" . date("r", time())   . ")-->\n");
          fclose ($fp);
        }
      }
      return $html;
    }
  }

  /**
Cette fonction effectue la transformation et retourne la chaine résultat. Cependant, si le fichier XSLT n'est pas défini, aucune transformation n'est effectuée, et le XML est retourné tel quel. Le type MIME est alors modifé en text/xml.
  */
  function transformer()
  {
    if ( $this->fichier_xslt == "" || $this->fichier_xslt == null ){
      // si aucun fichier xslt n'est défini, on affiche le fichier xml tel quel.
      $this->voir_xml = true;
    }
    
    if ($this->voir_xml == true){
      $this->type_mime = "text/xml";
    }
    
    foreach($this->fichiers_xml as $key => $fichier_xml) {
      $this->fichiers_xml[$key] = $this->xml_path . $fichier_xml;
    }
    $fichier_xslt = $this->xslt_path . $this->fichier_xslt;
    $html = "";
    
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

  /**
Transforme la liste des requetes SQL en un ensemble de noeuds XML contenant le résultat.
  */
  function sql_vers_xml($requetes)
  {
    if ( ! is_array($requetes) )
      {
  return null;//"<erreur>Erreur, l'argument $sql n'est pas un tableau</erreur>";
  //exit;
      }
    
    if (sizeof($requetes)>0){
 
      $db = &ADONewConnection($this->db_type); // create a connection
      $connexion_result = $db->PConnect($this->db_host, $this->db_user, $this->db_pass, $this->db_name); // Connexion à la base
      
      if ($connexion_result == false)
  {
    $xml = "<"."?"."xml version='1.0' encoding='UTF-8' ?".">\n";
    $xml .= "<erreur>Erreur de connexion à la base de donnée</erreur>";
  }
      else
  {
    $xml = "<"."?"."xml version='1.0' encoding='$this->db_encoding' ?".">\n";
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

  /**
Ouvre tous les fichiers XML données et les retourne en tant que noeud XML
  */
  function fichiers_vers_xml($array_fichiers){
    $dom_all = domxml_new_doc("1.0");
    $root_all = $dom_all->create_element("fichiers");

    foreach($array_fichiers as $key => $fichier_xml){
      $dom = domxml_open_file(getcwd() . "/" . $fichier_xml);
      $root = $dom->document_element();
      $root_all->append_child($root->clone_node(true));
    }
    $dom_all->append_child($root_all);
    return $root_all;
  }

  /**
Prends tous les nodes XML du tableau, et les place dans un seul document XML
  */
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

  /**
Transforme le document DOM XML donné en autre chose grace à la feuille de style donnée.
TODO: Support de transformation en cascade, avec plusieurs feuilles de style ?
  */
  function xml_vers_html($domxml, $fichier_xslt)
  {
    if (! $this->USE_SABLOTRON ){
      //libxslt
      $xsltproc = domxml_xslt_stylesheet_file(getcwd() . "/" . $fichier_xslt);
      $result = $xsltproc->process($domxml, $this->xslt_params);
      //return $result->dump_mem(); // Effet de bord : le résultat contient l'entete XML [?xml version... ?] 
      return $xsltproc->result_dump_mem($result); // Mais on ne veux pas forcement cette entete. C'est à la feuille de style de le décider.
    }
    else{
      //Sablotron
      $xml = $domxml->dump_mem(true,"UTF-8");
      $arguments = array('/_xml' => $xml);
      $xp = xslt_create();
      $result = xslt_process($xp, 'arg:/_xml', getcwd() . "/" . $fichier_xslt, NULL, $arguments, $this->xslt_params);
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
