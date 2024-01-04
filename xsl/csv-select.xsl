<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exslt="http://exslt.org/common"
	exclude-result-prefixes="exslt"
	>
    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="./string.xsl" />

    <!-- params
    key_row : int, row number for column labels
    col     : mixed, num|ltr|label for return
    key_col : mixed, num|ltr|label for key values
    key     : string for key_col match
    row     : row number(s) for direct select
    -->
    <xsl:param name="key_row" />
    <xsl:param name="col" />
    <xsl:param name="key_col" />
    <xsl:param name="key" />
    <xsl:param name="row" />
    <xsl:param name="class" />

    <xsl:variable name="alphabet">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>

    <xsl:key name="cell_rows" match="//td" use="@row" />
    <xsl:key name="cell_cols" match="//td" use="@col" />

    <!-- main -->
    <xsl:template match="/">

        <!-- labels (nodeset) from key_row (int) -->
        <xsl:variable name="key_row_values">
            <xsl:if test="string(number($key_row)) != 'NaN' and $key_row > 0">
                <xsl:copy-of select="key('cell_rows', $key_row)" />
            </xsl:if>
        </xsl:variable>

        <!-- col_values (nodeset) from col (mixed) -->
        <xsl:variable name="col_values">
            <xsl:call-template name="string-to-nodeset">
                <xsl:with-param name="value" select="$col" />
                <xsl:with-param name="delimiter" select="','" />
                <xsl:with-param name="nodename" select="'NODE'" />
            </xsl:call-template>
        </xsl:variable>

        <!-- row_values (nodeset) from row (mixed) -->
        <xsl:variable name="row_values">
            <xsl:call-template name="string-to-nodeset">
                <xsl:with-param name="value" select="$row" />
                <xsl:with-param name="delimiter" select="','" />
                <xsl:with-param name="nodename" select="'NODE'" />
            </xsl:call-template>
        </xsl:variable>

        <!-- key_values (nodeset) from key (mixed) -->
        <xsl:variable name="key_values">
            <xsl:call-template name="string-to-nodeset">
                <xsl:with-param name="value" select="$key" />
                <xsl:with-param name="delimiter" select="','" />
                <xsl:with-param name="nodename" select="'NODE'" />
            </xsl:call-template>
        </xsl:variable>

        <!-- key_col_num (int) from key_col (mixed) -->
        <xsl:variable name="key_col_num">
            <xsl:choose>
                <xsl:when test="string-length($key_col) &gt; 0 and exslt:node-set($key_row_values)/*[. = $key_col]/@col">
                    <xsl:value-of select="exslt:node-set($key_row_values)/*[. = $key_col]/@col" />
                </xsl:when>
                <xsl:when test="string-length($key_col) &lt; 3 and contains($alphabet,substring($key_col,1,1))">
                    <xsl:call-template name="alpha_to_num">
                        <xsl:with-param name="alpha" select="$key_col" />
                    </xsl:call-template>
                </xsl:when>
                <xsl:when test="string(number($key_col)) != 'NaN' and $key_col > 0">
                    <xsl:value-of select="$key_col" />
                </xsl:when>
                <xsl:when test="string-length($key) > 0">
                    <xsl:text>1</xsl:text>
                </xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- key_col_values (nodeset) from key_col_num (int) -->
        <xsl:variable name="key_col_values">
            <xsl:if test="$key_col_num > 0">
                <xsl:copy-of select="key('cell_cols', $key_col_num)" />
            </xsl:if>
        </xsl:variable>


        <!-- row_num (str) from key_values (nodeset) or row_values (nodeset) -->
        <xsl:variable name="row_num">
            <xsl:if test="$key_col_num > 0 and count(exslt:node-set($key_values)//NODE[string-length(.) &gt; 0]) &gt; 0">
                <xsl:text>|keyed-search</xsl:text>
                <xsl:for-each select="exslt:node-set($key_values)//NODE[string-length(.) &gt; 0]">
                    <xsl:variable name="key_val"><xsl:value-of select="text()" /></xsl:variable>
                    <xsl:choose>
                        <xsl:when test="$key_col_num > 0 and string-length($key_val) > 0">
                            <xsl:for-each select="exslt:node-set($key_col_values)/*[. = $key_val]">
                                <xsl:value-of select="concat('|',@row)" />
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="string(number($key_val)) != 'NaN' and $key_val > 0">
                            <xsl:value-of select="concat('|',$key_val)" />
                        </xsl:when>
                    </xsl:choose>
                </xsl:for-each>
            </xsl:if>
            <xsl:for-each select="exslt:node-set($row_values)//NODE[string-length(.) &gt; 0]">
                <xsl:variable name="row_val"><xsl:value-of select="text()" /></xsl:variable>
                <xsl:if test="string(number($row_val)) != 'NaN' and $row_val > 0">
                    <xsl:value-of select="concat('|',$row_val)" />
                </xsl:if>
            </xsl:for-each>
        </xsl:variable>

        <!-- col_num (str) from col_values (nodeset) -->
        <xsl:variable name="col_num">
            <xsl:for-each select="exslt:node-set($col_values)//NODE[string-length(.) &gt; 0]">
                <xsl:variable name="col_val"><xsl:value-of select="text()" /></xsl:variable>
                <xsl:choose>
                    <xsl:when test="string-length($col_val) &gt; 0 and exslt:node-set($key_row_values)/*[. = $col_val]/@col">
                        <xsl:text>|</xsl:text>
                        <xsl:value-of select="exslt:node-set($key_row_values)/*[. = $col_val]/@col" />
                    </xsl:when>
                    <xsl:when test="string-length($col_val) &lt; 3 and contains($alphabet,substring($col_val,1,1))">
                        <xsl:text>|</xsl:text>
                        <xsl:call-template name="alpha_to_num">
                            <xsl:with-param name="alpha" select="$col_val" />
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:when test="string(number($col_val)) != 'NaN' and $col_val > 0">
                        <xsl:value-of select="concat('|',$col_val)" />
                    </xsl:when>
                    <!-- <xsl:otherwise>|0</xsl:otherwise> -->
                </xsl:choose>
            </xsl:for-each>
        </xsl:variable>


        <!-- final column numbers (nodeset) from col_num (str) -->
        <xsl:variable name="columns">
            <xsl:call-template name="string-to-nodeset">
                <xsl:with-param name="value" select="$col_num" />
                <xsl:with-param name="delimiter" select="'|'" />
                <xsl:with-param name="nodename" select="'NODE'" />
            </xsl:call-template>
        </xsl:variable>

        <!-- final row numbers (nodeset) from row_num (str) -->
        <xsl:variable name="rows">
            <xsl:call-template name="string-to-nodeset">
                <xsl:with-param name="value" select="$row_num" />
                <xsl:with-param name="delimiter" select="'|'" />
                <xsl:with-param name="nodename" select="'NODE'" />
            </xsl:call-template>
        </xsl:variable>


        <!-- output -->
        <xsl:element name="table">
            <xsl:if test="string-length($class) > 0">
                <xsl:attribute name="class"><xsl:value-of select="$class" /></xsl:attribute>
            </xsl:if>
            <xsl:for-each select="//table/@*">
                <xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
            </xsl:for-each>

            <xsl:apply-templates select="//tr">
                <xsl:with-param name="rows" select="$rows" />
                <xsl:with-param name="columns" select="$columns" />
            </xsl:apply-templates>
        </xsl:element>

