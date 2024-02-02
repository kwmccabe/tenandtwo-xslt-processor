<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../date.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="time"><xsl:value-of select="time" /></xsl:variable>
            <xsl:variable name="value"><xsl:value-of select="value" /></xsl:variable>
            <xsl:variable name="shift"><xsl:value-of select="shift" /></xsl:variable>
            <xsl:variable name="format"><xsl:value-of select="format" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="date-format">
                    <xsl:with-param name="time" select="$time" />
                    <xsl:with-param name="value" select="$value" />
                    <xsl:with-param name="shift" select="$shift" />
                    <xsl:with-param name="format" select="$format" />
                </xsl:call-template>
            </xsl:variable>

            <!-- compare result against expected -->
            <xsl:variable name="pass">
                <xsl:choose>
                    <xsl:when test="$result = $expected">PASS</xsl:when>
                    <xsl:when test="not(string-length($expected))"><!-- UNKNOWN --></xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />date-format :
                <xsl:if test="string-length($time)"><br /> - time = <xsl:copy-of select="$time" /></xsl:if>
                <xsl:if test="string-length($value)"><br /> - value = <xsl:copy-of select="$value" /></xsl:if>
                <xsl:if test="string-length($shift)"><br /> - shift = <xsl:copy-of select="$shift" /></xsl:if>
                <xsl:if test="string-length($format)"><br /> - format = <xsl:copy-of select="$format" /></xsl:if>
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
<!-- end file-exists-remote.xsl -->
<!--
[xslt_transform_xml xsl="qa/date-format.xsl"]
<TESTS>
  <TEST/>
  <TEST>
    <value>now</value>
    <format>l, F d Y, h:i A</format>
  </TEST>
  <TEST>
    <value>today</value>
    <format>Y</format>
    <expected>2024</expected>
  </TEST>
  <TEST>
    <time>1672531200</time>
    <format>Y-m-d</format>
    <expected>2023-01-01</expected>
  </TEST>
  <TEST>
    <value>2024-01-01</value>
    <shift>+1 weeks</shift>
    <format>Y-m-d</format>
    <expected>2024-01-08</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
