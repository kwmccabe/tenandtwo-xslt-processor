<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../string.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="value"><xsl:copy-of select="value" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="string-title-case">
                    <xsl:with-param name="value" select="$value" />
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
                <br />string-title-case :
                <br /> - value = <xsl:copy-of select="$value" />
            </p>

            <p>result : <br />
                <xsl:copy-of select="$result" />
            </p>

            <xsl:if test="$pass = 'FAIL'">
                <p>expected : <br />
                    <xsl:copy-of select="$expected" />
                </p>
            </xsl:if>
        </xsl:for-each>
        <hr size="1" />


    </xsl:template>

</xsl:stylesheet>
<!-- end string-title-case.xsl -->
<!--
[xslt_transform xsl="qa/string-title-case.xsl"]
<TESTS>
  <TEST>
    <value>every good boy does fine</value>
    <expected>Every Good Boy Does Fine</expected>
  </TEST>
</TESTS>
[/xslt_transform]
-->
