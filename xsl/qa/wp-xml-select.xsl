<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exslt="http://exslt.org/common"
    exclude-result-prefixes="exslt"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../util.xsl" />
    <xsl:include href="../wp.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="xml"><xsl:value-of select="xml" /></xsl:variable>
            <xsl:variable name="select"><xsl:value-of select="select" /></xsl:variable>
            <xsl:variable name="cache"><xsl:value-of select="cache" /></xsl:variable>
            <xsl:variable name="format"><xsl:value-of select="format" /></xsl:variable>
            <xsl:variable name="root"><xsl:value-of select="root" /></xsl:variable>
            <!-- <xsl:variable name="strip-declaration"><xsl:value-of select="strip-declaration" /></xsl:variable> -->
            <xsl:variable name="strip-namespaces"><xsl:value-of select="strip-namespaces" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="wp-xml-select">
                    <xsl:with-param name="xml"    select="$xml" />
                    <xsl:with-param name="select" select="$select" />
                    <xsl:with-param name="cache" select="$cache" />
                    <xsl:with-param name="format" select="$format" />
                    <xsl:with-param name="root"   select="$root" />
                    <!-- <xsl:with-param name="strip-declaration" select="$strip-declaration" /> -->
                    <xsl:with-param name="strip-namespaces" select="$strip-namespaces" />
                </xsl:call-template>
            </xsl:variable>

            <!-- compare result against expected -->
            <xsl:variable name="pass">
                <xsl:choose>
                    <xsl:when test="$result = $expected">PASS</xsl:when>
                    <xsl:when test="exslt:node-set($result) = exslt:node-set($expected)">PASS0</xsl:when>
                    <xsl:when test="not(string-length($expected))"><!-- UNKNOWN --></xsl:when>
                    <xsl:otherwise>FAIL</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <!-- output -->
            <hr size="1" />
            <p>
                <b>TEST <xsl:value-of select="position()" /> : <xsl:value-of select="$pass" /></b>
                <br />wp-xml-select :
                <br /> - xml = <xsl:value-of select="$xml" />
                <br /> - select = <xsl:value-of select="$select" />
                <br /> - cache = <xsl:value-of select="$cache" />
                <br /> - format = <xsl:value-of select="$format" />
                <br /> - root = <xsl:value-of select="$root" />
                <!-- <br /> - strip-declaration = <xsl:value-of select="$strip-declaration" /> -->
                <br /> - strip-namespaces = <xsl:value-of select="$strip-namespaces" />
            </p>

            <p>result : <br />
                <!-- <xsl:value-of select="$result" /> -->
                <!-- <xsl:copy-of select="$result" /> -->
                <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$result" /></xsl:call-template>
            </p>

            <xsl:if test="$pass = 'FAIL'">
                <p>expected : <br />
                    <!-- <xsl:copy-of select="$expected" /> -->
                    <xsl:call-template name="util-print-nodes"><xsl:with-param name="nodes" select="$expected" /></xsl:call-template>
                </p>
            </xsl:if>
        </xsl:for-each>
        <hr size="1" />


    </xsl:template>

</xsl:stylesheet>
<!-- end wp-xml-select.xsl -->
<!--
[xsl_transform xsl_file="qa/wp-xml-select.xsl"]
<TESTS>
  <TEST>
    <expected><RESULT><NODATA/></RESULT></expected>
  </TEST>
  <TEST>
    <xml>sample-xml</xml>
    <select>//comment</select>
    <cache>10</cache>
    <format>xml</format>
    <root>MYROOT</root>
    <strip-namespaces>no</strip-namespaces>
    <expected><RESULT> <MYROOT xml="sample-xml" id="821" select="//comment"> <comment>This is a test WordPress XML Document item</comment> </MYROOT> </RESULT></expected>
  </TEST>
  <TEST>
    <xml>sample-xml</xml>
    <select>//item</select>
    <format>json</format>
    <root>JSON</root>
    <strip-namespaces>no</strip-namespaces>
    <expected><RESULT>{"JSON":[{"attributes":{"xml":"sample-xml","id":"821","select":"\/\/item"},"item":[{"attributes":{"value":"1"},"cdata":"One"},{"attributes":{"value":"2"},"cdata":"Two"},{"attributes":{"value":"3"},"cdata":"Three"},{"attributes":{"value":"4"},"cdata":"Four"}]}]}</RESULT></expected>
  </TEST>
</TESTS>
[/xsl_transform]



  -->
