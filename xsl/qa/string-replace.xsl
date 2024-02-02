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
            <xsl:variable name="find"><xsl:copy-of select="find" /></xsl:variable>
            <xsl:variable name="replace"><xsl:copy-of select="replace" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="string-replace">
                    <xsl:with-param name="value"><xsl:copy-of select="$value" /></xsl:with-param>
                    <xsl:with-param name="find"><xsl:copy-of select="$find" /></xsl:with-param>
                    <xsl:with-param name="replace"><xsl:copy-of select="$replace" /></xsl:with-param>
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
                <br />string-replace :
                <br /> - value = <xsl:copy-of select="$value" />
                <br /> - find = <xsl:copy-of select="$find" />
                <br /> - replace = <xsl:copy-of select="$replace" />
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
<!-- end string-replace.xsl -->
<!--
[xslt_transform_xml xsl="qa/string-replace.xsl"]
<TESTS>
  <TEST>
    <value>it was me</value>
    <find>me</find>
    <replace>him</replace>
    <expected>it was him</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
