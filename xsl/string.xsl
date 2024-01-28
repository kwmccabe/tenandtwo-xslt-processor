<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exslt="http://exslt.org/common"
	xmlns:php="http://php.net/xsl"
	exclude-result-prefixes="exslt php"
	>
<!--
-   string-upper          : value
-   string-lower          : value
-   string-title-case     : value

	string-maxlength      : value, max
	string-maxwords       : value, max, delimiter

-   string-trim           : value
-   string-ltrim          : value
-   string-rtrim          : value

-	string-replace        : value, find, replace

-   string-nl2br          : value
-	string-addslashes     : value
-	string-urlencode      : value

*   string-entity-decode  : value
*   string-strip-tags     : value, tags

    string-to-nodeset     : value, delimiter, nodename

-->
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />

    <xsl:variable name="uppercase_char">ABCDEFGHIJKLMNOPQRSTUVWXYZAAAAAAACEEEEIIIIDNOOOOOOUUUUYYYZ</xsl:variable>
    <xsl:variable name="lowercase_char">abcdefghijklmnopqrstuvwxyzaaaaaaaceeeeiiiidnoooooouuuuyyyz</xsl:variable>
    <xsl:variable name="uppercase_utf8">ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞŸŽ</xsl:variable>
    <xsl:variable name="lowercase_utf8">abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿž</xsl:variable>

<!-- MARK string-upper -->
<!--
@uses mb_convert_case(string $string, int $mode, ?string $encoding = null): string
    (0)MB_CASE_UPPER, (1)MB_CASE_LOWER, (2)MB_CASE_TITLE, (3)MB_CASE_FOLD,
    (4)MB_CASE_UPPER_SIMPLE, (5)MB_CASE_LOWER_SIMPLE, (6)MB_CASE_TITLE_SIMPLE, (7)MB_CASE_FOLD_SIMPLE
@see https://www.php.net/manual/en/function.mb-convert-case.php
-->
	<xsl:template name="string-upper">
		<xsl:param name="value" select="text()" />
		<xsl:param name="encoding" select="'UTF-8'" />
        <xsl:value-of select="php:function('mb_convert_case',string($value),0,string($encoding))" />
	</xsl:template>

<!-- MARK string-lower -->
	<xsl:template name="string-lower">
		<xsl:param name="value" select="text()" />
		<xsl:param name="encoding" select="'UTF-8'" />
        <xsl:value-of select="php:function('mb_convert_case',string($value),1,string($encoding))" />
	</xsl:template>

<!-- MARK string-title-case -->
	<xsl:template name="string-title-case">
		<xsl:param name="value" select="text()" />
		<xsl:param name="encoding" select="'UTF-8'" />
        <xsl:value-of select="php:function('mb_convert_case',string($value),2,string($encoding))" />
	</xsl:template>


<!-- MARK string-maxlength -->
	<xsl:template name="string-maxlength">
		<xsl:param name="value" select="text()" />
		<xsl:param name="max" select="31" />

		<xsl:choose>
			<xsl:when test="string-length($value) &gt; $max">
				<xsl:variable name="result">
					<xsl:call-template name="string-rtrim">
						<xsl:with-param name="value" select="substring($value,1,$max - 1)" />
					</xsl:call-template>
				</xsl:variable>
				<xsl:value-of disable-output-escaping="yes" select="concat($result,'…')" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of disable-output-escaping="yes" select="$value" />
			</xsl:otherwise>
		</xsl:choose>

	</xsl:template>

<!-- MARK string-maxwords -->
	<xsl:template name="string-maxwords">
		<xsl:param name="value" select="text()" />
		<xsl:param name="max" select="10" />
		<xsl:param name="delimiter" select="' '" />

		<xsl:if test="string-length($value) &gt; 0">
			<xsl:variable name="words">
				<xsl:call-template name="string-to-nodeset"> <!-- string.xsl -->
				   <xsl:with-param name="value" select="$value" />
				   <xsl:with-param name="delimiter" select="$delimiter" />
				</xsl:call-template>
			</xsl:variable>
			<xsl:variable name="word_cnt">
			    <xsl:value-of select="count(exslt:node-set($words)//NODE)" />
			</xsl:variable>
			<xsl:variable name="word_max">
			    <xsl:choose>
                    <xsl:when test="$max &gt; 0">
                        <xsl:value-of select="$max" />
                    </xsl:when>
                    <xsl:when test="$max &lt; 0">
                        <xsl:value-of select="($word_cnt + $max)" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$word_cnt" />
                    </xsl:otherwise>
			    </xsl:choose>
			</xsl:variable>

			<xsl:choose>
				<xsl:when test="$word_cnt &gt; $word_max">
					<xsl:for-each select="exslt:node-set($words)//NODE[position() &lt;= $word_max]">
						<xsl:value-of disable-output-escaping="yes" select="node()" />
						<xsl:if test="position() &lt; $word_max"><xsl:value-of select="$delimiter" /></xsl:if>
					</xsl:for-each>

					<xsl:if test="not(substring(exslt:node-set($words)//NODE[position() = $word_max],string-length(exslt:node-set($words)//NODE[position() = $word_max])) = '.')">
						<xsl:text>…</xsl:text>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of disable-output-escaping="yes" select="$value" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>

	</xsl:template>


<!-- MARK string-trim -->
	<xsl:template name="string-trim">
		<xsl:param name="value" select="text()" />
        <xsl:value-of select="php:function('trim',string($value))" />
	</xsl:template>

<!-- MARK string-ltrim -->
	<xsl:template name="string-ltrim">
		<xsl:param name="value" select="text()" />
        <xsl:value-of select="php:function('ltrim',string($value))" />
	</xsl:template>

