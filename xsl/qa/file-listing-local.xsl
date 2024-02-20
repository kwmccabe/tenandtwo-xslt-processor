<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../file.xsl" />
    <xsl:include href="../util.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="path"><xsl:value-of select="path" /></xsl:variable>
            <xsl:variable name="match"><xsl:value-of select="match" /></xsl:variable>
            <xsl:variable name="levels"><xsl:value-of select="levels" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected/*" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="file-listing-local">
                    <xsl:with-param name="path" select="$path" />
                    <xsl:with-param name="match" select="$match" />
                    <xsl:with-param name="levels" select="$levels" />
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
                    <xsl:when test="not(string-length($expected))"><!-- UNKNOWN --></xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />file-listing-local :
                <xsl:if test="string-length($path)"><br /> - path = <xsl:copy-of select="$path" /></xsl:if>
                <xsl:if test="string-length($match)"><br /> - match = <xsl:copy-of select="$match" /></xsl:if>
                <xsl:if test="string-length($levels)"><br /> - levels = <xsl:copy-of select="$levels" /></xsl:if>
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
<!-- end file-listing-local.xsl -->
<!--
[xslt_transform_xml xsl="qa/file-listing-local.xsl"]
<TESTS>
  <TEST>
    <path>case-study-beer</path>
    <match>.xml$</match>
    <levels>10</levels>
    <expected></expected>
  </TEST>
  <TEST>
    <path>.</path>
    <match>.xml$</match>
    <levels>10</levels>
    <expected></expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
