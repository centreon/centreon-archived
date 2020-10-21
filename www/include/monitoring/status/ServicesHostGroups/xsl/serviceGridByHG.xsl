<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
	<table class="ListTable">
	<tr class='ListHeader'>
		<td colspan="2" class="ListColHeaderLeft" style="white-space:nowrap;" id="alias" width="160"><xsl:value-of select="//i/host_name"/></td>
		<xsl:if test="//i/s = 1">
			<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state" width="70">Status</td>
		</xsl:if>
		<td class="ListColHeaderLeft" style="white-space:nowrap;" id="services"></td>
	</tr>
	<xsl:for-each select="//hg">		
			<tr class='list_lvl_1'>
				<xsl:if test="//i/s = 1">
				  <td colspan="4" id="hostGroup_name">
                    <xsl:value-of select="hgn"/>
                    <xsl:if test="notes_url != ''">
                      <xsl:element name="a">
                        <xsl:attribute name="href"><xsl:value-of select="notes_url"/></xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                        <xsl:element name="img">
                          <xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
							<xsl:attribute name="class">ico-14</xsl:attribute>
                        </xsl:element>
                      </xsl:element>
                    </xsl:if>
                    <xsl:if test="action_url != ''">
                      <xsl:element name="a">
                        <xsl:attribute name="href"><xsl:value-of select="action_url"/></xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                        <xsl:element name="img">
                          <xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
							<xsl:attribute name="class">ico-16</xsl:attribute>
                        </xsl:element>
                      </xsl:element>
                    </xsl:if>
                  </td>
				</xsl:if>
				<xsl:if test="//i/s = 0">
				  <td colspan="3" id="hostGroup_name">
                    <xsl:value-of select="hgn"/>
                    <xsl:if test="notes_url != ''">
                      <xsl:element name="a">
                        <xsl:attribute name="href"><xsl:value-of select="notes_url"/></xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                        <xsl:element name="img">
                          <xsl:attribute name="src">./img/icons/link.png</xsl:attribute>
							<xsl:attribute name="class">ico-14</xsl:attribute>
                        </xsl:element>
                      </xsl:element>
                    </xsl:if>
                    <xsl:if test="action_url != ''">
                      <xsl:element name="a">
                        <xsl:attribute name="href"><xsl:value-of select="action_url"/></xsl:attribute>
                        <xsl:attribute name="target">_blank</xsl:attribute>
                        <xsl:element name="img">
                          <xsl:attribute name="src">./img/icons/star-full.png</xsl:attribute>
							<xsl:attribute name="class">ico-16</xsl:attribute>
                        </xsl:element>
                      </xsl:element>
                    </xsl:if>
                  </td>
				</xsl:if>
			</tr>			
			<xsl:for-each select="l">
			<tr>
				<xsl:attribute name="id">trStatus</xsl:attribute>
	  			<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
				<td class="ListColLeft"  width="160" valign="top">
					<xsl:if test="hico != 'none'">
						<xsl:element name="img">
						  	<xsl:attribute name="src">./img/media/<xsl:value-of select="hico"/></xsl:attribute>
						  	<xsl:attribute name="width">16</xsl:attribute>
							<xsl:attribute name="height">16</xsl:attribute>
							<xsl:attribute name="style">padding-right:4px;</xsl:attribute>
						</xsl:element>
					</xsl:if>
					<xsl:element name="a">
					  	<xsl:attribute name="href"><xsl:value-of select="h_details_uri"/></xsl:attribute>
					    <xsl:attribute name="isreact">true</xsl:attribute>
						<xsl:attribute name="class">infobulle link_popup_volante</xsl:attribute>
				        <xsl:attribute name="id">host-<xsl:value-of select="hid"/></xsl:attribute>
						<xsl:value-of select="hn"/>
					</xsl:element>
				</td>
				<td class="ListColLeft" style="white-space:nowrap;width:37px;">
					<xsl:element name="a">
					  	<xsl:attribute name="href"><xsl:value-of select="s_listing_uri"/></xsl:attribute>
						<xsl:attribute name="isreact">true</xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icons/view.png</xsl:attribute>
								<xsl:attribute name="class">ico-18</xsl:attribute>
							</xsl:element>
					</xsl:element>
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?p=204&amp;mode=0&amp;svc_id=<xsl:value-of select="hnl"/></xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icons/chart.png</xsl:attribute>
								<xsl:attribute name="class">ico-18</xsl:attribute>
							</xsl:element>
					</xsl:element>
				</td>
				<xsl:if test="//i/s = 1">
				<td class="ListColCenter">
					<xsl:element name="span">
						<xsl:attribute name="class">badge <xsl:value-of select="hc"/></xsl:attribute>
						<xsl:value-of select="hs"/>
					</xsl:element>
				</td>
				</xsl:if>
				<td class="ListColLeft">
					<xsl:for-each select="svc">
						<xsl:element name="a">
							<xsl:attribute name="href"><xsl:value-of select="s_details_uri"/></xsl:attribute>
							<xsl:attribute name="isreact">true</xsl:attribute>
							<xsl:attribute name="class">infobulle link_popup_volante margin_right</xsl:attribute>
				            <xsl:attribute name="id">service-<xsl:value-of select="../hid"/>-<xsl:value-of select="svc_id"/></xsl:attribute>

							<xsl:element name="span">
								<xsl:attribute name="class">state_badge <xsl:value-of select="sc"/></xsl:attribute>
							</xsl:element>
							<xsl:value-of select="sn"/>
						</xsl:element>
					</xsl:for-each>
				</td>
			</tr>
		</xsl:for-each>	
</xsl:for-each>
</table>
<div id="div_popup" class="popup_volante"><div class="container-load"></div><div id="popup-container-display"></div></div>
</xsl:template>
</xsl:stylesheet>