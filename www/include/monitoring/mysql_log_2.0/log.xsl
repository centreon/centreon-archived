<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="root">



<div style="position:relative; width:150px; left:270px; top: -40px; * html left:340px;"  valign="top" align='left'>
	<xsl:element name="input">
		<xsl:attribute name="type">button</xsl:attribute>
		<xsl:attribute name="name">graph</xsl:attribute>
		<xsl:attribute name="value">Apply period</xsl:attribute>
		<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
	</xsl:element>
	<xsl:text> </xsl:text>
</div>

<div id="div2" style=" width:400px; position:relative;  left:340px; top: -87px"  valign="top" align='left'>

	<form name="formu2">
	    <table id="ListTableSmall" >
            <tr class="ListHeader">
            	<td class="FormHeader" colspan="2"><xsl:value-of select="//lang/typeAlert"/></td>
            </tr>
			<tr>
				<td style="width:200px;">
					<table style="">
		               	<tr >
		               		<td>
		               					<xsl:value-of select="//lang/notification"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">notification</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/notification = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/alert"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">alert</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/alert = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/error"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">error</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/error = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               	</tr>
					</table>
				</td>
				</tr>
        	</table>
		</form>
 </div>

<div id="div3" style=" width:400px; position:relative;  left:500px; top:-140px"  valign="top" align='left'>

	<form name="formu3">
	    <table id="ListTableSmall" >
            <tr class="ListHeader">
		               		<td class="FormHeader">
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">host</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/host = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               					<xsl:value-of select="//lang/host"/>
		               		</td>
            </tr>
			<tr>
				<td style="width:200px;">
					<table style="">
		               	<tr >
		               		<td>
		               					<xsl:value-of select="//lang/up"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">up</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/up = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/down"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">down</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/down = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/unreachable"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">unreachable</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/unreachable = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>


		               	</tr>
					</table>
				</td>
				</tr>
        	</table>
		</form>
 </div>

<div id="div4" style=" width:500px; position:relative;  left:660px; top:-193px"  valign="top" align='left'>

	<form name="formu4">
	    <table id="ListTableSmall" >
            <tr class="ListHeader">
               		<td class="FormHeader">
								<xsl:element name='input'>
									<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
									<xsl:attribute name="name">service</xsl:attribute>
									<xsl:attribute name="type">checkbox</xsl:attribute>

									<xsl:if test="//infos/service = 1">
										<xsl:attribute name="checked">checked</xsl:attribute>
									</xsl:if>
								</xsl:element>
               					<xsl:value-of select="//lang/service"/>
               		</td>

            </tr>
			<tr>
				<td style="width:320px;">
					<table style="">
		               	<tr >
		               	
		               		<td>
		               					<xsl:value-of select="//lang/ok"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">ok</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/ok = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/warning"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">warning</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/warning = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/critical"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">critical</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/critical = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               		<td>
		               					<xsl:value-of select="//lang/unknown"/>
										<xsl:element name='input'>
											<xsl:attribute name="onClick">log_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
											<xsl:attribute name="name">unknown</xsl:attribute>
											<xsl:attribute name="type">checkbox</xsl:attribute>
		
											<xsl:if test="//infos/unknown = 1">
												<xsl:attribute name="checked">checked</xsl:attribute>
											</xsl:if>
										</xsl:element>
		               		</td>
		               	</tr>
					</table>
				</td>
				</tr>
        	</table>
		</form>
 </div>




<div style="position:relative; top: -168px;">
	<table>
		<tr>
		<xsl:for-each select="//page">
			<td>
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
		</tr>
	</table>
</div>

<div style="position:relative; top: -168px;">
<table id="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderCenter">Day</td>
		<td class="ListColHeaderCenter">Time</td>
		<td class="ListColHeaderCenter" colspan="2">Host</td>
		<td class="ListColHeaderCenter">Status</td>
		<td class="ListColHeaderCenter">Type</td>
		<td class="ListColHeaderCenter">Retry</td>
		<td class="ListColHeaderCenter" colspan="2">Output</td>
	</tr>

	<xsl:for-each select="//line">
	<tr>
		<xsl:attribute name="class"><xsl:value-of select="class"/></xsl:attribute>
	
   		<td style="padding-left:10px;">
   			<xsl:value-of select="date"/>
   		</td>
   		<td style="padding-left:10px;">
   			<xsl:value-of select="time"/>
   		</td>
   		<td style="padding-left:10px;">
   			<xsl:value-of select="host_name"/>
   		</td>
   		<td style="padding-left:10px;">
   			<xsl:value-of select="service_description"/>
   		</td>
   		<td style="padding-left:10px;">
   			<xsl:value-of select="status"/>
   		</td>
   		<td style="padding-left:10px;">
   			<xsl:value-of select="type"/>
   		</td>
   		<td style="padding-left:10px;">
   			<xsl:value-of select="retry"/>
   		</td>

   		<td style="padding-left:10px;">
   			<xsl:value-of select="output"/>
   		</td>
	</tr>
	</xsl:for-each>
</table>
</div>
</xsl:template>
</xsl:stylesheet>