<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
 version="1.0" 
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns="http://www.w3.org/1999/xhtml">
  <xsl:output
   doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
   doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
   encoding="UTF-8"
   indent="yes"
   method="xml"
   omit-xml-declaration="no"
   version="1.0"/>

  <xsl:param name="baseurl" select="'http://lisa.bouil.org/~bouil/internet/falstelo/php/trunk/'"/>
  <xsl:param name="Message" select="''"/>
  <xsl:param name="titre-page"  xmlns:html="http://www.w3.org/1999/xhtml" select="//html:html/html:head/html:title"/>
  <xsl:param name="css-file"  xmlns:html="http://www.w3.org/1999/xhtml" select="'common.css'"/>

  <xsl:template match="/">
    <html lang="fr" xml:lang="fr">
      <head>
	<base href="{$baseurl}"/>
	<title>Falstelo - <xsl:value-of select="$titre-page"/></title>
	<xsl:choose>
	  <xsl:when xmlns:html="http://www.w3.org/1999/xhtml" test="count(//html:html/html:head/html:link[@rel='stylesheet']/@href) != 0">
	    <link rel="stylesheet" xmlns:html="http://www.w3.org/1999/xhtml" href="{$baseurl}css/{//html:html/html:head/html:link[@rel='stylesheet']/@href}" type="text/css" title="Classique"/>
	  </xsl:when>
	  <xsl:otherwise>
	    <link rel="stylesheet" href="{$baseurl}css/{$css-file}" type="text/css" title="Classique"/>
	  </xsl:otherwise>
	</xsl:choose>
      </head>
      <body>

	<div class="top">
	  <div class="logo">
	    <img src="img/logo.png"/>
	  </div>
	</div>

	<div class="boites gauche">
	  <div class="boite">
	    <p class="titre">
	      Menu
	    </p>
	    <div class="corps">
	      <ul>
		<li><a href="accueil.html">Accueil</a></li>
		<li><a href="documentation/documentation.html">Documentation</a></li>
		<li>
		  Page du projet sur BerliOS.de&#160;:
		  <ul>
		    <li><a href="https://developer.berlios.de/projects/falstelo/">Résumé</a></li>
		    <li><a href="https://developer.berlios.de/project/showfiles.php?group_id=1734">Téléchargement</a></li>
		    <li><a href="https://developer.berlios.de/svn/?group_id=1734">Code source</a></li>
		    <li><a href="https://developer.berlios.de/bugs/?group_id=1734">Rapports de bugs</a></li>
		  </ul>
		</li>
		<li><a href="contact.html">Contact</a></li>
	      </ul>
	    </div>
	  </div>
	</div>

	<div class="main">

	  <div class="contenu" id="contenu">
	    <xsl:if test="$Message != ''">
	      <p class="message"><xsl:value-of select="$Message"/></p>
	    </xsl:if>
	    <xsl:apply-templates select="page/fichiers | page/requetes"/>
	    <div style="clear: both">&#160;</div>
	  </div>


	</div>

	<!-- Pied de page -->
	<div id="pied">
	  <p>© Copyright Nicolas Bouillon, 2004.</p>
	  <p>Ce site utilise XML/XSLT grâce à PHP Falstelo</p>
	</div>
      </body>
    </html>
    <xsl:comment>Généré par le processeur xslt de <xsl:value-of select="system-property('xsl:vendor')"/> (<xsl:value-of select="system-property('xsl:vendor-url')"/>), supportant XSLT version <xsl:value-of select="system-property('xsl:version')"/></xsl:comment>
  </xsl:template>

  <xsl:template match="adodb_result/query"/>

  <xsl:template match="erreur">
    <h1 style="color: red; text-align: center;"><xsl:apply-templates/></h1>
  </xsl:template>

  <xsl:template xmlns:html="http://www.w3.org/1999/xhtml" match="html">
    <xsl:apply-templates select="html:body"/>
  </xsl:template>

  <xsl:template xmlns:html="http://www.w3.org/1999/xhtml" match="html:html/html:head"/>

  <xsl:template xmlns:html="http://www.w3.org/1999/xhtml" match="html:body">
    <xsl:copy-of select="text() | *"/>
  </xsl:template>

</xsl:stylesheet>