<!--
- key_row: <xsl:copy-of select="$key_row" />
- col: <xsl:copy-of select="$col" />
- key_col: <xsl:copy-of select="$key_col" />
- key: <xsl:copy-of select="$key" />
- row: <xsl:copy-of select="$row" />
- class: <xsl:copy-of select="$class" />

- key_row_values: <xsl:copy-of select="$key_row_values" />
- col_values: <xsl:copy-of select="$col_values" />
- row_values: <xsl:copy-of select="$row_values" />
- key_values: <xsl:copy-of select="$key_values" />

- key_col_num: <xsl:copy-of select="$key_col_num" />
- key_col_values: <xsl:copy-of select="$key_col_values" />

- row_num: <xsl:copy-of select="$row_num" />
- col_num: <xsl:copy-of select="$col_num" />

- rows: <xsl:copy-of select="$rows" />
- columns: <xsl:copy-of select="$columns" />
 -->

    </xsl:template>

    <!--
    -->
    <xsl:template match="tr">
        <xsl:param name="rows" />
        <xsl:param name="columns" />

        <xsl:variable name="keyed"><xsl:value-of select="count(exslt:node-set($rows)//NODE[. = 'keyed-search'])" /></xsl:variable>
        <xsl:variable name="row_cnt"><xsl:value-of select="count(exslt:node-set($rows)//NODE[. &gt; 0])" /></xsl:variable>
        <xsl:variable name="row_num"><xsl:value-of select="@row" /></xsl:variable>

        <xsl:if test="($keyed = 0 and $row_cnt = 0) or exslt:node-set($rows)//NODE[. = $row_num]">
            <xsl:element name="tr">
                <xsl:for-each select="@*">
                    <xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
                </xsl:for-each>

                <xsl:apply-templates select="td">
                    <xsl:with-param name="columns" select="$columns" />
                </xsl:apply-templates>
            </xsl:element>
        </xsl:if>

    </xsl:template>

    <!--
    -->
    <xsl:template match="td">
        <xsl:param name="columns" />

        <xsl:variable name="col_cnt"><xsl:value-of select="count(exslt:node-set($columns)//NODE[. &gt; 0])" /></xsl:variable>
        <xsl:variable name="col_num"><xsl:value-of select="@col" /></xsl:variable>

        <xsl:variable name="key_row_values">
            <xsl:if test="string(number($key_row)) != 'NaN' and $key_row > 0">
                <xsl:copy-of select="key('cell_rows', $key_row)" />
            </xsl:if>
        </xsl:variable>

        <xsl:if test="$col_cnt = 0 or exslt:node-set($columns)//NODE[. = $col_num]">
            <xsl:element name="td">
                <xsl:for-each select="@*">
                    <xsl:attribute name="{name()}"><xsl:value-of select="." /></xsl:attribute>
                </xsl:for-each>

                <xsl:if test="exslt:node-set($key_row_values)/*[@col = $col_num]/text()">
                    <xsl:attribute name="label">
                        <xsl:value-of select="exslt:node-set($key_row_values)/*[@col = $col_num]/text()" />
                    </xsl:attribute>
                </xsl:if>

                <xsl:copy-of disable-output-escaping="yes" select="text()" />
            </xsl:element>
        </xsl:if>
    </xsl:template>


    <!-- convert 'A'=1, 'B'=2 ... 'ZZ'=702
    -->
    <xsl:template name="alpha_to_num">
        <xsl:param name="alpha" />
        <xsl:variable name="pos_one">
            <xsl:value-of select="string-length(substring-before($alphabet,substring($alpha,1,1))) + 1" />
        </xsl:variable>
        <xsl:choose>
            <xsl:when test="string-length($alpha) > 1">
                <xsl:variable name="pos_two">
                    <xsl:value-of select="string-length(substring-before($alphabet,substring($alpha,2,1))) + 1" />
                </xsl:variable>
                <xsl:value-of select="($pos_one * 26) + $pos_two" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$pos_one" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>


</xsl:stylesheet>
<!-- end xsl/csv-select.xsl -->
