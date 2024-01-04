<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="exslt php"
    >
<!--
*   date-microtime  : none
*   date-format     : time, value, shift, format
-->
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />


<!-- MARK date-microtime -->
<!--
    <xsl:call-template name="date-microtime" />
-->
    <xsl:template name="date-microtime">
        <xsl:copy-of select="php:function('XSLT_Callback','getMicrotime','$params = array();')/RESULT" />
    </xsl:template>


<!-- MARK date-format -->
<!--
    <xsl:call-template name="date-format">
        <xsl:with-param name="value">string datetime</xsl:with-param>
        <xsl:with-param name="shift">string, eg "+x hours +y minutes"</xsl:with-param>
        <xsl:with-param name="format">string, l = Sunday ; D = Sun ; d = 01 ; F = January ; M = Jan ; m = 01 ; Y = 2000 ; U = unix seconds</xsl:with-param>
    </xsl:call-template>
or
    <xsl:call-template name="date-format">
        <xsl:with-param name="time">int utime</xsl:with-param>
        <xsl:with-param name="shift">string, eg "+x hours +y minutes"</xsl:with-param>
        <xsl:with-param name="format">string, l = Sunday ; D = Sun ; d = 01 ; F = January ; M = Jan ; m = 01 ; Y = 2000 ; U = unix seconds</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="date-format">
        <xsl:param name="time" select="''" />
        <xsl:param name="value" select="''" />
        <xsl:param name="shift" select="''" />
        <xsl:param name="format" select="''" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"time" =&gt; "</xsl:text><xsl:value-of select="$time" /><xsl:text>"</xsl:text>
                <xsl:text>, "value" =&gt; "</xsl:text><xsl:value-of select="$value" /><xsl:text>"</xsl:text>
                <xsl:text>, "shift" =&gt; "</xsl:text><xsl:value-of select="$shift" /><xsl:text>"</xsl:text>
                <xsl:text>, "format" =&gt; "</xsl:text><xsl:value-of select="$format" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>

        <xsl:copy-of select="php:function('XSLT_Callback','getDateTime',string($SUBPARAMS))/RESULT" />
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/date.xsl -->
