<xsl:stylesheet version = '1.0'
xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:template match="/">
<table id="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td colspan="2" class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>	
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
					<xsl:value-of select="hn"/>
				</td>
				<td class="ListColLeft">
					<xsl:value-of select="sd"/>
				</td>
				<td class="ListColRight">
				infos..
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
