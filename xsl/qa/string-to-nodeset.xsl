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
            <xsl:variable name="value"><xsl:value-of select="value" /></xsl:variable>
            <xsl:variable name="delimiter"><xsl:value-of select="delimiter" />
                <xsl:if test="not(string-length(delimiter))">|</xsl:if>
            </xsl:variable>
            <xsl:variable name="nodename"><xsl:value-of select="nodename" />
                <xsl:if test="not(string-length(nodename))">NODE</xsl:if>
            </xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected/*" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="string-to-nodeset">
                    <xsl:with-param name="value" select="$value" />
                    <xsl:with-param name="delimiter" select="$delimiter" />
                    <xsl:with-param name="nodename" select="$nodename" />
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
                    <xsl:when test="not(string-length($p_expected))">UNKNOWN</xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />string-to-nodeset :
                <xsl:if test="string-length($value)"><br /> - value = <xsl:copy-of select="$value" /></xsl:if>
                <xsl:if test="string-length($delimiter)"><br /> - delimiter = <xsl:copy-of select="$delimiter" /></xsl:if>
                <xsl:if test="string-length($nodename)"><br /> - nodename = <xsl:copy-of select="$nodename" /></xsl:if>
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
<!-- end string-to-nodeset.xsl -->
<!--
[xslt_transform_xml xsl="qa/string-to-nodeset.xsl"]
<TESTS>
  <TEST>
    <value>1|2|3</value>
    <delimiter>|</delimiter>
    <nodename>NODE</nodename>
    <expected><RESULT> <NODE>1</NODE> <NODE>2</NODE> <NODE>3</NODE> </RESULT></expected>
  </TEST>
  <TEST>
    <value>one,two,three</value>
    <delimiter>,</delimiter>
    <nodename>OPTION</nodename>
    <expected><RESULT> <OPTION>one</OPTION> <OPTION>two</OPTION> <OPTION>three</OPTION> </RESULT></expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
