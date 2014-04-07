<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
<xsl:for-each select='//root'>    
    <xsl:element name='table'>
        <xsl:element name='tr'>
            <xsl:element name='td'>
                <xsl:attribute name='class'>FormRowValue</xsl:attribute>
                <xsl:element name='table'>
                    <xsl:attribute name='width'>100%</xsl:attribute>
                    <xsl:element name='tbody'>
                        <xsl:for-each select='//trap'>
                            <xsl:element name='tr'>
                                <xsl:attribute name='id'>regularTr_<xsl:value-of select='order'/></xsl:attribute>
                                <xsl:attribute name='class'>list_one</xsl:attribute>
                                <xsl:element name='td'>
                                	<xsl:value-of select='//main/regexpVar'/>
                                    <xsl:element name='span'>
                                    	<xsl:value-of select='var'/>
                                    </xsl:element>
                                    <xsl:text>  </xsl:text>
                                    <xsl:value-of select='//main/regexpLabel'/>
                                    <xsl:element name='span'>
                                        <xsl:value-of select='regexp'/>
                                    </xsl:element>
                                    <xsl:text>  </xsl:text>
                                    <xsl:value-of select='//main/statusLabel'/>      
                                    <xsl:element name="span">
                                    	<xsl:if test="status = '0'"><xsl:value-of select='//main/okLabel'/></xsl:if>
                                    	<xsl:if test="status = '1'"><xsl:value-of select='//main/warningLabel'/></xsl:if>
                                    	<xsl:if test="status = '2'"><xsl:value-of select='//main/criticalLabel'/></xsl:if>
                                    	<xsl:if test="status = '3'"><xsl:value-of select='//main/unknownLabel'/></xsl:if>
                                    </xsl:element>                      
                                    <xsl:text>   </xsl:text>
                                    <xsl:value-of select='//main/orderLabel'/>
                                    <xsl:element name='span'>
                                        <xsl:value-of select='order'/>
                                    </xsl:element>
                                    <xsl:text>  </xsl:text>
                                </xsl:element>
                            </xsl:element>
                        </xsl:for-each>
                    </xsl:element>
                </xsl:element>
            </xsl:element>
        </xsl:element>
    </xsl:element>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>