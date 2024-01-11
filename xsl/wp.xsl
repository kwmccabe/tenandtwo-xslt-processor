<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="exslt php"
    >
<!--
-   wp-select-xml       : xml, select, cache, format, root, strip-namespaces
-   wp-select-csv       : csv, separator, enclosure, escape, key_row, col, key_col, key, row, class
-   wp-size-format      : bytes, decimals
-   wp-sanitize-title   : title
-->
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />

<!-- MARK wp-select-csv -->
<!--
    uses XSLT_Callback::getSelectCsv()

    <xsl:call-template name="wp-select-csv">
        <xsl:with-param name="csv">/path/to/local/spreadsheet.csv</xsl:with-param>
        <xsl:with-param name="separator">,</xsl:with-param>
        <xsl:with-param name="enclosure">"</xsl:with-param>
        <xsl:with-param name="escape">\</xsl:with-param>
        <xsl:with-param name="key_row" select="0" />
        <xsl:with-param name="col" select="0" />
        <xsl:with-param name="key_col" select="0" />
        <xsl:with-param name="key" select="''" />
        <xsl:with-param name="row" select="0" />
        <xsl:with-param name="class" select="'table'" />
    </xsl:call-template>
-->
    <xsl:template name="wp-select-csv">
        <xsl:param name="csv"       select="''" />
        <!-- read params -->
        <xsl:param name="separator" select="','" />
        <xsl:param name="enclosure" select="'\&quot;'" />
        <xsl:param name="escape"    select="'\\'" />
        <!-- write params -->
        <xsl:param name="key_row"   select="0" />
        <xsl:param name="col"       select="0" />
        <xsl:param name="key_col"   select="0" />
        <xsl:param name="key"       select="''" />
        <xsl:param name="row"       select="0" />
        <xsl:param name="class"     select="'table'" />
        <!-- <xsl:param name="htmlentities" select="'yes'" /> -->

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"csv" =&gt; "</xsl:text><xsl:value-of select="$csv" /><xsl:text>"</xsl:text>
                <xsl:text>, "separator" =&gt; "</xsl:text><xsl:value-of select="$separator" /><xsl:text>"</xsl:text>
                <xsl:text>, "enclosure" =&gt; "</xsl:text><xsl:value-of select="$enclosure" /><xsl:text>"</xsl:text>
                <xsl:text>, "escape" =&gt; "</xsl:text><xsl:value-of select="$escape" /><xsl:text>"</xsl:text>
                <xsl:text>, "key_row" =&gt; "</xsl:text><xsl:value-of select="$key_row" /><xsl:text>"</xsl:text>
                <xsl:text>, "col" =&gt; "</xsl:text><xsl:value-of select="$col" /><xsl:text>"</xsl:text>
                <xsl:text>, "key_col" =&gt; "</xsl:text><xsl:value-of select="$key_col" /><xsl:text>"</xsl:text>
                <xsl:text>, "key" =&gt; "</xsl:text><xsl:value-of select="$key" /><xsl:text>"</xsl:text>
                <xsl:text>, "row" =&gt; "</xsl:text><xsl:value-of select="$row" /><xsl:text>"</xsl:text>
                <xsl:text>, "class" =&gt; "</xsl:text><xsl:value-of select="$class" /><xsl:text>"</xsl:text>
                <!-- <xsl:text>, "htmlentities" =&gt; "</xsl:text><xsl:value-of select="$htmlentities" /><xsl:text>"</xsl:text> -->
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getSelectCsv',string($SUBPARAMS))/RESULT" />
    </xsl:template>


<!-- MARK wp-post-item -->
<!--
    uses XSLT_Callback : getPostItem()

    <xsl:call-template name="wp-post-item">
        <xsl:with-param name="post">sample-xml</xsl:with-param>
        <xsl:with-param name="type">xml</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="wp-post-item">
        <xsl:param name="post" select="text()" />
        <xsl:param name="type" select="''" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"post" =&gt; "</xsl:text><xsl:value-of select="$post" /><xsl:text>"</xsl:text>
                <xsl:text>, "type" =&gt; "</xsl:text><xsl:value-of select="$type" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getPostItem',string($SUBPARAMS))/RESULT" />
    </xsl:template>


<!-- MARK wp-post-meta -->
<!--
    uses XSLT_Callback : getPostMeta()

    <xsl:call-template name="wp-post-meta">
        <xsl:with-param name="post">sample-xml</xsl:with-param>
        <xsl:with-param name="type">xml</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="wp-post-meta">
        <xsl:param name="post" select="text()" />
        <xsl:param name="type" select="''" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"post" =&gt; "</xsl:text><xsl:value-of select="$post" /><xsl:text>"</xsl:text>
                <xsl:text>, "type" =&gt; "</xsl:text><xsl:value-of select="$type" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getPostMeta',string($SUBPARAMS))/RESULT" />
    </xsl:template>


<!-- MARK wp-sanitize-title -->
<!--
    uses XSLT_Callback : getSanitizeTitle()

    <xsl:call-template name="wp-sanitize-title">
        <xsl:with-param name="title">my title</xsl:with-param>
    </xsl:call-template>
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


<!-- MARK wp-size-format -->
<!--
    <xsl:call-template name="wp-size-format">
        <xsl:with-param name="bytes" select="1024" />
        <xsl:with-param name="decimals" select="2" />
    </xsl:call-template>
-->
    <xsl:template name="wp-size-format">
        <xsl:param name="bytes" select="'0'" />
        <xsl:param name="decimals" select="'2'" />

        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"bytes" =&gt; "</xsl:text><xsl:value-of select="$bytes" /><xsl:text>"</xsl:text>
                <xsl:text>, "decimals" =&gt; "</xsl:text><xsl:value-of select="$decimals" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getSizeFormat',string($SUBPARAMS))/RESULT" />
    </xsl:template>


<!-- MARK wp-select-xml -->
<!--
    uses XSLT_Callback : getSelectXml()

    <xsl:call-template name="wp-select-xml">
        <xsl:with-param name="xml" select="wp-data-xml" />
        <xsl:with-param name="select" select="'/'" />
        <xsl:with-param name="cache" select="5" />
        <xsl:with-param name="format" select="'xml'" />
        <xsl:with-param name="root" select="'ROOT'" />
        <xsl:with-param name="strip-namespaces" select="'no'" />
    </xsl:call-template>
-->
    <xsl:template name="wp-select-xml">
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
        <xsl:copy-of select="php:function('XSLT_Callback','getSelectXml',string($SUBPARAMS))/RESULT" />
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/wp.xsl -->