<!-- MARK string-rtrim -->
	<xsl:template name="string-rtrim">
		<xsl:param name="value" select="text()" />
        <xsl:value-of select="php:function('rtrim',string($value))" />
	</xsl:template>

<!-- MARK string-replace -->
	<xsl:template name="string-replace">
		<xsl:param name="value" select="text()" />
		<xsl:param name="find" select="''" />
		<xsl:param name="replace" select="''" />
        <xsl:value-of select="php:function('str_replace',string($find),string($replace),string($value))" />
	</xsl:template>

<!-- MARK string-nl2br -->
<!--
    replace newline with <br />
-->
	<xsl:template name="string-nl2br">
		<xsl:param name="value" select="text()" />
        <xsl:value-of select="php:function('nl2br',string($value))" />
	</xsl:template>

<!-- MARK string-addslashes -->
<!--
    in:  \  "  $
    out: \\ \" \$
-->
	<xsl:template name="string-addslashes">
		<xsl:param name="value" select="text()" />
		<xsl:variable name="result_00">
            <xsl:value-of select="php:function('str_replace','\','\\',string($value))" />
		</xsl:variable>
		<xsl:variable name="result_01">
            <xsl:value-of select="php:function('str_replace','&quot;','\&quot;',string($result_00))" />
		</xsl:variable>
		<xsl:variable name="result_02">
            <xsl:value-of select="php:function('str_replace','$','\$',string($result_01))" />
		</xsl:variable>
        <xsl:value-of disable-output-escaping="yes" select="$result_02" />
	</xsl:template>

<!-- MARK string-urlencode -->
<!--
    in:  %   $   &   +   ,   /   :   ;   =   ?   @   #   [space]
    out: %25 %24 %26 %2B %2C %2F %3A %3B %3D %3F %40 %23 +
-->
	<xsl:template name="string-urlencode">
		<xsl:param name="value" select="text()" />
        <xsl:value-of select="php:function('urlencode',string($value))" />
	</xsl:template>

<!-- MARK string-entity-decode -->
	<xsl:template name="string-entity-decode">
		<xsl:param name="value" select="text()" />
        <!-- <xsl:copy-of select="php:function('html_entity_decode',string($value))" /> -->

        <xsl:variable name="pvalue">
            <xsl:call-template name="string-addslashes"><xsl:with-param name="value" select="$value" /></xsl:call-template>
        </xsl:variable>
        <xsl:variable name="SUBPARAMS">
            <xsl:text>$params = array(</xsl:text>
                <xsl:text>"value" =&gt; "</xsl:text><xsl:value-of select="$pvalue" /><xsl:text>"</xsl:text>
            <xsl:text>);</xsl:text>
        </xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getHtmlEntityDecode',string($SUBPARAMS))/RESULT" />
	</xsl:template>

<!-- MARK string-strip-tags -->
	<xsl:template name="string-strip-tags">
		<xsl:param name="value" select="text()" />
		<xsl:param name="allowed_tags" select="''" />
		<xsl:variable name="pvalue">
			<xsl:call-template name="string-addslashes"><xsl:with-param name="value" select="$value" /></xsl:call-template>
		</xsl:variable>

		<xsl:variable name="SUBPARAMS">
			<xsl:text>$params = array(</xsl:text>
				<xsl:text>"value" =&gt; "</xsl:text><xsl:value-of select="$pvalue" /><xsl:text>"</xsl:text>
				<xsl:text>, "allowed_tags" =&gt; "</xsl:text><xsl:value-of select="$allowed_tags" /><xsl:text>"</xsl:text>
			<xsl:text>);</xsl:text>
		</xsl:variable>

		<xsl:copy-of select="php:function('XSLT_Callback','getStripTags',string($SUBPARAMS))/RESULT" />
	</xsl:template>


<!-- MARK string-to-nodeset -->
<!--
    transform "one|two|three" to "<RESULT><NODE>one</NODE><NODE>two</NODE><NODE>three</NODE></RESULT>"
-->
	<xsl:template name="string-to-nodeset">
		<xsl:param name="value" select="text()" />
		<xsl:param name="delimiter" select="'|'" />
		<xsl:param name="nodename" select="'NODE'" />

		<xsl:element name="RESULT">
			<xsl:call-template name="string-to-nodes">
				<xsl:with-param name="value" select="$value" />
				<xsl:with-param name="delimiter" select="$delimiter" />
				<xsl:with-param name="nodename" select="$nodename" />
			</xsl:call-template>
		</xsl:element>
	</xsl:template>

	<xsl:template name="string-to-nodes">
		<xsl:param name="value" select="text()" />
		<xsl:param name="delimiter" />
		<xsl:param name="nodename" />

		<xsl:variable name="first">
			<xsl:choose>
				<xsl:when test="contains($value,$delimiter)">
					<xsl:value-of select="substring-before($value,$delimiter)" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$value" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="last">
			<xsl:if test="contains($value,$delimiter)">
				<xsl:choose>
					<xsl:when test="contains(substring-after($value,$delimiter),$delimiter)">
						<xsl:call-template name="string-to-nodes">
							<xsl:with-param name="value" select="substring-after($value,$delimiter)" />
							<xsl:with-param name="delimiter" select="$delimiter" />
							<xsl:with-param name="nodename" select="$nodename" />
						</xsl:call-template>
					</xsl:when>
					<xsl:otherwise>
						<xsl:element name="{$nodename}">
							<xsl:value-of select="substring-after($value,$delimiter)" />
						</xsl:element>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
		</xsl:variable>

		<xsl:element name="{$nodename}">
			<xsl:value-of disable-output-escaping="yes" select="$first" />
		</xsl:element>

		<xsl:copy-of select="$last" />
	</xsl:template>

</xsl:stylesheet>
<!-- end xsl/string.xsl -->
