<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output
	method="xhtml"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	version="1.0"
	encoding="UTF-8"
	indent="yes"
	omit-xml-declaration="no"/>
<!--<xsl:output
	doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN"
	doctype-system="http://www.w3.org/TR/html4/loose.dtd"
	encoding="ISO-8859-1" indent="yes" method="html"
	omit-xml-declaration="yes" version="1.0"/>-->

<xsl:template match="/">
	<html xml:lang="fr" lang ="fr">
		<head>
			<title>Présentation d'un produit</title>
			<link rel="stylesheet" href="./css/style.css" type="text/css"/>
		</head>
		<body>
			<div class="entete">
				<div class="logo">
					LOGO
				</div>
				Entete
			</div>
			<div class="spacer"></div>
			<div class="gauche">
				menu de gauche
			</div>
			<div class="contenu">
				<xsl:apply-templates />
				Fin fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin  fin
			</div>
		</body>
	</html>
</xsl:template>

<xsl:template match="produit">
	<div class="produit">
		<xsl:apply-templates />
	</div>
</xsl:template>

<xsl:template match="produit/nom">
	<h1 class="nom">
		<xsl:apply-templates/>
	</h1>
</xsl:template>

<xsl:template match="produit/prix">
	<p class="prix">
		<xsl:apply-templates/>
	</p>
</xsl:template>


</xsl:stylesheet>
