<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table class="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="hostGroup_name"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="host_status"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="service_status"></td>
	</tr>
	<xsl:for-each select="//l">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>

				<td class="ListColLeft">
						<xsl:element name="a">						  	
							<xsl:attribute name="class">pop</xsl:attribute>
							<xsl:attribute name="href"><xsl:value-of select="hgurl"/></xsl:attribute>
  							<xsl:value-of select="hn"/>
						</xsl:element>
				</td>
				<td class="ListColLeft">
					<xsl:if test="hu >= 1">				
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="huc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hu"/>&#160;UP
						</span>&#160;
					</xsl:if>

					<xsl:if test="hd >= 1">				
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="hdc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hd"/>&#160;DOWN
						</span>&#160;
					</xsl:if>
					<xsl:if test="hur >= 1">					
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="hurc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hur"/>&#160;UNREACHABLE
						</span>&#160;
					</xsl:if>
				</td>
				<td class="ListColLeft">

					<xsl:if test="sk >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="skc"/>;
    						</xsl:attribute>
							<xsl:value-of select="sk"/>&#160;OK
						</span>&#160;
					</xsl:if>


					<xsl:if test="sw >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="swc"/>;
    						</xsl:attribute>
						<xsl:value-of select="sw"/>&#160;WARNING
						</span>&#160;
					</xsl:if>
					<xsl:if test="sc >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="scc"/>;
    						</xsl:attribute>
						<xsl:value-of select="sc"/>&#160;CRITICAL
						</span>&#160;
					</xsl:if>
					<xsl:if test="su >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="suc"/>;
    						</xsl:attribute>
						<xsl:value-of select="su"/>&#160;UNKNOWN
						</span>&#160;
					</xsl:if>
					<xsl:if test="sp >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="spc"/>;
    						</xsl:attribute>
						<xsl:value-of select="sp"/>&#160;PENDING
						</span>
					</xsl:if>

				</td>
	</tr>
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>
