<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="root">
<div>
	<div id="div2"   valign="top" align='left' >	
		<form name="formu2">
		    <table class="ajaxOption" >
	            <tr>
	            	<td style="vertical-align:top;"><b><xsl:value-of select="//lang/ty"/></b>
	            	</td>
					<td>
						<table>
			               	<tr>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">notification</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/notification = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/n"/>
			               		</td>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">alert</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/alert = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/a"/>
			               		</td>
			               	</tr>
			               	<tr>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">error</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/error = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/e"/>
			               		</td>
								<td></td>
			               	</tr>
						</table>
					</td>
			        <td style="vertical-align:top;"><b><xsl:value-of select="//lang/h"/></b>
			        </td>
					<td>
						<table style="">
			               	<tr>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">up</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/up = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/up"/>
			               		</td>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">down</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/down = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/do"/>
			               		</td>
			               	</tr>
			               	<tr>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">unreachable</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/unreachable = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/un"/>
			               		</td>
								<td></td>
			               	</tr>
						</table>
					</td>
               		<td style="vertical-align:top;"><b><xsl:value-of select="//lang/s"/></b></td>
					<td>
						<table style="">
			               	<tr>			               	
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">ok</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/ok = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/ok"/>
			               		</td>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">warning</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/warning = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/w"/>
			               		</td>
			               	</tr>
			               	<tr>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">critical</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/critical = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/cr"/>
			               		</td>
			               		<td>
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">unknown</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/unknown = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/uk"/>
			               		</td>
			               	</tr>
						</table>
					</td>
					<td style="vertical-align:top;"><b><xsl:value-of select="//lang/T"/></b></td>
					<td>
						<table>
			               	<tr>			               	
			               		<td rowspan="2">
									<xsl:element name='input'>
										<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form, ''); return false;</xsl:attribute>
										<xsl:attribute name="name">oh</xsl:attribute>
										<xsl:attribute name="type">checkbox</xsl:attribute>
										<xsl:if test="//infos/oh = 'true'">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</xsl:element>
	               					<xsl:value-of select="//lang/oh"/>
			               		</td>
			               	</tr>
						</table>
					</td>
				</tr>
	       	</table>
		</form>
	 </div>
