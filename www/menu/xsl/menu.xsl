<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
<xsl:element name="div">
	<xsl:attribute name="id">		
		<xsl:value-of select="//Menu1ID"/>				
	</xsl:attribute>	
	<xsl:element name="div">
		<xsl:attribute name="id">		
			<xsl:value-of select="//Menu1Color"/>				
		</xsl:attribute>
	<xsl:for-each select="//Menu1">	
	<ul>
		<li>
			<xsl:element name="div">
				<xsl:attribute name="id">
					<xsl:value-of select="Menu1ClassImg"/>
				</xsl:attribute>
				<xsl:element name="a">
					<xsl:attribute name="onclick">
						loadAjax(<xsl:value-of select="Menu1Page"/>);
					</xsl:attribute>
					<xsl:attribute name="href">
						#
					</xsl:attribute>				
					<xsl:value-of select="Menu1Name"/>			
				</xsl:element>		
			</xsl:element>
		</li>
	</ul>	
	</xsl:for-each>
	</xsl:element>
</xsl:element>
<xsl:element name="div">
	<xsl:attribute name="id">		
		<xsl:value-of select="//Menu2Color"/>				
	</xsl:attribute>	
	<xsl:element name="div">
		<xsl:attribute name="id">		
			<xsl:value-of select="//Menu2ID"/>				
		</xsl:attribute>
		<xsl:for-each select="//Menu2">
			<xsl:element name="span">
				<xsl:attribute name="class">
					separator_menu2		
				</xsl:attribute>
				<xsl:value-of select="Menu2Sep"/>
			</xsl:element>
			<xsl:element name="span">
				<xsl:attribute name="class">
					span2					
				</xsl:attribute>
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="Menu2Url"/>
					</xsl:attribute>
					<xsl:attribute name="style">
						white-space:nowrap;
					</xsl:attribute>
					<xsl:value-of select="Menu2Name"/>
				</xsl:element>	
			</xsl:element>
		</xsl:for-each>
	</xsl:element>
</xsl:element>
</xsl:template>
</xsl:stylesheet>