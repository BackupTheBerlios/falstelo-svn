<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet
 version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns="http://www.w3.org/1999/xhtml">
<xsl:output
	doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN"
	doctype-system="http://www.w3.org/TR/html4/loose.dtd"
	encoding="iso-8859-1" indent="yes" method="html"
	omit-xml-declaration="yes" version="1.0"/>

<xsl:include href="common.xsl"/>
<xsl:variable name="titre-page">Accueil</xsl:variable>
<xsl:variable name="css-file">common.css</xsl:variable>
<xsl:variable name="onglet">Accueil</xsl:variable>

<xsl:template match="page">
  <xsl:copy-of select="text()|*"/>
</xsl:template>

</xsl:stylesheet>
