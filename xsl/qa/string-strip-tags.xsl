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
            <xsl:variable name="allowed_tags"><xsl:copy-of select="allowed_tags" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="string-strip-tags">
                    <xsl:with-param name="value"><xsl:copy-of select="$value" /></xsl:with-param>
                    <xsl:with-param name="allowed_tags"><xsl:copy-of select="$allowed_tags" /></xsl:with-param>
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
                <br />string-strip-tags :
                <br /> - value = <xsl:copy-of select="$value" />
                <br /> - allowed_tags = <xsl:copy-of select="$allowed_tags" />
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
<!-- end string-strip-tags.xsl -->
<!--
[xslt_transform_xml xsl="qa/string-strip-tags.xsl"]
<TESTS>
  <TEST>
    <value>&lt;b&gt;HEY&lt;/b&gt;&lt;br/&gt;&lt;i&gt;there&lt;/i&gt;</value>
    <expected>HEY there</expected>
  </TEST>
  <TEST>
    <value>&lt;b&gt;HEY&lt;/b&gt;&lt;br/&gt;&lt;i&gt;there&lt;/i&gt;</value>
    <allowed_tags>&lt;b&gt;</allowed_tags>
    <expected>&lt;b&gt;HEY&lt;/b&gt; there</expected>
  </TEST>
  <TEST>
    <value>&lt;b&gt;HEY&lt;/b&gt;&lt;br/&gt;&lt;i&gt;there&lt;/i&gt;</value>
    <allowed_tags>&lt;i&gt;</allowed_tags>
    <expected>HEY &lt;i&gt;there&lt;/i&gt;</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
