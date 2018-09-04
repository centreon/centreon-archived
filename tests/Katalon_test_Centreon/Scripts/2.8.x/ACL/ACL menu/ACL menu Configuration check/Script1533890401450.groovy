import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import org.junit.After
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

//This file contains the information about Configuration's part of the acl menu
def file = TestDataFactory.findTestData('ACL menu config/ACL menu Configuration')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'

//***************************************************Check the Configuration page**************************************************//


WebUI.waitForPageLoad(3)

def menu = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', 'Hosts', true)

def subMenu = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'title', 'equals', 'Hosts', true)

//If 'Configuration' == '1' the access is granted and the link is displayed
if(file.getValue('Configuration', 1) == '1') {
	WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))
	
	//This checks the Configuration/Hosts page
	if(file.getValue('Conf/Hosts', 1) == '1'){
		WebUI.click(menu)
		
		//If the access is granted, the link exists, I can click on it and I can also create a new object.
		if(file.getValue('Hosts/Hosts', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} //If the access is granted, the link exists, I can click on it but I cannot create a new object so the 'add' button is missing.
		else if (file.getValue('Hosts/Hosts', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{//The access is not granted, the link is not displayed
			WebUI.verifyElementNotPresent(subMenu, 1)
		}
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Host Groups', true)
		
		if(file.getValue('Hosts/hg', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Hosts/hg', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Templates', true)
		
		if(file.getValue('Hosts/Templates', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Hosts/Templates', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Categories', true)
		
		if(file.getValue('Hosts/Categories', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Hosts/Categories', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }
	
	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Services', true)
	
	//This checks the Configuration/Services page
	if(file.getValue('Conf/Services', 1) == '1'){		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services by host', true)

		WebUI.click(menu)

		if(file.getValue('Ser/Services by hosts', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Ser/Services by hosts', 1) == '2'){
			WebUI.click(subMenu)

			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }

		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services by host group', true)
		
		if(file.getValue('Ser/Services by hg', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Ser/Services by hg', 1) == '2'){
			WebUI.click(subMenu)

			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }

		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Service Groups', true)
		
		if(file.getValue('Ser/sg', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Ser/sg', 1) == '2'){
			WebUI.click(subMenu)

			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }

		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Templates', true)
		
		if(file.getValue('Ser/Templates', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Ser/Templates', 1) == '2'){
			WebUI.click(subMenu)

			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }

		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Categories', true)
		
		if(file.getValue('Ser/Categories', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Ser/Categories', 1) == '2'){
			WebUI.click(subMenu)

			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1)	}
	
	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Users', true)
	
	//This checks the Configuration/Users page
	if(file.getValue('Conf/Users', 1) == '1'){		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Contacts / Users', true)
		
		WebUI.click(menu)
		
		if(file.getValue('Users/Contacts', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Users/Contacts', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Contact Templates', true)
		
		if(file.getValue('Users/ct', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Users/ct', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Contact Groups', true)
		
		if(file.getValue('Users/cg', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Users/cg', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Time Periods', true)
		
		if(file.getValue('Users/tp', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Users/tp', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }
	
	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Commands', true)
	
	//This checks the Configuration/Commands page
	if(file.getValue('Conf/Commands', 1) == '1'){
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Checks', true)
		
		WebUI.click(menu)
		
		if(file.getValue('Com/Checks', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Com/Checks', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Notifications', true)
		
		if(file.getValue('Com/Notifications', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Com/Notifications', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Discovery', true)
		
		if(file.getValue('Com/Discovery', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Com/Discovery', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Miscellaneous', true)
		
		if(file.getValue('Com/Miscellaneous', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Com/Miscellaneous', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Connectors', true)
		
		if(file.getValue('Com/Connectors', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Com/Connectors', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }
} else{ WebUI.verifyElementNotPresent(findTestObject('Old menu/Configuration/a_Configuration'), 1) }

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
