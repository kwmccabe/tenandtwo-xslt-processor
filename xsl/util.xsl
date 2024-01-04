<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="exslt php"
    >
<!--
*   util-super-global    : global, index
-   util-hash-data       : method, data, raw_output

    current-node-path       : .
    current-node-input-path : .

    util-nodeset-to-php     : nodes
    util-print-node-names   : nodes
    util-print-nodes        : nodes
-->
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />

<!-- MARK util-super-global -->
<!--
	PHP SuperGlobals:  _REQUEST ; _SERVER ; _FILES ; _COOKIE ; _SESSION

sample:
	<xsl:variable name="_SESSION">
		<xsl:call-template name="util-super-global">
			<xsl:with-param name="global" select="'_SESSION'" />
			<xsl:with-param name="index">['myindex']</xsl:with-param>
		</xsl:call-template>
	</xsl:variable>
	<xsl:value-of select="exslt:node-set($_REQUEST)/RESULT/test_key" />
-->
	<xsl:template name="util-super-global">
		<xsl:param name="global" select="'_REQUEST'" />
		<xsl:param name="index" select="''" />
        <xsl:variable name="SUBPARAMS">
			<xsl:text>$params = array(</xsl:text>
				<xsl:text>"global" =&gt; "</xsl:text><xsl:value-of select="$global" /><xsl:text>"</xsl:text>
				<xsl:text>, "index" =&gt; "</xsl:text><xsl:value-of select="$index" /><xsl:text>"</xsl:text>
			<xsl:text>);</xsl:text>
		</xsl:variable>
        <xsl:copy-of select="php:function('XSLT_Callback','getSuperGlobal',string($SUBPARAMS))/RESULT" />
	</xsl:template>


<!-- MARK util-hash-data -->
<!--
    <xsl:call-template name="util-hash-data">
        <xsl:with-param name="data">string to encode</xsl:with-param>
    </xsl:call-template>
-->
    <xsl:template name="util-hash-data">
        <xsl:param name="method" select="'md5'" />
        <xsl:param name="data" select="''" />
        <xsl:param name="raw_output" select="'0'" />
        <xsl:value-of select="php:function('hash',string($method),string($data),string($raw_output))" />
    </xsl:template>


<!-- MARK current-node-path -->
<!--
    <xsl:call-template name="current-node-path" />
-->
    <xsl:template name="current-node-path">

        <xsl:for-each select="ancestor-or-self::*">
            <xsl:value-of select="concat('/',local-name())"/>
            <!-- only if needed -->
            <xsl:if test="(preceding-sibling::*|following-sibling::*)[local-name()=local-name(current())]">
                <xsl:value-of select="concat('[',count(preceding-sibling::*[local-name()=local-name(current())])+1,']')"/>
            </xsl:if>
        </xsl:for-each>

    </xsl:template>

<!-- MARK current-node-input-path -->
<!--
    <xsl:call-template name="getNodeFormName" />
-->
    <xsl:template name="current-node-input-path">

        <xsl:text>xml</xsl:text>
        <xsl:for-each select="ancestor-or-self::*">
            <xsl:value-of select="concat('[',local-name(),']')"/>
            <xsl:value-of select="concat('[',count(preceding-sibling::*[local-name()=local-name(current())]),']')"/>
        </xsl:for-each>

    </xsl:template>


<!-- MARK util-nodeset-to-php -->
<!--
    transform nodes to string for php : eval("\$result = array(".$subresult.");");
    see XSLT_Processor_XML::transcode_xml()
-->
    <xsl:template name="util-nodeset-to-php">
        <xsl:param name="nodes" select="." />

        <!-- xsl:for-each select="exslt:node-set($nodes)/*[not(count(preceding-sibling::*[name() = current()]))]" -->
        <!-- xsl:for-each select="exslt:node-set($nodes)/*[not(preceding-sibling::*/name()=name())]" -->
        <xsl:for-each select="exslt:node-set($nodes)/*">
            <xsl:variable name="name" select="name()" />

            <xsl:if test="count(preceding-sibling::*[name() = $name]) = 0">
                <xsl:text>"</xsl:text>
                <xsl:value-of select="$name" />
                <xsl:text>" =&gt; array(</xsl:text>

                <xsl:for-each select="exslt:node-set($nodes)/*[name() = $name]">
                    <xsl:call-template name="node-to-php">
                        <xsl:with-param name="idx" select="count(preceding-sibling::*[name() = $name])" />
                    </xsl:call-template>
                </xsl:for-each>

                <xsl:text>),</xsl:text>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="node-to-php">
        <xsl:param name="idx" select="0" />

        <xsl:value-of select="$idx" />
        <xsl:text> =&gt; array(</xsl:text>

        <xsl:if test="count(./@*) &gt; 0">
            <xsl:text>"attributes" =&gt; array(</xsl:text>

            <xsl:for-each select="./@*">
                <xsl:text>"</xsl:text>
                <xsl:value-of select="name()" />
                <xsl:text>" =&gt; "</xsl:text>
                <xsl:call-template name="string-addslashes">
                    <xsl:with-param name="value" select="normalize-space(.)" />
                </xsl:call-template>
                <xsl:text>",</xsl:text>
            </xsl:for-each>

            <xsl:text>),</xsl:text>
        </xsl:if>

        <xsl:choose>
            <xsl:when test="count(./*) &gt; 0">
                <xsl:call-template name="util-nodeset-to-php" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>"cdata" =&gt; "</xsl:text>
                <xsl:call-template name="string-addslashes">
                    <xsl:with-param name="value" select="normalize-space(.)" />
                </xsl:call-template>
                <xsl:text>"</xsl:text>
            </xsl:otherwise>
        </xsl:choose>

        <xsl:text>),</xsl:text>
    </xsl:template>


