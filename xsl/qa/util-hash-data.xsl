<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../string.xsl" />
    <xsl:include href="../util.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="method"><xsl:value-of select="method" /></xsl:variable>
            <xsl:variable name="data"><xsl:copy-of select="data" /></xsl:variable>
            <xsl:variable name="raw_output"><xsl:value-of select="raw_output" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="util-hash-data">
                    <xsl:with-param name="method" select="$method" />
                    <xsl:with-param name="data" select="$data" />
                    <xsl:with-param name="raw_output" select="$raw_output" />
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
                <br />util-hash-data :
                <xsl:if test="string-length($method)"><br /> - method = <xsl:copy-of select="$method" /></xsl:if>
                <xsl:if test="string-length($data)"><br /> - data = <xsl:copy-of select="$data" /></xsl:if>
                <xsl:if test="string-length($raw_output)"><br /> - raw_output = <xsl:copy-of select="$raw_output" /></xsl:if>
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
<!-- end util-hash-data.xsl -->
<!--
[xslt_transform_xml xsl="qa/util-hash-data.xsl"]
<TESTS>
  <TEST>
    <method>md5</method>
    <data>http://plugins.tenandtwo.com/</data>
    <raw_output>0</raw_output>
    <expected>7b547cdaba126f1e182d1375c6ac4e70</expected>
  </TEST>
  <TEST>
    <method>sha512</method>
    <data>corn beef on rye</data>
    <raw_output>0</raw_output>
    <expected>9c11cfe1363c710aca75c222a6ef957000954fc12705e20e4e01bed34cba446fa44f7da429bef272f5fa2f9be9386d32eff3dc6b7d72f6bee4bd30f1cf132a2a</expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
