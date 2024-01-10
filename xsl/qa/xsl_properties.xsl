<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="UTF-8" indent="yes" />

    <!--
    -->
	<xsl:template match="/">

        <ul>
            <li>xsl:version: <xsl:value-of select="system-property('xsl:version')" /></li>
            <li>xsl:vendor: <xsl:value-of select="system-property('xsl:vendor')" /></li>
            <li>xsl:vendor-url:  <xsl:value-of select="system-property('xsl:vendor-url')" /></li>
        </ul>

	</xsl:template>

</xsl:stylesheet>
<!-- end xsl_properties.xsl -->
