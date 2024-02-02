<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
<!--     <xsl:include href="../string.xsl" /> -->
    <xsl:include href="../wp.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="title"><xsl:copy-of select="title" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="wp-sanitize-title">
                    <xsl:with-param name="title" select="$title" />
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
                <br />wp-sanitize-title :
                <br /> - title = <xsl:value-of select="$title" />
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
<!-- end wp-sanitize-title.xsl -->
<!--
[xslt_transform_xml xsl="qa/wp-sanitize-title.xsl"]
<TESTS>
  <TEST>
    <title>Turning Titles! into Slugs?</title>
    <expected>turning-titles-into-slugs</expected>
  </TEST>
  <TEST>
    <title>The Book (by someone)</title>
    <expected>the-book-by-someone</expected>
  </TEST>
  <TEST>
    <title>COOKING / Methods / Barbecue &amp; Grilling</title>
    <expected>cooking-methods-barbecue-grilling</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
