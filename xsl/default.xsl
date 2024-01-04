<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" />
    <xsl:include href="./util.xsl" />

    <xsl:template match="/">

        <xsl:call-template name="util-print-nodes" />

    </xsl:template>

</xsl:stylesheet>
<!-- end default.xsl -->
