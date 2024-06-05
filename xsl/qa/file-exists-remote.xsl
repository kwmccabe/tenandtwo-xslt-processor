<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../file.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="url"><xsl:value-of select="url" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="file-exists-remote">
                    <xsl:with-param name="url" select="$url" />
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
                <br />file-exists-remote :
                <br /> - url = <xsl:copy-of select="$url" />
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
[xslt_transform_xml xsl="qa/file-exists-remote.xsl"]
<TESTS>
<TEST>
  <url>https://tenandtwo.com/</url>
  <expected>https://tenandtwo.com/</expected>
</TEST>
<TEST>
  <url>https://plugins.tenandtwo.com/wp-content/uploads/upload_sample.xml</url>
  <expected>https://plugins.tenandtwo.com/wp-content/uploads/upload_sample.xml</expected>
</TEST>
<TEST>
  <url>https://nope.tenandtwo.com/</url>
  <expected>cURL error 6: Could not resolve host: nope.tenandtwo.com</expected>
</TEST>
<TEST>
  <url>goop</url>
  <expected>A valid URL was not provided.</expected>
</TEST>
</TESTS>
[/xslt_transform_xml]
-->
