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
            <xsl:variable name="csv"><xsl:value-of select="csv" /></xsl:variable>
            <xsl:variable name="separator"><xsl:value-of select="separator" />
                <xsl:if test="not(string-length(separator))">,</xsl:if>
            </xsl:variable>
            <xsl:variable name="enclosure"><xsl:value-of select="enclosure" />
                <xsl:if test="not(string-length(enclosure))">\"</xsl:if>
            </xsl:variable>
            <xsl:variable name="escape"><xsl:value-of select="escape" />
                <xsl:if test="not(string-length(escape))">\\</xsl:if>
            </xsl:variable>
            <xsl:variable name="key_row"><xsl:value-of select="key_row" /></xsl:variable>
            <xsl:variable name="col"><xsl:value-of select="col" /></xsl:variable>
            <xsl:variable name="key_col"><xsl:value-of select="key_col" /></xsl:variable>
            <xsl:variable name="key"><xsl:value-of select="key" /></xsl:variable>
            <xsl:variable name="row"><xsl:value-of select="row" /></xsl:variable>
            <xsl:variable name="class"><xsl:value-of select="class" />
                <xsl:if test="not(string-length(class))">table</xsl:if>
            </xsl:variable>
            <!-- <xsl:variable name="htmlentities"><xsl:value-of select="htmlentities" /></xsl:variable> -->
            <xsl:variable name="expected"><xsl:copy-of select="expected/*" /></xsl:variable>

            <!-- run test -->
            <xsl:variable name="result">
                <xsl:call-template name="wp-select-csv">
                    <xsl:with-param name="csv" select="$csv" />
                    <xsl:with-param name="separator" select="$separator" />
                    <xsl:with-param name="enclosure" select="$enclosure" />
                    <xsl:with-param name="escape" select="$escape" />
                    <xsl:with-param name="key_row" select="$key_row" />
                    <xsl:with-param name="col" select="$col" />
                    <xsl:with-param name="key_col" select="$key_col" />
                    <xsl:with-param name="key" select="$key" />
                    <xsl:with-param name="row" select="$row" />
                    <xsl:with-param name="class" select="$class" />
                    <!-- <xsl:with-param name="htmlentities" select="$htmlentities" /> -->
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
                <br />wp-select-csv :
                <xsl:if test="string-length($csv)"><br /> - csv = <xsl:copy-of select="$csv" /></xsl:if>
                <xsl:if test="string-length($separator)"><br /> - separator = <xsl:copy-of select="$separator" /></xsl:if>
                <xsl:if test="string-length($enclosure)"><br /> - enclosure = <xsl:copy-of select="$enclosure" /></xsl:if>
                <xsl:if test="string-length($escape)"><br /> - escape = <xsl:copy-of select="$escape" /></xsl:if>
                <xsl:if test="string-length($key_row)"><br /> - key_row = <xsl:copy-of select="$key_row" /></xsl:if>
                <xsl:if test="string-length($col)"><br /> - col = <xsl:copy-of select="$col" /></xsl:if>
                <xsl:if test="string-length($key_col)"><br /> - key_col = <xsl:copy-of select="$key_col" /></xsl:if>
                <xsl:if test="string-length($key)"><br /> - key = <xsl:copy-of select="$key" /></xsl:if>
                <xsl:if test="string-length($row)"><br /> - row = <xsl:copy-of select="$row" /></xsl:if>
                <xsl:if test="string-length($class)"><br /> - class = <xsl:copy-of select="$class" /></xsl:if>
                <!-- <xsl:if test="string-length($htmlentities)"><br /> - htmlentities = <xsl:copy-of select="$htmlentities" /></xsl:if> -->
            </p>

            <p>result : <br />
                <xsl:copy-of select="$result" />
                <!-- <pre><xsl:value-of select="$p_result" /></pre> -->
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
<!-- end wp-select-csv.xsl -->
<!--
[xslt_transform_xml xsl_file="qa/wp-select-csv.xsl"]
<TESTS>
  <TEST>
    <csv>case-study-gsheets/Sheet1.csv</csv>
    <class>table table-striped</class>
  </TEST>
  <TEST>
    <csv>case-study-gsheets/Sheet1.tsv</csv>
    <separator>\t</separator>
    <enclosure>"</enclosure>
    <escape>\</escape>
    <class>table table-striped</class>
  </TEST>
</TESTS>
[/xslt_transform_xml]
-->
