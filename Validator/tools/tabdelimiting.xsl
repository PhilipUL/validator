<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" />
<xsl:template match="/nodeList/nodeList">
<xsl:for-each select="//node">
path=<xsl:value-of select="@path"/><xsl:text>&#9;</xsl:text>text=<xsl:value-of select="@content"/><xsl:text>&#9;</xsl:text>
<xsl:for-each select="output/@*">
<xsl:value-of select="name()"/>="<xsl:value-of select="."/>"<xsl:text>&#9;</xsl:text></xsl:for-each>
 <xsl:for-each select="output/*">
 <xsl:value-of select="name()"/>="<xsl:value-of select="."/>
<xsl:for-each select="@*">
 <!--<xsl:value-of select="name()"/>="--><xsl:value-of select="."/>"<xsl:text>&#9;</xsl:text></xsl:for-each>
</xsl:for-each>
<!--<xsl:for-each select="output/*/*"><xsl:value-of select="."/></xsl:for-each>-->
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>