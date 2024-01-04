<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <!-- set output method="xml|html|text" -->
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />

    <!-- include templates from other files -->
    <xsl:include href="../../../plugins/tenandtwo-xslt-processor/xsl/date.xsl" />

    <!-- catch values passed in shortcode attributes -->
    <xsl:param name="hello_name">Nobody</xsl:param>

    <!-- main -->
    <xsl:template match="/">
        <h3>Hello <xsl:value-of select="$hello_name" /></h3>

        <!-- select and process nodes with 'for-each' -->
        <p>Comments:
            <ul>
            <xsl:for-each select="//comment">
                <li><pre><xsl:value-of select="." /></pre></li>
            </xsl:for-each>
            </ul>
        </p>

        <!-- select and process nodes with 'apply-templates' -->
        <p>Lists: <xsl:apply-templates select="//list" /></p>
        <p>Dates: <ul><xsl:apply-templates select="//date" /></ul></p>

    </xsl:template>

    <!-- list nodes -->
    <xsl:template match="list">
        <p>List: <xsl:value-of select="./@title" />
            <ul>
            <xsl:apply-templates select="item" />
            </ul>
        </p>
    </xsl:template>

    <!-- list/item nodes -->
    <xsl:template match="item">
        <li>
        <xsl:value-of select="." />
        <xsl:value-of select="concat(' (', @value, ')')" />
        </li>
    </xsl:template>

    <!-- date nodes : use template from date.xsl to format (and shift) the value -->
    <xsl:template match="date">
        <li>
        <xsl:call-template name="date-format">
            <xsl:with-param name="value"><xsl:value-of select="." /></xsl:with-param>
            <xsl:with-param name="format">l, F d Y, H:i:s</xsl:with-param>
            <xsl:with-param name="shift">+10 hours</xsl:with-param>
        </xsl:call-template>
        </li>
    </xsl:template>

</xsl:stylesheet>
<!-- end sample.xsl -->
