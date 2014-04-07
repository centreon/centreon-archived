<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match='/'>
<xsl:for-each select='//root'>    
    <xsl:element name='table'>
        <xsl:element name='tr'>
            <xsl:element name='td'>
                <xsl:attribute name='class'>FormRowValue</xsl:attribute>        
                <xsl:element name='a'>            
                    <xsl:attribute name='id'>addBtn</xsl:attribute>
                    <xsl:attribute name='style'>cursor: pointer;</xsl:attribute>
                    <xsl:attribute name='name'>additionalRow</xsl:attribute>
                    <xsl:attribute name='onClick'>
                        addNewRow();
                    </xsl:attribute>
                    <xsl:value-of select='//main/addLabel'/>
                    <xsl:text>  </xsl:text>
                    <xsl:element name='img'>
                        <xsl:attribute name='src'>
                            <xsl:value-of select='//main/addImg'/>
                        </xsl:attribute>
                    </xsl:element>
                </xsl:element>
                <xsl:element name='table'>
                    <xsl:attribute name='width'>100%</xsl:attribute>
                    <xsl:element name='tbody'>
                        <xsl:for-each select='//trap'>
                            <xsl:element name='tr'>
                                <xsl:attribute name='id'>regularTr_<xsl:value-of select='order'/></xsl:attribute>
                                <xsl:attribute name='class'>list_one</xsl:attribute>
                                <xsl:element name='td'>
                                	<xsl:value-of select='//main/regexpVar'/>
                                    <xsl:element name='input'>
                                        <xsl:attribute name='type'>text</xsl:attribute>
                                        <xsl:attribute name='value'><xsl:value-of select='var'/></xsl:attribute>
                                        <xsl:attribute name='name'>regularVar_<xsl:value-of select='order'/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:text>  </xsl:text>
                                    <xsl:value-of select='//main/regexpLabel'/>
                                    <xsl:element name='input'>
                                        <xsl:attribute name='type'>text</xsl:attribute>
                                        <xsl:attribute name='value'><xsl:value-of select='regexp'/></xsl:attribute>
                                        <xsl:attribute name='name'>regularRegexp_<xsl:value-of select='order'/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:text>  </xsl:text>
                                    <xsl:value-of select='//main/statusLabel'/>                            
                                        <xsl:element name='select'>
                                            <xsl:attribute name='name'>regularStatus_<xsl:value-of select='order'/></xsl:attribute>
                                            <xsl:attribute name='style'>width:100px;</xsl:attribute>
                                            <xsl:element name='option'>
                                                <xsl:attribute name='value'>0</xsl:attribute>                                
                                                <xsl:if test="status = '0'">                                
                                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                                </xsl:if>
                                                <xsl:value-of select='//main/okLabel'/>
                                            </xsl:element>
                                            <xsl:element name='option'>
                                                <xsl:attribute name='value'>1</xsl:attribute>                                
                                                <xsl:if test="status = '1'">
                                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                                </xsl:if>
                                                <xsl:value-of select='//main/warningLabel'/>
                                            </xsl:element>
                                            <xsl:element name='option'>
                                                <xsl:attribute name='value'>2</xsl:attribute>                                
                                                <xsl:if test="status = '2'">
                                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                                </xsl:if>
                                                <xsl:value-of select='//main/criticalLabel'/>
                                            </xsl:element>
                                            <xsl:element name='option'>
                                                <xsl:attribute name='value'>3</xsl:attribute>                                
                                                <xsl:if test="status = '3'">
                                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                                </xsl:if>
                                                <xsl:value-of select='//main/unknownLabel'/>
                                            </xsl:element>
                                        </xsl:element>                            
                                    <xsl:text>   </xsl:text>
                                    <xsl:value-of select='//main/orderLabel'/>
                                    <xsl:element name='input'>
                                        <xsl:attribute name='name'>regularOrder_<xsl:value-of select='order'/></xsl:attribute>
                                        <xsl:attribute name='type'>text</xsl:attribute>
                                        <xsl:attribute name='size'>2</xsl:attribute>
                                        <xsl:attribute name='value'><xsl:value-of select='order'/></xsl:attribute>
                                    </xsl:element>
                                    <xsl:text>  </xsl:text>
                                    <xsl:element name='img'>
                                        <xsl:attribute name='src'>./img/icones/16x16/delete.gif</xsl:attribute>
                                        <xsl:attribute name='style'>cursor: pointer;</xsl:attribute>
                                        <xsl:attribute name='onClick'>
                                            if (confirm("<xsl:value-of select='//main/confirmDeletion'/>")) {
                                                removeTr("regularTr_<xsl:value-of select='order'/>");
                                            }
                                        </xsl:attribute>
                                    </xsl:element>                        
                                </xsl:element>
                            </xsl:element>
                        </xsl:for-each>
                    </xsl:element>
                </xsl:element>
                <xsl:element name='div'>
                        <xsl:attribute name='id'>additionalRow_1</xsl:attribute>
                </xsl:element>
            </xsl:element>
        </xsl:element>
    </xsl:element>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>