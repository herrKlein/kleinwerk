<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  exclude-result-prefixes="xsl">
<xsl:output method="xml"/>
<!-- this template takes care of nodes with xHTML, these have no namespace, so we define the tags -->
    <xsl:template match="p|ul|ol|li|h2|h1|h3|small|span|div|form|fieldset|label|input|textarea|img|br|script|tr|td|table">
        <xsl:copy-of select="." />
    </xsl:template>
    <xsl:template match="a">
        <a href="{@href}" id="{@id}" class="{@class}"><xsl:apply-templates select="*|text()"/></a>
    </xsl:template>
    <xsl:template match="p">
        <p class="{@class}"><xsl:apply-templates select="*|text()"/></p>
    </xsl:template>

</xsl:stylesheet>
