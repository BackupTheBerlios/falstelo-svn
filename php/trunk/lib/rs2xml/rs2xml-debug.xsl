<?xml version="1.0" encoding="UTF-8" ?>
<!-- 
  - Experimental XSL stylesheet for rs2xml.inc.php output. Shows the result
  -   as an HTML table, shows the field names and types as another table,
  -   and, finally, shows the SQL statement used to generate the recordset.
  -
  - Written by Toyvo Yanski <toyvo@inbox.ru>
  - Date: 2002-Jul-31
  -->
<xsl:stylesheet version="1.1" 
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template match="//adodb_result">
    <html>
      <head>
        <title>SQL Select Query Result</title>
      </head>
      <body>
        <h1>SQL Select Query Result</h1>
        <h2>Records</h2>
        <table>         
        <tr><xsl:for-each select="field">
          <th><xsl:value-of select="./@name"/></th>
        </xsl:for-each></tr>
        <xsl:for-each select="row">
          <tr><xsl:for-each select="e">
            <td><xsl:value-of select="."/></td>
          </xsl:for-each></tr>
        </xsl:for-each>
        </table>
        <h2>fields</h2>
        <table><tr><th>Name</th><th>Type</th>
               <th>AdoDB-type</th><th>Max-length</th></tr>
        <xsl:for-each select="field">
          <tr>
           <td><xsl:value-of select="@name"/></td>
           <td><xsl:value-of select="@type"/></td>
           <td><xsl:value-of select="@ado_type"/></td>
           <td><xsl:value-of select="@max_length"/></td>
          </tr>        
        </xsl:for-each>
        </table>
        <h2>SQL-query</h2>
        <xsl:value-of select="query"/><br />
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>