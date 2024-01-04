<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../date.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="date-microtime" />
            </xsl:variable>

            <!-- compare result against expected (not empty) -->
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
                <br />date-microtime
                <br /> - no parameters
            </p>

            <p>result : <br />
                <xsl:copy-of select="$result" />
            </p>

        </xsl:for-each>
        <hr size="1" />


    </xsl:template>

</xsl:stylesheet>
<!-- end datemicrotime.xsl -->
<!--
[xsl_transform xsl="qa/date-microtime.xsl"]
<TESTS>
  <TEST />
</TESTS>
[/xsl_transform]
-->
