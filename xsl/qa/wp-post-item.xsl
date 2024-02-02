<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../wp.xsl" />
    <xsl:include href="../util.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="post"><xsl:copy-of select="post" /></xsl:variable>
            <xsl:variable name="type"><xsl:copy-of select="type" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected/*" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="wp-post-item">
                    <xsl:with-param name="post" select="$post" />
                    <xsl:with-param name="type" select="$type" />
                </xsl:call-template>
            </xsl:variable>

            <xsl:variable name="p_result">
                <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$result" /></xsl:call-template>
            </xsl:variable>
            <xsl:variable name="p_expected">
                <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$expected" /></xsl:call-template>
            </xsl:variable>

            <!-- compare result against expected -->
            <xsl:variable name="pass">
                <xsl:choose>
                    <xsl:when test="$result = $expected">PASS</xsl:when>
                    <xsl:when test="$p_result = $p_expected">PPASS</xsl:when>
                    <xsl:when test="not(string-length($expected))"><!-- UNKNOWN --></xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />wp-post-item :
                <xsl:if test="string-length($post)"><br /> - post = <xsl:copy-of select="$post" /></xsl:if>
                <xsl:if test="string-length($type)"><br /> - type = <xsl:copy-of select="$type" /></xsl:if>
            </p>

            <p>result : <br />
                <!-- <xsl:copy-of select="$result" /> -->
                <pre><xsl:value-of select="$p_result" /></pre>
            </p>

            <xsl:if test="$pass = 'FAIL'">
                <p>expected : <br />
                    <!-- <xsl:copy-of select="$expected" /> -->
                    <pre><xsl:value-of select="$p_expected" /></pre>
                </p>
            </xsl:if>
        </xsl:for-each>
        <hr size="1" />


    </xsl:template>

</xsl:stylesheet>
<!-- end wp-post-item.xsl -->
<!--
[xslt_transform_xml xsl="qa/wp-post-item.xsl"]
<TESTS>
  <TEST>
    <post>2</post>
  </TEST>
  <TEST>
    <post>sample-xml</post>
    <type>xml</type>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
