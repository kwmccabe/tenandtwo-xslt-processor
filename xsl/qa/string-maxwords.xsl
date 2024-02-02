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
            <xsl:variable name="max"><xsl:value-of select="max" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="string-maxwords">
                    <xsl:with-param name="value"><xsl:copy-of select="$value" /></xsl:with-param>
                    <xsl:with-param name="max"><xsl:copy-of select="$max" /></xsl:with-param>
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
                <br />string-maxwords :
                <br /> - value = <xsl:copy-of select="$value" />
                <br /> - max = <xsl:copy-of select="$max" />
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
<!-- end string-maxwords.xsl -->
<!--
[xslt_transform_xml xsl="qa/string-maxwords.xsl"]
<TESTS>
  <TEST>
    <value>what's the fuss</value>
    <max>2</max>
    <expected>what's the…</expected>
  </TEST>
  <TEST>
    <value><b>what's</b> the big idea?</value>
    <max>3</max>
    <expected>what's the big…</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
