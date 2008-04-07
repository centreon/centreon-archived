<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE toto[
  <!ENTITY nbsp "&#160;" >
]>
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
						  	<xsl:attribute name="href">main.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
							<xsl:attribute name="class">pop</xsl:attribute>
  							<xsl:value-of select="hn"/>
						</xsl:element>
				</td>
				<td class="ListColLeft">
					<xsl:if test="hu >= 1">				
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="huc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hu"/>&nbsp;UP
						</span>&nbsp;
					</xsl:if>

					<xsl:if test="hd >= 1">				
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="hdc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hd"/>&nbsp;DOWN
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="hur >= 1">					
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="hurc"/>;
    						</xsl:attribute>
						<xsl:value-of select="hur"/>&nbsp;UNREACHABLE
						</span>&nbsp;
					</xsl:if>
				</td>
				<td class="ListColLeft">

					<xsl:if test="sk >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="skc"/>;
    						</xsl:attribute>
							<xsl:value-of select="sk"/>&nbsp;OK
						</span>&nbsp;
					</xsl:if>


					<xsl:if test="sw >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="swc"/>;
    						</xsl:attribute>
						<xsl:value-of select="sw"/>&nbsp;WARNING
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="sc >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="scc"/>;
    						</xsl:attribute>
						<xsl:value-of select="sc"/>&nbsp;CRITICAL
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="su >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="suc"/>;
    						</xsl:attribute>
						<xsl:value-of select="su"/>&nbsp;PENDING
						</span>&nbsp;
					</xsl:if>
					<xsl:if test="sp >= 1">
						<span>
							<xsl:attribute name="style">
								background-color:<xsl:value-of select="spc"/>;
    						</xsl:attribute>
						<xsl:value-of select="sp"/>&nbsp;PENDING
						</span>
					</xsl:if>

				</td>
	</tr>
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>