<!-- MARK util-print-node-names -->
<!--
    <xsl:call-template name="util-print-node-names" />
    or
    <xsl:call-template name="util-print-node-names"><xsl:with-param name="nodes" select="$VARIABLE" /></xsl:call-template>
-->
    <xsl:template name="util-print-node-names">
        <xsl:param name="nodes" select="." />
        <xsl:param name="lead" select="''" />

        <xsl:variable name="newline">
            <xsl:text>
</xsl:text><xsl:value-of select="$lead" />
        </xsl:variable>

        <xsl:for-each select="exslt:node-set($nodes)/*">

            <xsl:if test="$lead = '' and position() = 1 and string-length(name(..))">
                <xsl:value-of select="name(..)" />
            </xsl:if>

            <xsl:value-of select="concat($newline,'-')" />
            <xsl:choose>
                <xsl:when test="count(*) &gt; 0">
                    <xsl:value-of select="name()" />

                    <xsl:call-template name="util-print-node-names">
                        <xsl:with-param name="lead" select="concat($lead,'-')" />
                    </xsl:call-template>

                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="name()" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:template>

<!-- MARK util-print-nodes -->
<!--
    <xsl:call-template name="util-print-nodes" />
    or
    <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$VARIABLE" /></xsl:call-template>
-->
    <xsl:template name="util-print-nodes">
        <xsl:param name="nodes" select="." />
        <xsl:param name="lead" select="''" />

        <xsl:variable name="newline">
            <xsl:text>
</xsl:text><xsl:value-of select="$lead" />
        </xsl:variable>

        <xsl:for-each select="exslt:node-set($nodes)/*|text()[string-length(normalize-space(.)) &gt; 0]">

            <xsl:if test="$lead = '' and position() = 1 and string-length(name(..))">
                <xsl:text>&lt;</xsl:text>
                <xsl:value-of select="name(..)" />

                <xsl:for-each select="../@*">
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="name()" />
                    <xsl:text>=&quot;</xsl:text>
                    <xsl:value-of select="." />
                    <xsl:text>&quot;</xsl:text>
                </xsl:for-each>

                <xsl:text>&gt;</xsl:text>
            </xsl:if>

            <xsl:value-of select="concat($newline,'  ')" />
            <xsl:choose>
                <xsl:when test="count(*) &gt; 0">
                    <xsl:text>&lt;</xsl:text>
                    <xsl:value-of select="name()" />

                    <xsl:for-each select="./@*">
                        <xsl:text> </xsl:text>
                        <xsl:value-of select="name()" />
                        <xsl:text>=&quot;</xsl:text>
                        <xsl:value-of select="." />
                        <xsl:text>&quot;</xsl:text>
                    </xsl:for-each>

                    <xsl:text>&gt;</xsl:text>

                    <xsl:call-template name="util-print-nodes">
                        <xsl:with-param name="lead" select="concat($lead,'  ')" />
                    </xsl:call-template>

                    <xsl:value-of select="concat($newline,'  ')" />
                    <xsl:text>&lt;/</xsl:text>
                    <xsl:value-of select="name()" />
                    <xsl:text>&gt;</xsl:text>

                </xsl:when>
                <xsl:when test="string-length(normalize-space(.)) &gt; 0">
                    <xsl:if test="string-length(name()) &gt; 0">
                        <xsl:text>&lt;</xsl:text>
                        <xsl:value-of select="name()" />

                        <xsl:for-each select="@*">
                            <xsl:text> </xsl:text>
                            <xsl:value-of select="name()" />
                            <xsl:text>=&quot;</xsl:text>
                            <xsl:value-of select="." />
                            <xsl:text>&quot;</xsl:text>
                        </xsl:for-each>

                        <xsl:text>&gt;</xsl:text>
                    </xsl:if>

                    <xsl:value-of select="normalize-space(.)" />

                    <xsl:if test="string-length(name()) &gt; 0">
                        <xsl:text>&lt;/</xsl:text>
                        <xsl:value-of select="name()" />
                        <xsl:text>&gt;</xsl:text>
                    </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>&lt;</xsl:text>
                    <xsl:value-of select="name()" />

                    <xsl:for-each select="@*">
                        <xsl:text> </xsl:text>
                        <xsl:value-of select="name()" />
                        <xsl:text>=&quot;</xsl:text>
                        <xsl:value-of select="." />
                        <xsl:text>&quot;</xsl:text>
                    </xsl:for-each>

                    <xsl:text>/&gt;</xsl:text>
                </xsl:otherwise>

            </xsl:choose>

            <xsl:if test="$lead = '' and position() = last() and string-length(name(..))">
                <xsl:value-of select="$newline" />
                <xsl:text>&lt;/</xsl:text>
                <xsl:value-of select="name(..)" />
                <xsl:text>&gt;</xsl:text>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/util.xsl -->
