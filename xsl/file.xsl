<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="exslt php"
    >
<!--
*   file-exists-local   : path
*   file-exists-remote  : url
*   file-listing-local  : path, match, levels
-->
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />


<!-- MARK file-exists-local -->
<!--
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path" select="'/path/to/file'" />
    </xsl:call-template>
    or
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path">__WP_HOME_DIR__/wp-content/uploads/file.txt</xsl:with-param>
    </xsl:call-template>
    or
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path">__WP_CONTENT_DIR__/uploads/file.txt</xsl:with-param>
    </xsl:call-template>
    or
    <xsl:call-template name="file-exists-local">
        <xsl:with-param name="path">__XSLT_PLUGIN_DIR__/xml/file.xml</xsl:with-param>
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


<!-- MARK file-exists-remote -->
<!--
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


<!-- MARK file-listing-local -->
<!--
    <xsl:call-template name="file-listing-local">
        <xsl:with-param name="path">/path/to/directory</xsl:with-param>
        <xsl:with-param name="match">.xml$</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="file-listing-local">
        <xsl:param name="path" select="''" />
        <xsl:param name="match" select="'.xml$'" />
        <xsl:param name="levels" select="'10'" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"path" =&gt; "</xsl:text><xsl:value-of select="$path" /><xsl:text>"</xsl:text>
                <xsl:text>, "match" =&gt; "</xsl:text><xsl:value-of select="$match" /><xsl:text>"</xsl:text>
                <xsl:text>, "levels" =&gt; "</xsl:text><xsl:value-of select="$levels" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getFileListingLocal',string($SUBPARAMS))/RESULT" />
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/file.xsl -->
