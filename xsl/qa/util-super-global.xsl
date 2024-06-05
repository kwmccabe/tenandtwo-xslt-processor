<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../string.xsl" />
    <xsl:include href="../util.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="global"><xsl:value-of select="global" /></xsl:variable>
            <xsl:variable name="index"><xsl:value-of select="index" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected/*" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="util-super-global">
                    <xsl:with-param name="global" select="$global" />
                    <xsl:with-param name="index" select="$index" />
                </xsl:call-template>
            </xsl:variable>

            <xsl:variable name="p_result">
                <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$result" /></xsl:call-template>
            </xsl:variable>
            <xsl:variable name="p_expected">
                <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$expected" /></xsl:call-template>
            </xsl:variable>

            <!-- compare result against expected -->
            <xsl:variable name="pass">
                <xsl:choose>
                    <xsl:when test="$result = $expected">PASS</xsl:when>
                    <xsl:when test="$p_result = $p_expected">PPASS</xsl:when>
                    <xsl:when test="not(string-length($p_expected))"><!-- UNKNOWN --></xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />util-super-global :
                <br /> - global = <xsl:copy-of select="$global" />
                <br /> - index = <xsl:copy-of select="$index" />
            </p>

            <p>result : <br />
                <!-- <xsl:copy-of select="$result" /> -->
                <pre><xsl:value-of select="$p_result" /></pre>
            </p>

            <xsl:if test="$pass = 'FAIL'">
                <p>expected : <br />
                    <!-- <xsl:copy-of select="$expected" /> -->
                    <pre><xsl:value-of select="$p_expected" /></pre>
                </p>
            </xsl:if>
        </xsl:for-each>
        <hr size="1" />


    </xsl:template>

</xsl:stylesheet>
<!-- end util-super-global.xsl -->
<!--
[xslt_transform_xml xsl="qa/util-super-global.xsl"]
<TESTS>
  <TEST>
    <global>_SERVER</global>
    <index>DOCUMENT_ROOT</index>
    <expected><RESULT template="util-super-global" global="_SERVER" index="DOCUMENT_ROOT">/srv/plugins.tenandtwo.com/htdocs</RESULT></expected>
  </TEST>
  <TEST>
    <global>_REQUEST</global>
    <expected><RESULT template="util-super-global" global="_REQUEST" index="" count="0"/></expected>
  </TEST>
  <TEST>
    <global>_COOKIE</global>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
