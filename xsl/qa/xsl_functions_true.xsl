<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="UTF-8" indent="yes" />

    <!--
    -->
	<xsl:template match="/">
        <ul>

<li><b>Node Set Functions</b></li>
            <xsl:if test="(function-available('choose'))"><li>choose()</li></xsl:if>
            <xsl:if test="(function-available('count'))"><li>count()</li></xsl:if>
            <xsl:if test="(function-available('current'))"><li>current()</li></xsl:if>
            <xsl:if test="(function-available('document'))"><li>document()</li></xsl:if>
            <xsl:if test="(function-available('generate-id'))"><li>generate-id()</li></xsl:if>
            <xsl:if test="(function-available('id'))"><li>id()</li></xsl:if>
            <xsl:if test="(function-available('key'))"><li>key()</li></xsl:if>
            <xsl:if test="(function-available('last'))"><li>last()</li></xsl:if>
            <xsl:if test="(function-available('local-name'))"><li>local-name()</li></xsl:if>
            <xsl:if test="(function-available('name'))"><li>name()</li></xsl:if>
            <xsl:if test="(function-available('namespace-uri'))"><li>namespace-uri()</li></xsl:if>
            <xsl:if test="(function-available('position'))"><li>position()</li></xsl:if>

<li><b>String Functions</b></li>
            <xsl:if test="(function-available('string'))"><li>string()</li></xsl:if>
            <xsl:if test="(function-available('string-length'))"><li>string-length()</li></xsl:if>
            <xsl:if test="(function-available('contains'))"><li>contains()</li></xsl:if>
            <xsl:if test="(function-available('starts-with'))"><li>starts-with()</li></xsl:if>
<xsl:if test="(function-available('ends-with'))"><li>ends-with()</li></xsl:if>
<!-- <xsl:if test="(function-available('compare'))"><li>compare()</li></xsl:if> -->
            <xsl:if test="(function-available('concat'))"><li>concat()</li></xsl:if>
            <xsl:if test="(function-available('substring'))"><li>substring()</li></xsl:if>
            <xsl:if test="(function-available('substring-after'))"><li>substring-after()</li></xsl:if>
            <xsl:if test="(function-available('substring-before'))"><li>substring-before()</li></xsl:if>
<!-- <xsl:if test="(function-available('matches'))"><li>matches()</li></xsl:if> -->
<xsl:if test="(function-available('replace'))"><li>replace()</li></xsl:if>
<xsl:if test="(function-available('tokenize'))"><li>tokenize()</li></xsl:if>
<xsl:if test="(function-available('escape-uri'))"><li>escape-uri()</li></xsl:if>
<xsl:if test="(function-available('upper-case'))"><li>upper-case()</li></xsl:if>
<xsl:if test="(function-available('lower-case'))"><li>lower-case()</li></xsl:if>
            <xsl:if test="(function-available('normalize-space'))"><li>normalize-space()</li></xsl:if>
            <xsl:if test="(function-available('translate'))"><li>translate()</li></xsl:if>
            <xsl:if test="(function-available('unparsed-entity-url'))"><li>unparsed-entity-url()</li></xsl:if>

<li><b>Boolean Functions</b></li>
            <xsl:if test="(function-available('boolean'))"><li>boolean()</li></xsl:if>
            <xsl:if test="(function-available('not'))"><li>not()</li></xsl:if>
            <xsl:if test="(function-available('true'))"><li>true()</li></xsl:if>
            <xsl:if test="(function-available('false'))"><li>false()</li></xsl:if>
            <xsl:if test="(function-available('lang'))"><li>lang()</li></xsl:if>

<li><b>Number Functions</b></li>
            <xsl:if test="(function-available('number'))"><li>number()</li></xsl:if>
            <xsl:if test="(function-available('ceiling'))"><li>ceiling()</li></xsl:if>
            <xsl:if test="(function-available('floor'))"><li>floor()</li></xsl:if>
            <xsl:if test="(function-available('round'))"><li>round()</li></xsl:if>
            <xsl:if test="(function-available('sum'))"><li>sum()</li></xsl:if>
<!-- <xsl:if test="(function-available('abs'))"><li>abs()</li></xsl:if> -->
<xsl:if test="(function-available('avg'))"><li>avg()</li></xsl:if>
<xsl:if test="(function-available('max'))"><li>max()</li></xsl:if>
<xsl:if test="(function-available('min'))"><li>min()</li></xsl:if>
            <xsl:if test="(function-available('format-number'))"><li>format-number()</li></xsl:if>

        </ul>
	</xsl:template>

</xsl:stylesheet>
<!-- end xsl_properties_true.xsl -->
