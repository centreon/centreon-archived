<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
<xsl:element name="div">
	<xsl:attribute name="style">		
		background-color:<xsl:value-of select="//Menu1ID"/>;				
	</xsl:attribute>	
	<xsl:element name="div">
		<xsl:attribute name="id">		
			<xsl:value-of select="//Menu1Color"/>				
		</xsl:attribute>
		<ul>
		<xsl:for-each select="//Menu1">
			<li>
				<xsl:element name="div">
					<xsl:attribute name="style">
						<xsl:if test="Menu1ClassImg != ''">
		                    background-color: <xsl:value-of select="Menu1ClassImg"/>;
		                </xsl:if>
					</xsl:attribute>
					<xsl:element name="a">
						<xsl:choose>
		                    <xsl:when test="Menu1UrlPopup = '1'">
		                        <xsl:attribute name="target">_blank</xsl:attribute>
		                        <xsl:attribute name="href"><xsl:value-of select="Menu1UrlPopupOpen"/></xsl:attribute>
		                        <xsl:attribute name="style"><xsl:value-of select="Menu1UrlPopupOpen"/></xsl:attribute>
		                    </xsl:when>
		                    <xsl:otherwise>
		                        <xsl:attribute name="onclick">
		                            loadAjax(<xsl:value-of select="Menu1Page"/>); return false;
		                        </xsl:attribute>
		                        <xsl:attribute name="href">
		                            #
		                        </xsl:attribute>
		                    </xsl:otherwise>
		                </xsl:choose>
						<xsl:value-of select="Menu1Name"/>			
					</xsl:element>		
				</xsl:element>
			</li>
		</xsl:for-each>
		</ul>
    </xsl:element>
</xsl:element>
<xsl:element name="div">
	<xsl:attribute name="id">		
		<xsl:value-of select="//Menu2Color"/>				
	</xsl:attribute>	
	<xsl:element name="div">
		<xsl:attribute name="style">		
			background-color:<xsl:value-of select="//Menu2ID"/>;				
		</xsl:attribute>
		<xsl:for-each select="//Menu2">
			<xsl:element name="span2">
				<xsl:attribute name="class">span2</xsl:attribute>                                
				<xsl:element name="a">
					<xsl:choose>
                        <xsl:when test="Menu2UrlPopup = '1'">
                            <xsl:attribute name="target">_blank</xsl:attribute>
                            <xsl:attribute name="href"><xsl:value-of select="Menu2UrlPopupOpen"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="href"><xsl:value-of select="Menu2Url"/></xsl:attribute>
                            <xsl:attribute name="style">white-space:nowrap;</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:value-of select="Menu2Name"/>
				</xsl:element>
			</xsl:element>
		</xsl:for-each>
	</xsl:element>
</xsl:element>
</xsl:template>
</xsl:stylesheet>