import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import org.stringtemplate.v4.compiler.STParser.element_return
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), userName)

WebUI.setText(findTestObject('General/Login/input_password'), userPassword)

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*******************************************************Check Reporting page******************************************************//

//This file contains the information about Reporting's part of the acl menu
def file = TestDataFactory.findTestData('ACL menu config/ACL menu Reporting')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'

//I change the element's properties to select the correct element according to the name displayed
def p = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/p'),
	'text', 'equals', 'Hosts', true)

def span = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/span'),
	'text', 'equals', 'Dashboard', true)

//If 'Reporting' == '1' the access is granted and the link is displayed
if(file.getValue('Reporting', 1) == '1'){
	WebUI.click(findTestObject('Reporting/button_Reporting'))
	
	//This checks the Reporting/Dashboard page
	if(file.getValue('Rep/Dashboard', 1) == '1'){	
		//This boolean is set to 'true' when the Dash/Services access is verified
		def bool = false
		
		//This file contains the name of the services linked to the hosts
		def fileService = TestDataFactory.findTestData('Services')
		
		WebUI.mouseOver(span)
		
		if(file.getValue('Dash/Hosts', 1) == '1'){
			WebUI.click(p)
			
			if (file.getValue('Dash/Services', 1) == '1'){
				//This select an host
				def select = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/select'),
					'name', 'equals', 'host', true)
				
				WebUI.click(select)
				
				//This file is used to get the name of the host
				def host = TestDataFactory.findTestData('Host data')
				
				def option = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/option'),
					'text', 'equals', config.getValue('TimeIndicator', 1) + host.getValue('hostName', 1) + '1', true)
				
				WebUI.click(option)
				
				//This two following commands select one service of the host
				option = WebUI.modifyObjectProperty(findTestObject('General/a'),
					'text', 'equals', fileService.getValue('Services', 1), true)
				
				WebUI.click(option)
				
				//The Dash/Services access has been verified
				bool = true
			}
		} else{ WebUI.verifyElementNotPresent(p, 1) }
		
		p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Host Groups', true)
		
		WebUI.mouseOver(span)
		
		if(file.getValue('Dash/hg', 1) == '1'){
			WebUI.click(p)
			
			if (!bool && file.getValue('Dash/Services', 1) == '1'){
				//This select an host
				def select = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/select'),
					'name', 'equals', 'item', true)
				
				WebUI.click(select)
				
				//This file is used to get the name of the host group
				def hostGroup = TestDataFactory.findTestData('Host group')
				
				def option = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/option'),
					'text', 'equals', config.getValue('TimeIndicator', 1) + hostGroup.getValue('hostGroupName', 1), true)
				
				WebUI.click(option)
				
				def host = TestDataFactory.findTestData('Host data')
				
				//This two following commands select one service of the host
				option = WebUI.modifyObjectProperty(findTestObject('General/a'),
					'text', 'equals', config.getValue('TimeIndicator', 1) + host.getValue('hostName', 1) + '1_1', true)
				
				WebUI.click(option)
				
				//The Dash/Services access has been verified
				bool = true
			}
		} else{ WebUI.verifyElementNotPresent(p, 1) }
		
		p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Service Groups', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))
		
		WebUI.mouseOver(span)
		
		if(file.getValue('Dash/sg', 1) == '1'){ 
			WebUI.click(p)
			
			if (!bool && file.getValue('Dash/Services', 1) == '1'){
				//This select an host
				def select = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/select'),
					'name', 'equals', 'item', true)
				
				WebUI.click(select)
				
				//This file is used to get the name of the host group
				def serviceGroup = TestDataFactory.findTestData('Service group')
				
				def option = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/option'),
					'text', 'equals', config.getValue('TimeIndicator', 1) + serviceGroup.getValue('sgName', 1), true)
				
				WebUI.click(option)
				
				def host = TestDataFactory.findTestData('Host data')
				
				//This two following commands select one service of the host
				option = WebUI.modifyObjectProperty(findTestObject('General/a'),
					'text', 'equals', config.getValue('TimeIndicator', 1) + host.getValue('hostName', 1) + '1_1', true)
				
				WebUI.click(option)
				
				//The Dash/Services access has been verified
				bool = true
			}
		} else{ WebUI.verifyElementNotPresent(p, 1) }
		
		//If 'Dash/Services' is the only access granted in the Dashboard web page,
		//the sentence "You are not allowed to reach this page" is displayed
		if(!bool && file.getValue('Dash/Services', 1) == '1'){
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 1)
		}
	} else{ WebUI.verifyElementNotPresent(span, 1) }
} else{ WebUI.verifyElementNotPresent(findTestObject('Reporting/button_Reporting'), 1) }

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
