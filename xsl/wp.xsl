<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="exslt php"
    >
<!--
-   wp-sanitize-title   : title
-   wp-xml-select       : post, thpe
-->
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />


<!-- wp-sanitize-title
    uses XSLT_Callback : getSanitizeTitle()
-->
    <xsl:template name="wp-sanitize-title">
        <xsl:param name="title" select="text()" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"title" =&gt; "</xsl:text><xsl:value-of select="$title" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getSanitizeTitle',string($SUBPARAMS))/RESULT" />
    </xsl:template>


<!-- wp-xml-select
    uses XSLT_Callback : getXmlSelect()

    <xsl:call-template name="wp-xml-select">
        <xsl:with-param name="post" select="12345" />
    </xsl:call-template>
    or

-->
    <xsl:template name="wp-xml-select">
        <xsl:param name="xml"    select="''" />
        <xsl:param name="select" select="'/'" />
        <xsl:param name="cache" select="'-1'" />
        <xsl:param name="format" select="'xml'" />
        <xsl:param name="root"   select="'RESULT'" />
        <!-- <xsl:param name="strip-declaration" select="'yes'" /> -->
        <xsl:param name="strip-namespaces"  select="'no'" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"xml" =&gt; "</xsl:text><xsl:value-of select="$xml" /><xsl:text>"</xsl:text>
                <xsl:text>, "select" =&gt; "</xsl:text><xsl:value-of select="$select" /><xsl:text>"</xsl:text>
                <xsl:text>, "cache" =&gt; "</xsl:text><xsl:value-of select="$cache" /><xsl:text>"</xsl:text>
                <xsl:text>, "format" =&gt; "</xsl:text><xsl:value-of select="$format" /><xsl:text>"</xsl:text>
                <xsl:text>, "root" =&gt; "</xsl:text><xsl:value-of select="$root" /><xsl:text>"</xsl:text>
                <!-- <xsl:text>, "strip-declaration" =&gt; "</xsl:text><xsl:value-of select="$strip-declaration" /><xsl:text>"</xsl:text> -->
                <xsl:text>, "strip-namespaces" =&gt; "</xsl:text><xsl:value-of select="$strip-namespaces" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getXmlSelect',string($SUBPARAMS))/RESULT" />
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/util.xsl -->
