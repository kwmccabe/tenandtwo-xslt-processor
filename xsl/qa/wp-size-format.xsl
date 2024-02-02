<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../wp.xsl" />
    <xsl:include href="../util.xsl" />
    <!-- <xsl:include href="../string.xsl" /> -->

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="bytes"><xsl:value-of select="bytes" /></xsl:variable>
            <xsl:variable name="decimals"><xsl:value-of select="decimals" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="wp-size-format">
                    <xsl:with-param name="bytes" select="$bytes" />
                    <xsl:with-param name="decimals" select="$decimals" />
                </xsl:call-template>
            </xsl:variable>

            <!-- compare result against expected -->
            <xsl:variable name="pass">
                <xsl:choose>
                    <xsl:when test="$result = $expected">PASS</xsl:when>
                    <xsl:when test="not(string-length($expected))">UNKNOWN</xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />wp-size-format :
                <xsl:if test="string-length($bytes)"><br /> - bytes = <xsl:copy-of select="$bytes" /></xsl:if>
                <xsl:if test="string-length($decimals)"><br /> - decimals = <xsl:copy-of select="$decimals" /></xsl:if>
            </p>

            <p>result : <br />
                <xsl:copy-of select="$result" />
                <!-- xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$result" /></xsl:call-template -->
            </p>

            <xsl:if test="$pass = 'FAIL'">
                <p>expected : <br />
                    <xsl:copy-of select="$expected" />
                    <!-- xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$expected" /></xsl:call-template -->
                </p>
            </xsl:if>
        </xsl:for-each>
        <hr size="1" />


    </xsl:template>

</xsl:stylesheet>
<!-- end wp-size-format.xsl -->
<!--
[xslt_transform_xml xsl="qa/wp-size-format.xsl"]
<TESTS>
  <TEST>
    <bytes>1024</bytes>
    <expected>1 KB</expected>
  </TEST>
  <TEST>
    <bytes>1234567890</bytes>
    <expected>1.15 GB</expected>
  </TEST>
  <TEST>
    <bytes>12345678901234567890</bytes>
    <expected>8 Exabytes</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
