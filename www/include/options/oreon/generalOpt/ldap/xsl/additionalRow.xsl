<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
<xsl:for-each select='//root'>
	<xsl:element name='table'>
		<xsl:attribute name='id'>additionalTable_<xsl:value-of select="//main/currentId"/></xsl:attribute>
		<xsl:attribute name='class'>ListTable</xsl:attribute>
        <xsl:attribute name='width'>100%</xsl:attribute>
        <xsl:element name='tbody'>
            <xsl:element name='tr'>
            	<xsl:attribute name='class'>list_lvl_1</xsl:attribute>
            	<xsl:element name='td'> 
            		Ldap server #<xsl:value-of select='//main/orderValue'/>
            	</xsl:element>
            	<xsl:element name='td'>
            		<xsl:attribute name='style'>float: right;</xsl:attribute>
            		<xsl:element name='img'>
						<xsl:attribute name='src'>./img/icones/16x16/delete.gif</xsl:attribute>
						<xsl:attribute name='style'>cursor: pointer;</xsl:attribute>
						<xsl:attribute name='onClick'>
							if (confirm("<xsl:value-of select='//main/labels/confirmDeletion'/>")) {
								removeTr("additionalTable_<xsl:value-of select='//main/currentId' />");
							}
						</xsl:attribute>
					</xsl:element>
            	</xsl:element>
            </xsl:element>
            <xsl:for-each select="//inputs/input">
            	<xsl:element name="tr">
					<xsl:element name="td"><xsl:value-of select='label'/></xsl:element>
					<xsl:element name="td">
						<xsl:choose>
							<xsl:when test="type = 'select'">
								<xsl:element name='select'>
									<xsl:if test="onChange != ''">
										<xsl:attribute name='onChange'><xsl:value-of select='onChange'/></xsl:attribute>
									</xsl:if>
									<xsl:attribute name='name'><xsl:value-of select='name'/></xsl:attribute>
									<xsl:for-each select="options/option">
										<xsl:element name="option">
											<xsl:attribute name="value"><xsl:value-of select="value"/></xsl:attribute>
											<xsl:if test="selected = 1">
												<xsl:attribute name='selected'>selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="value"/>
										</xsl:element>
									</xsl:for-each>
								</xsl:element>
							</xsl:when>
							<xsl:otherwise>
								<xsl:element name='input'>
									<xsl:if test='checked = 1'>
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
									<xsl:attribute name='name'><xsl:value-of select='name'/></xsl:attribute>
									<xsl:attribute name='type'><xsl:value-of select='type'/></xsl:attribute>
									<xsl:attribute name='size'><xsl:value-of select='size'/></xsl:attribute>
									<xsl:attribute name='value'><xsl:value-of select='value'/></xsl:attribute>
								</xsl:element>
							</xsl:otherwise>
						</xsl:choose>						
					</xsl:element>
				</xsl:element>
            </xsl:for-each>
        </xsl:element>
	</xsl:element>
    <xsl:element name='div'>
        <xsl:attribute name='id'><xsl:value-of select='//main/nextRowId'/></xsl:attribute>        
    </xsl:element>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>