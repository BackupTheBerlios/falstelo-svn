<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
 version="1.0" 
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output
   doctype-public="-//W3C//DTD HTML 4.01//EN"
	doctype-system="http://www.w3.org/TR/html40/strict.dtd"
   encoding="UTF-8"
   indent="yes"
   method="html"
   omit-xml-declaration="yes"
   version="1.0"/>

  <xsl:template match="racine">
	 <html>
		<head>
		  <title>Statique</title>
		</head>
		<body>
		  <p>Le texte de votre fichier est&#160;:</p>
		  <pre><xsl:apply-templates/></pre>
		</body>
	 </html>
  </xsl:template>

</xsl:stylesheet>