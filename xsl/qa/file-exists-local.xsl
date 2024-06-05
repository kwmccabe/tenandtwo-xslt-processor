<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    >

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="../file.xsl" />

    <xsl:template match="/">

        <xsl:for-each select="//TEST">
            <!-- values from xml -->
            <xsl:variable name="path"><xsl:value-of select="path" /></xsl:variable>
            <xsl:variable name="expected"><xsl:value-of select="expected" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="file-exists-local">
                    <xsl:with-param name="path" select="$path" />
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
                <br />file-exists-local :
                <br /> - path = <xsl:copy-of select="$path" />
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
<!-- end file-exists-local.xsl -->
<!--
[xslt_transform_xml xsl="qa/file-exists-local.xsl"]
<TESTS>
  <TEST>
    <path>__WP_HOME_DIR__/wp-content/plugins/tenandtwo-xslt-processor/xsl/sample.xml</path>
    <expected>/srv/plugins.tenandtwo.com/htdocs/wp-content/plugins/tenandtwo-xslt-processor/xsl/sample.xml</expected>
  </TEST>
  <TEST>
    <path>__WP_CONTENT_DIR__/plugins/tenandtwo-xslt-processor/xsl/sample.xml</path>
    <expected>/srv/plugins.tenandtwo.com/htdocs/wp-content/plugins/tenandtwo-xslt-processor/xsl/sample.xml</expected>
  </TEST>
  <TEST>
    <path>__XSLT_PLUGIN_DIR__/xsl/default.xml</path>
    <expected>/srv/plugins.tenandtwo.com/htdocs/wp-content/plugins/tenandtwo-xslt-processor/xsl/default.xml</expected>
  </TEST>
  <TEST>
    <path>/not-a-file.txt</path>
    <expected></expected>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
