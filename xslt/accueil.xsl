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

  <xsl:include href="common.xsl"/>
  <xsl:variable name="titre-page">Ĉefpaĝo</xsl:variable>
  <xsl:variable name="css-file">common.css</xsl:variable>
  <xsl:variable name="onglet">Ĉefpaĝo</xsl:variable>

  <xsl:template match="page">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="titre">
    <h1><xsl:apply-templates/></h1>
  </xsl:template>

  <xsl:template match="image">
    <div style="float: left; margin: 1em;">
      <img src="{text()}" alt="RAYON"/>
    </div>
  </xsl:template>

  <xsl:template match="into">
    <p style="font-weight: bold"><xsl:apply-templates/></p>
  </xsl:template>

  <xsl:template match="texte">
    <p><xsl:apply-templates/></p>
  </xsl:template>
  
</xsl:stylesheet>

