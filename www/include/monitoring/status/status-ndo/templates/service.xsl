<xsl:stylesheet version = '1.0'
xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table id="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="service_description"></td>
		<td colspan="2" class="ListColHeaderCenter" style="white-space:nowrap;" id="infos"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_state_change"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_check"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_attempt"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="plugin_output"></td>	
	</tr>
	<xsl:for-each select="//l">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>

  			<xsl:attribute name="class">
      		<xsl:choose>
      			<xsl:when test="position() mod 2=0">list_one</xsl:when>
      			<xsl:otherwise>list_two</xsl:otherwise>
	  		</xsl:choose>
    	</xsl:attribute>
				<td class="ListColPicker">
				<input name="" value="1" type="checkbox"></input></td>
				<td class="ListColLeft">
					<xsl:if test="host_color != normal">
						<xsl:attribute name="style">
							background-color:<xsl:value-of select="hc"/>;
    					</xsl:attribute>
					</xsl:if>
					<xsl:element name="a">
					  	<xsl:attribute name="href">oreon.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
						<xsl:attribute name="class">pop</xsl:attribute>
  						<xsl:value-of select="hn"/>
					</xsl:element>
				</td>
				<td class="ListColLeft">
					<xsl:element name="a">
					  	<xsl:attribute name="href">oreon.php?p=202&amp;o=svcd&amp;host_name=<xsl:value-of select="hn"/>&amp;service_description=<xsl:value-of select="sd"/></xsl:attribute>
  						<xsl:value-of select="sd"/>
					</xsl:element>
				</td>
				<td class="ListColRight">
					<xsl:element name="a">
					  	<xsl:attribute name="href">oreon.php?p=40207&amp;host_name=<xsl:value-of select="hn"/>&amp;service_description=<xsl:value-of select="sd"/>&amp;submitC=Grapher</xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icones/16x16/column-chart.gif</xsl:attribute>
							</xsl:element>
					</xsl:element>

				</td>
				<td class="ListColRight">
				
				</td>
				<td class="ListColCenter">
					<xsl:attribute name="style">
						background-color:<xsl:value-of select="sc"/>;
    				</xsl:attribute>
					<xsl:value-of select="cs"/>
				</td>
				<td class="ListColRight">
					<xsl:value-of select="d"/>
				</td>
	            <td class="ListColRight">
	            	<xsl:value-of select="lc"/>
	            </td>
	            <td class="ListColCenter">
	            	<xsl:value-of select="ca"/>
	            </td>
	            <td class="ListColNoWrap">
	            	<xsl:value-of select="po"/>
	            </td>
	</tr>
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>
