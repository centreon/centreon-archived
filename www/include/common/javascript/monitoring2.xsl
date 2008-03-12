<xsl:stylesheet version = '1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:template match="/">
<table class="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;"></td>
	</tr>
	<xsl:for-each select="//line">
	<tr>
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
					background-color:<xsl:value-of select="host_color"/>;
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="host_name"/>
		</td>
		<td class="ListColLeft">
			<xsl:value-of select="service_description"/>
		</td>
		<td class="ListColRight">
		infos..
		</td>
		<td class="ListColCenter">
			<xsl:attribute name="style">
				background-color:<xsl:value-of select="service_color"/>;
			</xsl:attribute>
			<xsl:value-of select="current_state"/>
		</td>
		<td class="ListColRight"><div id="last_check" style="white-space:nowrap;">
			<xsl:value-of select="last_check"/>
		</div></td>
        <td class="ListColRight"><div id="last_state_change" style="white-space:nowrap;">
        	<xsl:value-of select="last_state_change"/>
        </div></td>
        <td class="ListColCenter"><div id="current_attempt">
        	<xsl:value-of select="current_attempt"/>
        </div></td>
        <td class="ListColNoWrap"><div id="plugin_output">
        	<xsl:value-of select="plugin_output"/>
        </div></td>
	</tr>
	</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>