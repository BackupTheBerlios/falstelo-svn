<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
 version="1.0" 
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output
   doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN"
   doctype-system="http://www.w3.org/TR/html4/loose.dtd"
   encoding="iso-8859-1"
   indent="yes"
   method="html"
   omit-xml-declaration="yes"
   version="1.0"/>

  <xsl:param name="Message" select="''"/>

  <xsl:decimal-format name="francais" decimal-separator="," grouping-separator=" "/>

  <xsl:template name="Onglet">
    <xsl:param name="NomOnglet" select="ERREUR"/>
    <xsl:param name="LienOnglet" select="ERREUR"/>
    <xsl:choose>
      <xsl:when test="$onglet != $NomOnglet">
	<li class="unselected">
	  <a href="{$LienOnglet}"><xsl:value-of select="$NomOnglet"/></a>
	</li>
      </xsl:when>
      <xsl:otherwise>
	<li class="selected">
	  <a href="{$LienOnglet}"><xsl:value-of select="$NomOnglet"/></a>
	</li>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="/">
    <html>
      <head>
	<title>Falstelo - <xsl:value-of select="$titre-page"/></title>
	<link rel="stylesheet" href="./css/{$css-file}" type="text/css"/>
	<script src="./js/scripts.js" type="text/javascript" defer="defer" />
      </head>
      <body>
	<div class="top">
	  <div class="logo"><img src="images/logo.png" alt="La Maison du Pastis - Marseille"/></div>
	</div>

	<div class="main">

	  <div class="onglets">
	    <ul class="liens">
	      <xsl:call-template name="Onglet">
		<xsl:with-param name="NomOnglet" select="'Ĉefpaĝo'"/>
		<xsl:with-param name="LienOnglet" select="'accueil.html'"/>
	      </xsl:call-template>
<!--	      <xsl:call-template name="Onglet">
		<xsl:with-param name="NomOnglet" select="'Kompendio'"/>
		<xsl:with-param name="LienOnglet" select="'manuel.html'"/>
	      </xsl:call-template>
	      <xsl:call-template name="Onglet">
		<xsl:with-param name="NomOnglet" select="'Kontaktu nin'"/>
		<xsl:with-param name="LienOnglet" select="'contact.html'"/>
	      </xsl:call-template>-->
	    </ul>
	  </div>


	  
	  <div class="contenu">
	    <xsl:if test="$Message != ''">
	      <p class="message"><xsl:value-of select="$Message"/></p>
	    </xsl:if>
	    <xsl:apply-templates select="page/fichiers | page/requetes"/>
	  </div>

<!--	  <div class="onglets ongletsbas">
	    <ul class="liens">
	      <li class="unselected">
		<a href="">FR</a>
	      </li>
	      <li class="selected">
		<a href="">EO</a>
	      </li>
	    </ul>
	  </div>-->

	</div>

	<!-- Pied de page -->
	<div class="pied">
	  <p>© Copyleft La Falstelo Projekto 2004.</p>
	</div>
	
      </body>
    </html>
    <xsl:comment>Généré par le processeur xslt de <xsl:value-of select="system-property('xsl:vendor')"/> (<xsl:value-of select="system-property('xsl:vendor-url')"/>), supportant XSLT version <xsl:value-of select="system-property('xsl:version')"/></xsl:comment>
  </xsl:template>

  <xsl:template match="page/session/panier">
    <!--    <xsl:if test="count(produit) &gt; 0">-->
    <div class="boite panier">
      <p class="titre">Votre Panier</p>
      <!--(<a href="#" onclick="javascript:document.getElementById('panier').style.display='none'">V</a>)-->
      <div id="panier" class="corps">
	<p>Votre <a href="panier.html">panier</a> contient <xsl:value-of select="sum(produit/quantite)"/> produit(s)</p>
	<p style="border-top: 1px solid black">Total: <xsl:value-of select="format-number(sum(produit/totalttc), '# ##0,00', 'francais')"/> EUR</p>
      </div>
    </div>
    <!--    </xsl:if>-->
  </xsl:template>

  <!-- <boites categories> !-->

  <xsl:template match="page/categories">
    <ul>
      <li>
	Pastis
      </li>
      <li>
	Absinthe
      </li>
      <li>
	Accessoires
      </li>
      <xsl:apply-templates/>
    </ul>
  </xsl:template>

  <xsl:template match="page/categories/categorie">
    <li>
      <a href="categorie.html?cat_nom={@cat_nom}"><xsl:value-of select="@cat_nom"/></a>
    </li>
  </xsl:template>

  <!-- </boite categories> !-->

  <xsl:template match="adodb_result/query"/>

  <xsl:template match="erreur">
    <h1 style="color: red;"><xsl:apply-templates/></h1>
  </xsl:template>

</xsl:stylesheet>



