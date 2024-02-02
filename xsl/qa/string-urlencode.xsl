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
                <xsl:call-template name="string-urlencode">
                    <xsl:with-param name="value"><xsl:copy-of select="$value" /></xsl:with-param>
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
                <br />string-urlencode :
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
<!-- end string-urlencode.xsl -->
<!--
[xslt_transform_xml xsl="qa/string-urlencode.xsl"]
<TESTS>
  <TEST>
    <value>http://example.com/test#car</value>
    <expected>http%3A%2F%2Fexample.com%2Ftest%23car</expected>
  </TEST>
  <TEST>
    <value>http://example.com/test?a=b&amp;c=d</value>
    <expected>http%3A%2F%2Fexample.com%2Ftest%3Fa%3Db%26c%3Dd</expected>
  </TEST>
  <TEST>
    <value>&amp; $%+,/:;=?@#</value>
    <expected>%26+%24%25%2B%2C%2F%3A%3B%3D%3F%40%23</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
