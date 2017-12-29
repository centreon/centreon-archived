<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="root">
<div>
	<div id="div2" valign="top" align='left'></div>
</div>
<div>
	<div>
		<table class="ToolbarTable table">
			<tr class="ToolbarTR">
	            <td></td>
	            <td class="ToolbarPagination">
	                <table>
	                    <tr>
							<xsl:if test="first/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="first"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">first</xsl:attribute>						
										<xsl:attribute name="alt">first</xsl:attribute>
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/first_rewind.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
							<xsl:if test="prev/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="prev"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">previous</xsl:attribute>						
										<xsl:attribute name="alt">previous</xsl:attribute>	
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/rewind.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
							<xsl:for-each select="//page">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:if test="selected = 0">
										<xsl:attribute name="class">otherPageNumber</xsl:attribute>
									</xsl:if>
									<xsl:if test="selected = 1">
										<xsl:attribute name="class">currentPageNumber</xsl:attribute>
									</xsl:if>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="num"/>'); return false;</xsl:attribute>
									<xsl:value-of select="label_page"/>
								</xsl:element>
							</td>
							</xsl:for-each>
							<xsl:if test="next/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="next"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">next</xsl:attribute>						
										<xsl:attribute name="alt">next</xsl:attribute>	
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/fast_forward.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
							<xsl:if test="last/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="last"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">last</xsl:attribute>						
										<xsl:attribute name="alt">last</xsl:attribute>	
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/end_forward.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
                        </tr>
                    </table> 
                </td>    
                <td class="Toolbar_pagelimit">
					<xsl:element name='a'>
						<xsl:attribute name="href">javascript:logs(this, '', 'CSV')</xsl:attribute>
						<xsl:element name='img'>
							<xsl:attribute name="title">Export CSV</xsl:attribute>
							<xsl:attribute name="alt">Export CSV</xsl:attribute>
							<xsl:attribute name="class">ico-20</xsl:attribute>
							<xsl:attribute name="src">./img/icons/csv.png</xsl:attribute>
						</xsl:element>
					</xsl:element>
					<xsl:element name='a'>
						<xsl:attribute name="href">javascript:logs(this, '', 'XML')</xsl:attribute>
						<xsl:element name='img'>
							<xsl:attribute name="title">Export XML</xsl:attribute>
							<xsl:attribute name="alt">Export XML</xsl:attribute>
							<xsl:attribute name="class">ico-20</xsl:attribute>
							<xsl:attribute name="src">./img/icons/xml.png</xsl:attribute>
						</xsl:element>
					</xsl:element>
                    <xsl:element name='select'>
                        <xsl:attribute name="onChange">setL(this.value); logsEngine('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
                        <xsl:attribute name="name">l</xsl:attribute>
                            <xsl:for-each select="//limitValue">
                                <xsl:element name='option'>
                                    <xsl:attribute name="value"><xsl:value-of select="current()"/></xsl:attribute>
                                    <xsl:if test="current() = //limit">
                                        <xsl:attribute name="selected">selected</xsl:attribute>
                                    </xsl:if>
                                    <xsl:value-of select="current()"/>
                                </xsl:element>
                            </xsl:for-each>
                    </xsl:element>
                </td>
            </tr>
		</table>
	</div>
	<div style="">
	<table class="ListTable">
		<tr class='ListHeader'>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/d"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/t"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/P"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/o"/></td>
		</tr>
		<xsl:for-each select="//line">
		<tr>
			<xsl:attribute name="class"><xsl:value-of select="class"/></xsl:attribute>
	   		<td style="padding-left:5px;" class="ListColCenter"><xsl:value-of select="date"/></td>
	   		<td style="padding-left:5px;" class="ListColCenter"><xsl:value-of select="time"/></td>
	   		<td style="padding-left:5px;" class="ListColCenter"><xsl:value-of select="poller"/></td>
	   		<td style="padding-left:5px;"><xsl:value-of select="output"/></td>
		</tr>
		</xsl:for-each>
	</table>
	</div>
	<div>
		<table class="ToolbarTable table">
			<tr class="ToolbarTR">
	            <td></td>
	            <td class="ToolbarPagination">
	                <table>
	                    <tr>
							<xsl:if test="first/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="first"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">first</xsl:attribute>						
										<xsl:attribute name="alt">first</xsl:attribute>
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/first_rewind.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
							<xsl:if test="prev/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="prev"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">previous</xsl:attribute>						
										<xsl:attribute name="alt">previous</xsl:attribute>	
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/rewind.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
							<xsl:for-each select="//page">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:if test="selected = 0">
										<xsl:attribute name="class">otherPageNumber</xsl:attribute>
									</xsl:if>
									<xsl:if test="selected = 1">
										<xsl:attribute name="class">currentPageNumber</xsl:attribute>
									</xsl:if>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="num"/>'); return false;</xsl:attribute>
									<xsl:value-of select="label_page"/>
								</xsl:element>
							</td>
							</xsl:for-each>
							<xsl:if test="next/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="next"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">next</xsl:attribute>						
										<xsl:attribute name="alt">next</xsl:attribute>	
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/fast_forward.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
							<xsl:if test="last/@show = 'true'">
							<td style='width:auto;padding-left:5px;'>
								<xsl:element name='a'>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:attribute name="onClick">log_4_engine_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="last"/>'); return false;</xsl:attribute>
									<xsl:element name='img'>
										<xsl:attribute name="title">last</xsl:attribute>						
										<xsl:attribute name="alt">last</xsl:attribute>	
			                            <xsl:attribute name="class">ico-14</xsl:attribute>
										<xsl:attribute name="src">./img/icons/end_forward.png</xsl:attribute>						
									</xsl:element>
								</xsl:element>
							</td>
							</xsl:if>
                            </tr>
                        </table> 
                </td>  
                <td class="Toolbar_pagelimit">   
                    <xsl:element name='select'>
                        <xsl:attribute name="onChange">setL(this.value); logsEngine('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
                        <xsl:attribute name="name">l</xsl:attribute>
                            <xsl:for-each select="//limitValue">
                                <xsl:element name='option'>
                                    <xsl:attribute name="value"><xsl:value-of select="current()"/></xsl:attribute>
                                    <xsl:if test="current() = //limit">
                                        <xsl:attribute name="selected">selected</xsl:attribute>
                                    </xsl:if>
                                    <xsl:value-of select="current()"/>
                                </xsl:element>
                            </xsl:for-each>
                    </xsl:element>
                </td>
            </tr>
		</table>
	</div>
</div>
</xsl:template>
</xsl:stylesheet>
