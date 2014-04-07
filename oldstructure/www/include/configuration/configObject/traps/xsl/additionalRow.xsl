<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match='/'>
<xsl:for-each select='//root'>       
    <xsl:element name='table'>        
        <xsl:attribute name='width'>100%</xsl:attribute>
        <xsl:element name='tbody'>
            <xsl:element name='tr'>
                <xsl:attribute name='id'>additionalTr_<xsl:value-of select='//main/currentId'/></xsl:attribute>
                <xsl:attribute name='class'>list_one</xsl:attribute>
                <xsl:element name='td'>
                             <xsl:value-of select='//main/regexpVar'/>
                            <xsl:element name='input'>                            
                                <xsl:attribute name='name'>additionalVar_<xsl:value-of select='//main/currentId'/></xsl:attribute>
                                <xsl:attribute name='type'>text</xsl:attribute>
                                <xsl:attribute name='value'>@OUTPUT@</xsl:attribute>
                            </xsl:element>
                            <xsl:text>  </xsl:text>
                            <xsl:value-of select='//main/regexpLabel'/>
                            <xsl:element name='input'>                            
                                <xsl:attribute name='name'>additionalRegexp_<xsl:value-of select='//main/currentId'/></xsl:attribute>
                                <xsl:attribute name='type'>text</xsl:attribute>
                                <xsl:attribute name='value'>//</xsl:attribute>
                            </xsl:element>
                            <xsl:text>  </xsl:text>
                            <xsl:value-of select='//main/statusLabel'/>
                            <xsl:element name='select'>
                                <xsl:attribute name='name'>additionalStatus_<xsl:value-of select='//main/currentId'/></xsl:attribute>
								<xsl:attribute name='style'>width:100px;</xsl:attribute>
                                <xsl:element name='option'>
                                    <xsl:attribute name='value'>0</xsl:attribute>
                                    <xsl:value-of select='//main/okLabel'/>
                                </xsl:element>
                                <xsl:element name='option'>
                                    <xsl:attribute name='value'>1</xsl:attribute>                                
                                    <xsl:value-of select='//main/warningLabel'/>
                                </xsl:element>
                                <xsl:element name='option'>
                                    <xsl:attribute name='value'>2</xsl:attribute>
                                    <xsl:value-of select='//main/criticalLabel'/>
                                </xsl:element>
                                <xsl:element name='option'>
                                    <xsl:attribute name='value'>3</xsl:attribute>
                                    <xsl:value-of select='//main/unknownLabel'/>
                                </xsl:element>
                            </xsl:element>
                            <xsl:text>  </xsl:text>
                            <xsl:value-of select='//main/orderLabel'/>
                            <xsl:element name='input'>
                                <xsl:attribute name='name'>additionalOrder_<xsl:value-of select='//main/currentId'/></xsl:attribute>
                                <xsl:attribute name='type'>text</xsl:attribute>
                                <xsl:attribute name='size'>2</xsl:attribute>
                                <xsl:attribute name='value'><xsl:value-of select='//main/orderValue'/></xsl:attribute>
                            </xsl:element>
                            <xsl:text>  </xsl:text>
                            <xsl:element name='img'>
                                <xsl:attribute name='src'>./img/icones/16x16/delete.gif</xsl:attribute>
                                <xsl:attribute name='style'>cursor: pointer;</xsl:attribute>
                                <xsl:attribute name='onClick'>
                                    if (confirm("<xsl:value-of select='//main/confirmDeletion'/>")) {
                                        removeTr("additionalTr_<xsl:value-of select='//main/currentId'/>");
                                    }
                                </xsl:attribute>
                            </xsl:element>
                </xsl:element>  
            </xsl:element>
        </xsl:element>
    </xsl:element>
    <xsl:element name='div'>
        <xsl:attribute name='id'><xsl:value-of select='//main/nextRowId'/></xsl:attribute>        
    </xsl:element>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>