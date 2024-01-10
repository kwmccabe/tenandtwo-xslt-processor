<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="php"
    >
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />

    <xsl:param name="param1" />
    <xsl:param name="param2">default</xsl:param>

    <xsl:template match="/">

        <p>convert_uuencode(param1): </p>
        <ul>
            <li>
<xsl:value-of select="php:functionString('convert_uuencode', string($param1))" />
            </li>
        </ul>

        <p>convert_uuencode(param2): </p>
        <ul>
            <li>
<xsl:value-of select="php:functionString('convert_uuencode', string($param2))" />
            </li>
        </ul>

        <p>xslt_function_sample(param1,param2): </p>
<xsl:copy-of select="php:function('xslt_function_sample', string($param1), string($param2))" />

    </xsl:template>

</xsl:stylesheet>
<!-- end sample-xslt-functions.xsl -->
