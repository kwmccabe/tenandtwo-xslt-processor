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
            <xsl:variable name="select"><xsl:value-of select="select" />
                <xsl:if test="not(string-length(select))">/</xsl:if>
            </xsl:variable>
            <xsl:variable name="cache"><xsl:value-of select="cache" /></xsl:variable>
            <xsl:variable name="format"><xsl:value-of select="format" /></xsl:variable>
            <xsl:variable name="root"><xsl:value-of select="root" /></xsl:variable>
            <!-- <xsl:variable name="strip-declaration"><xsl:value-of select="strip-declaration" /></xsl:variable> -->
            <xsl:variable name="strip-namespaces"><xsl:value-of select="strip-namespaces" /></xsl:variable>
            <xsl:variable name="expected"><xsl:copy-of select="expected/*" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="wp-select-xml">
                    <xsl:with-param name="xml"    select="$xml" />
                    <xsl:with-param name="select" select="$select" />
                    <xsl:with-param name="cache" select="$cache" />
                    <xsl:with-param name="format" select="$format" />
                    <xsl:with-param name="root"   select="$root" />
                    <!-- <xsl:with-param name="strip-declaration" select="$strip-declaration" /> -->
                    <xsl:with-param name="strip-namespaces" select="$strip-namespaces" />
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
                <br />wp-select-xml :
                <xsl:if test="string-length($xml)"><br /> - xml = <xsl:copy-of select="$xml" /></xsl:if>
                <xsl:if test="string-length($select)"><br /> - select = <xsl:copy-of select="$select" /></xsl:if>
                <xsl:if test="string-length($cache)"><br /> - cache = <xsl:copy-of select="$cache" /></xsl:if>
                <xsl:if test="string-length($format)"><br /> - format = <xsl:copy-of select="$format" /></xsl:if>
                <xsl:if test="string-length($root)"><br /> - root = <xsl:copy-of select="$root" /></xsl:if>
                <!-- <xsl:if test="string-length($strip-declaration)"><br /> - strip-declaration = <xsl:copy-of select="$strip-declaration" /></xsl:if> -->
                <xsl:if test="string-length($strip-namespaces)"><br /> - strip-namespaces = <xsl:copy-of select="$strip-namespaces" /></xsl:if>
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
<!-- end wp-select-xml.xsl -->
<!--
[xslt_transform_xml xsl="qa/wp-select-xml.xsl"]
<TESTS>
  <TEST>
    <expected><RESULT><NODATA/></RESULT></expected>
  </TEST>
  <TEST>
    <xml>sample-xml</xml>
    <select>//comment</select>
    <cache>10</cache>
    <format>xml</format>
    <root>DATA</root>
    <strip-namespaces>no</strip-namespaces>
    <expected><RESULT template="wp-select-xml"><DATA xml="sample-xml" id="876" select="//comment"><comment>This is a test WordPress XML Document item</comment></DATA></RESULT></expected>
  </TEST>
  <TEST>
    <xml>sample-xml</xml>
    <select>//item</select>
    <format>json</format>
    <root>JSON</root>
    <expected><RESULT template="wp-select-xml">{"JSON":[{"attributes":{"xml":"sample-xml","id":"876","select":"\/\/item"},"item":[{"attributes":{"value":"1"},"cdata":"One"},{"attributes":{"value":"2"},"cdata":"Two"},{"attributes":{"value":"3"},"cdata":"Three"},{"attributes":{"value":"4"},"cdata":"Four"}]}]}</RESULT></expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
  -->