</div>
<div>
	<div class="pagination">
		<table>
			<tr>
				<xsl:if test="first/@show = 'true'">
				<td style='padding-left:5px;'>
					<xsl:element name='a'>
						<xsl:attribute name="href">#</xsl:attribute>
						<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="first"/>'); return false;</xsl:attribute>
						<xsl:element name='img'>
							<xsl:attribute name="title">first</xsl:attribute>						
							<xsl:attribute name="alt">first</xsl:attribute>						
							<xsl:attribute name="src">./img/icones/16x16/arrow_left_blue_double.gif</xsl:attribute>						
						</xsl:element>
					</xsl:element>
				</td>
				</xsl:if>
				<xsl:if test="prev/@show = 'true'">
				<td style='padding-left:5px;'>
					<xsl:element name='a'>
						<xsl:attribute name="href">#</xsl:attribute>
						<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="prev"/>'); return false;</xsl:attribute>
						<xsl:element name='img'>
							<xsl:attribute name="title">previous</xsl:attribute>						
							<xsl:attribute name="alt">previous</xsl:attribute>						
							<xsl:attribute name="src">./img/icones/16x16/arrow_left_blue.gif</xsl:attribute>						
						</xsl:element>
					</xsl:element>
				</td>
				</xsl:if>
				<xsl:for-each select="//page">
				<td style='padding-left:5px;'>
					<xsl:element name='a'>
						<xsl:attribute name="href">#</xsl:attribute>
						<xsl:if test="selected = 0">
							<xsl:attribute name="class">otherPageNumber</xsl:attribute>
						</xsl:if>
						<xsl:if test="selected = 1">
							<xsl:attribute name="class">currentPageNumber</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="num"/>'); return false;</xsl:attribute>
						<xsl:value-of select="label_page"/>
					</xsl:element>
				</td>
				</xsl:for-each>
				<xsl:if test="next/@show = 'true'">
				<td style='padding-left:5px;'>
					<xsl:element name='a'>
						<xsl:attribute name="href">#</xsl:attribute>
						<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="next"/>'); return false;</xsl:attribute>
						<xsl:element name='img'>
							<xsl:attribute name="title">next</xsl:attribute>						
							<xsl:attribute name="alt">next</xsl:attribute>						
							<xsl:attribute name="src">./img/icones/16x16/arrow_right_blue.gif</xsl:attribute>						
						</xsl:element>
					</xsl:element>
				</td>
				</xsl:if>
				<xsl:if test="last/@show = 'true'">
				<td style='padding-left:5px;'>
					<xsl:element name='a'>
						<xsl:attribute name="href">#</xsl:attribute>
						<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="last"/>'); return false;</xsl:attribute>
						<xsl:element name='img'>
							<xsl:attribute name="title">last</xsl:attribute>						
							<xsl:attribute name="alt">last</xsl:attribute>						
							<xsl:attribute name="src">./img/icones/16x16/arrow_right_blue_double.gif</xsl:attribute>						
						</xsl:element>
					</xsl:element>
				</td>
				</xsl:if>
			</tr>
		</table>
	</div>
	<div style="">
	<table class="ListTable">
		<tr class='ListHeader'>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/d"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/t"/></td>
			<td class="ListColHeaderLeft" colspan="2"><xsl:value-of select="//lang/h"/></td>
			<td class="ListColHeaderCenter" style='width:50px;'><xsl:value-of select="//lang/s"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/T"/></td>
			<td class="ListColHeaderCenter">R</td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/o"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/c"/></td>
			<td class="ListColHeaderCenter"><xsl:value-of select="//lang/C"/></td>
		</tr>
		<xsl:for-each select="//line">
		<tr>
			<xsl:attribute name="class"><xsl:value-of select="class"/></xsl:attribute>
	   		<td style="padding-left:5px;" class="ListColCenter"><xsl:value-of select="date"/></td>
	   		<td style="padding-left:5px;" class="ListColCenter"><xsl:value-of select="time"/></td>
	   		<td style="padding-left:5px;"><xsl:value-of select="host_name"/></td>
	   		<td style="padding-left:5px;"><xsl:value-of select="service_description"/></td>
	   		<td style="padding-left:5px;" class="ListColCenter"><xsl:attribute name="style">background-color:<xsl:value-of select="status/@color"/>;</xsl:attribute><xsl:value-of select="status"/></td>
	   		<td style="padding-left:5px;padding-right:5px;" class="ListColCenter"><xsl:value-of select="type"/></td>
	   		<td style="padding-left:5px;padding-right:5px;" class="ListColCenter"><xsl:value-of select="retry"/></td>
	   		<td style="padding-left:5px;"><xsl:value-of select="output"/></td>
	   		<td style="padding-left:5px;"><xsl:value-of select="contact"/></td>
	   		<td style="padding-left:5px;"><xsl:value-of select="contact_cmd"/></td>
		</tr>
		</xsl:for-each>
	</table>
	</div>
	<div class="pagination">
		<table>
			<tr>
				<xsl:if test="first/@show = 'true'">
					<td style='padding-left:5px;'>
						<xsl:element name='a'>
							<xsl:attribute name="href">#</xsl:attribute>
							<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="first"/>'); return false;</xsl:attribute>
							<xsl:element name='img'>
								<xsl:attribute name="title">first</xsl:attribute>						
								<xsl:attribute name="alt">first</xsl:attribute>						
								<xsl:attribute name="src">./img/icones/16x16/arrow_left_blue_double.gif</xsl:attribute>						
							</xsl:element>
						</xsl:element>
					</td>
				</xsl:if>
				<xsl:if test="prev/@show = 'true'">
					<td style='padding-left:5px;'>
						<xsl:element name='a'>
							<xsl:attribute name="href">#</xsl:attribute>
							<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="prev"/>'); return false;</xsl:attribute>
							<xsl:element name='img'>
								<xsl:attribute name="title">previous</xsl:attribute>						
								<xsl:attribute name="alt">previous</xsl:attribute>						
								<xsl:attribute name="src">./img/icones/16x16/arrow_left_blue.gif</xsl:attribute>						
							</xsl:element>
						</xsl:element>
					</td>
				</xsl:if>
				<xsl:for-each select="//page">
				<td style='padding-left:5px;'>
					<xsl:element name='a'>
						<xsl:attribute name="href">#</xsl:attribute>
						<xsl:if test="selected = 0">
							<xsl:attribute name="class">otherPageNumber</xsl:attribute>
						</xsl:if>
						<xsl:if test="selected = 1">
							<xsl:attribute name="class">currentPageNumber</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="num"/>'); return false;</xsl:attribute>
						<xsl:value-of select="label_page"/>
					</xsl:element>
				</td>
				</xsl:for-each>
				<xsl:if test="next/@show = 'true'">
					<td style='padding-left:5px;'>
						<xsl:element name='a'>
							<xsl:attribute name="href">#</xsl:attribute>
							<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="next"/>'); return false;</xsl:attribute>
							<xsl:element name='img'>
								<xsl:attribute name="title">next</xsl:attribute>						
								<xsl:attribute name="alt">next</xsl:attribute>						
								<xsl:attribute name="src">./img/icones/16x16/arrow_right_blue.gif</xsl:attribute>						
							</xsl:element>
						</xsl:element>
					</td>
				</xsl:if>
				<xsl:if test="last/@show = 'true'">
					<td style='padding-left:5px;'>
						<xsl:element name='a'>
							<xsl:attribute name="href">#</xsl:attribute>
							<xsl:attribute name="onClick">log_4_host_page('<xsl:value-of select="//opid"/>', this.form,'<xsl:value-of select="last"/>'); return false;</xsl:attribute>
							<xsl:element name='img'>
								<xsl:attribute name="title">last</xsl:attribute>						
								<xsl:attribute name="alt">last</xsl:attribute>						
								<xsl:attribute name="src">./img/icones/16x16/arrow_right_blue_double.gif</xsl:attribute>						
							</xsl:element>
						</xsl:element>
					</td>
				</xsl:if>
			</tr>
		</table>
	</div>
</div>
<div style="display:none; ">
	<xsl:element name="div">
		<xsl:attribute name="id">openid</xsl:attribute>
		<xsl:value-of select="//opid"/>
	</xsl:element>
	<xsl:text> </xsl:text>
</div>
</xsl:template>
</xsl:stylesheet>