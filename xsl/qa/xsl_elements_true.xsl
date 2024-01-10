<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="UTF-8" indent="yes" />

    <!--
    Top-Level Elements:
        xsl:stylesheet == xsl:transform
        xsl:attribute-set
        xsl:namespace-alias
        xsl:output
        xsl:preserve-space
        xsl:strip-space
        xsl:import
        xsl:include
        xsl:script
        xsl:key
        xsl:template
    -->
	<xsl:template match="/">

        <ul>
        <xsl:if test="(element-available('xsl:apply-imports'))"><li>xsl:apply-imports</li></xsl:if>
        <xsl:if test="(element-available('xsl:apply-templates'))"><li>xsl:apply-templates</li></xsl:if>
        <xsl:if test="(element-available('xsl:attribute'))"><li>xsl:attribute</li></xsl:if>
        <xsl:if test="(element-available('xsl:call-template'))"><li>xsl:call-template</li></xsl:if>
        <xsl:if test="(element-available('xsl:choose'))"><li>xsl:choose</li></xsl:if>
        <xsl:if test="(element-available('xsl:comment'))"><li>xsl:comment</li></xsl:if>
        <xsl:if test="(element-available('xsl:copy'))"><li>xsl:copy</li></xsl:if>
        <xsl:if test="(element-available('xsl:copy-of'))"><li>xsl:copy-of</li></xsl:if>
        <xsl:if test="(element-available('xsl:decimal-format'))"><li>xsl:decimal-format</li></xsl:if>
        <xsl:if test="(element-available('xsl:document'))"><li>xsl:document</li></xsl:if>
        <xsl:if test="(element-available('xsl:element'))"><li>xsl:element</li></xsl:if>
        <xsl:if test="(element-available('xsl:fallback'))"><li>xsl:fallback</li></xsl:if>
        <xsl:if test="(element-available('xsl:for-each'))"><li>xsl:for-each</li></xsl:if>
        <xsl:if test="(element-available('xsl:if'))"><li>xsl:if</li></xsl:if>
        <xsl:if test="(element-available('xsl:when'))"><li>xsl:when</li></xsl:if>
        <xsl:if test="(element-available('xsl:otherwise'))"><li>xsl:otherwise</li></xsl:if>
        <xsl:if test="(element-available('xsl:message'))"><li>xsl:message</li></xsl:if>
        <xsl:if test="(element-available('xsl:number'))"><li>xsl:number</li></xsl:if>
        <xsl:if test="(element-available('xsl:param'))"><li>xsl:param</li></xsl:if>
        <xsl:if test="(element-available('xsl:processing-instruction'))"><li>xsl:processing-instruction</li></xsl:if>
        <xsl:if test="(element-available('xsl:sort'))"><li>xsl:sort</li></xsl:if>
        <xsl:if test="(element-available('xsl:text'))"><li>xsl:text</li></xsl:if>
        <xsl:if test="(element-available('xsl:value-of'))"><li>xsl:value-of</li></xsl:if>
        <xsl:if test="(element-available('xsl:variable'))"><li>xsl:variable</li></xsl:if>
        <xsl:if test="(element-available('xsl:with-param'))"><li>xsl:with-param</li></xsl:if>
        </ul>

	</xsl:template>

</xsl:stylesheet>
<!-- end xsl_elements_true.xsl -->
