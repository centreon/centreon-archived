<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
<xsl:for-each select="//root">
<xsl:for-each select="//logger">
<xsl:element name="table">
	<xsl:element name="tbody">
		<xsl:attribute name="id">logger_<xsl:value-of select="id" /></xsl:attribute>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_lvl_1</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">ListColLvl1_name</xsl:attribute>
				Logger <xsl:value-of select="id" />
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">ListColLvl1_name</xsl:attribute>
				<xsl:attribute name="style">text-align: right</xsl:attribute>
				<xsl:element name="a">
					<xsl:attribute name="href">javascript:deleteRow('logger',<xsl:value-of select="id" />);</xsl:attribute>
					<xsl:element name="img">
						<xsl:attribute name="src">./img/icones/16x16/delete.gif</xsl:attribute>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_one</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/type" />
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				<xsl:element name="select">
					<xsl:attribute name="id">logger_<xsl:value-of select="id" />_type</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][type]</xsl:attribute>
					<xsl:attribute name="onchange">changeLoggerType(<xsl:value-of select="id" />,this.selectedIndex,this);</xsl:attribute>
					<xsl:element name="option">
						<xsl:if test="type='file'">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">file</xsl:attribute>
						<xsl:value-of select="//main/lang/file" />
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="type='standard'">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">standard</xsl:attribute>
						<xsl:value-of select="//main/lang/standard" />
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="type='syslog'">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">syslog</xsl:attribute>
						<xsl:value-of select="//main/lang/syslog" />
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_two</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/logging_config" />
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][config]</xsl:attribute>
					<xsl:attribute name="value">1</xsl:attribute>
					<xsl:if test="config='true'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/yes" /> 
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][config]</xsl:attribute>
					<xsl:attribute name="value">0</xsl:attribute>
					<xsl:if test="config='false'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/no" />
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_one</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/logging_debug" />
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				 
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][debug]</xsl:attribute>
					<xsl:attribute name="value">1</xsl:attribute>
					<xsl:if test="debug='true'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/yes" />  
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][debug]</xsl:attribute>
					<xsl:attribute name="value">0</xsl:attribute>
					<xsl:if test="debug='false'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/no" /> 
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_two</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/logging_info" /> 
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				 
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][info]</xsl:attribute>
					<xsl:attribute name="value">1</xsl:attribute>
					<xsl:if test="info='true'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/yes" />  
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][info]</xsl:attribute>
					<xsl:attribute name="value">0</xsl:attribute>
					<xsl:if test="info='false'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/no" /> 
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_one</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/logging_error" /> 
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				 
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][error]</xsl:attribute>
					<xsl:attribute name="value">1</xsl:attribute>
					<xsl:if test="error='true'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/yes" />  
				<xsl:element name="input">
					<xsl:attribute name="type">radio</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][error]</xsl:attribute>
					<xsl:attribute name="value">0</xsl:attribute>
					<xsl:if test="error='false'">
					<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</xsl:element>
				 <xsl:value-of select="//main/lang/no" /> 
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_two</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/logging_level" /> 
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				<xsl:element name="select">
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][level]</xsl:attribute>
					<xsl:element name="option">
						<xsl:if test="level=0">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">0</xsl:attribute>
						0
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="level=1">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">1</xsl:attribute>
						1
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="level=2">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">2</xsl:attribute>
						2
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="level=3">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">3</xsl:attribute>
						3
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_one</xsl:attribute>
			<xsl:attribute name="id">logger_<xsl:value-of select="id" />_file</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/file_for_logging" /> 
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				<xsl:element name="input">
					<xsl:attribute name="size">30</xsl:attribute>
					<xsl:attribute name="type">text</xsl:attribute>
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][file]</xsl:attribute>
					<xsl:if test="file">
						<xsl:attribute name="value"><xsl:value-of select="file"/></xsl:attribute>
					</xsl:if>
				</xsl:element>
			</xsl:element>
		</xsl:element>
		<xsl:element name="tr">
			<xsl:attribute name="class">list_one</xsl:attribute>
			<xsl:attribute name="id">logger_<xsl:value-of select="id" />_output</xsl:attribute>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowField</xsl:attribute>
				<xsl:value-of select="//main/lang/logging_output" /> 
			</xsl:element>
			<xsl:element name="td">
				<xsl:attribute name="class">FormRowValue</xsl:attribute>
				<xsl:element name="select">
					<xsl:attribute name="name">logger[<xsl:value-of select="id" />][output]</xsl:attribute>
					<xsl:element name="option">
						<xsl:if test="output='stdout'">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">stdout</xsl:attribute>
						<xsl:value-of select="//main/lang/standard_output" />
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="output='stderr'">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">stderr</xsl:attribute>
						<xsl:value-of select="//main/lang/standard_error" />
					</xsl:element>
					<xsl:element name="option">
						<xsl:if test="output='stdlog'">
							<xsl:attribute name="selected">selected</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="value">stdlog</xsl:attribute>
							<xsl:value-of select="//main/lang/standard_log" /> 
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
	</xsl:element>
</xsl:element>
</xsl:for-each>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>