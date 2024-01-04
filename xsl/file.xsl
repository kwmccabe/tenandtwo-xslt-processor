<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="exslt php"
    >
<!--
*   file-exists-local  : path
*   file-exists-remote : url
-->
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />


<!-- file-exists-local
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path" select="'/path/to/file'" />
    </xsl:call-template>
    or
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path">__DOCUMENT_ROOT__/wp-content/uploads/file.txt</xsl:with-param>
    </xsl:call-template>
    or
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path">__PLUGIN_DIR__/xml/file.xml</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="file-exists-local">
        <xsl:param name="path" select="''" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"path" =&gt; "</xsl:text><xsl:value-of select="$path" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getFileExistsLocal',string($SUBPARAMS))/RESULT" />
    </xsl:template>


<!-- file-exists-remote
    <xsl:call-template name="file-exists-remote">
        <xsl:with-param name="url">http://somewhere.com/myfile.xml</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="file-exists-remote">
        <xsl:param name="url" select="''" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"url" =&gt; "</xsl:text><xsl:value-of select="$url" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getFileExistsRemote',string($SUBPARAMS))/RESULT" />
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/file.xsl -->
