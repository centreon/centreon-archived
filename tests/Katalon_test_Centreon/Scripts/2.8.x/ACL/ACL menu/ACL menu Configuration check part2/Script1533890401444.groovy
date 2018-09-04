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

//I change the element's properties to select the correct element according to the name displayed
def menu = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', 'Notifications', true)

def subMenu = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'title', 'equals', 'Escalations', true)

//If 'Configuration' == '1' the access is granted and the link is displayed
if(file.getValue('Configuration', 1) == '1'){
	WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

	//This checks the Configuration/Notifications page
	if(file.getValue('Conf/Notifications', 1) == '1'){
		WebUI.click(menu)
				
		//If the access is granted, the button exists, I can click on it and I can also create a new object.
		if(file.getValue('Notif/Escalations', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} //If the access is granted, the button exists, I can click on it but I cannot create a new object so the 'add' button is missing.
		else if(file.getValue('Notif/Escalations', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ //The access is not granted, the button is not displayed
			WebUI.verifyElementNotPresent(subMenu, 1)
		}
		
		p = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Hosts', true)
		
		if(file.getValue('Notif/Hosts', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Notif/Hosts', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Host Groups', true)
		
		if(file.getValue('Notif/hg', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Notif/hg', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services', true)
		
		if(file.getValue('Notif/Services', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Notif/Services', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Service Groups', true)
		
		if(file.getValue('Notif/sg', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Notif/sg', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Meta Services', true)
		
		if(file.getValue('Notif/Meta Services', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Notif/Meta Services', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }
	
	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'SNMP Traps', true)
	
	//This checks the Configuration/SNMP Traps page
	if(file.getValue('Conf/SNMP Traps', 1) == '1'){
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'SNMP Traps', true)

		WebUI.click(menu)

		if(file.getValue('SNMP/SNMP Traps', 1) == '1'){
			WebUI.click(subMenu)

			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('SNMP/SNMP Traps', 1) == '2'){
			WebUI.click(subMenu)

			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }

		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Manufacturer', true)

		//This checks the Configuration/Manufacturer page
		if(file.getValue('SNMP/Manufacturer', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('SNMP/Manufacturer', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Group', true)
		
		if(file.getValue('SNMP/Group', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('SNMP/Group', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'MIBs', true)
		
		if(file.getValue('SNMP/MIBs', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Generate', true)
		
		if(file.getValue('SNMP/Generate', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }

	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Plugin Packs', true)
	
	//This checks the Configuration/Plugin Packs page
	if(file.getValue('Conf/Plugin Packs', 1) == '1'){		//TODO le manager. I need the solution of the ticket before finishing this test.
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Manager', true)
		
		WebUI.click(menu)
		
		WebUI.click(subMenu)
		
		if(file.getValue('PP/Plugin pack documentation', 1) == '1'){
			WebUI.delay(1)
			
			//This opens the plugin pack documentation in a new Window
			WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/PP_documentation'))
			
			//This switch to the documentation window
			WebUI.switchToWindowUrl(config.getValue('url', 1) + 'main.php?p=65099&min=1&slug=base-generic')
			
			//If the access to the documentation is allowed the sentence : "You are not allowed to reach this page" is not displayed
			if(file.getValue('PP/Plugin pack documentation', 1) == '1'){
				WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
			} else{	//If the access is not granted, the sentence is displayed
				WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
			}
			
			//When the check is done this deletes the documentation's window
			WebUI.closeWindowUrl(config.getValue('url', 1) +  'main.php?p=65099&min=1&slug=base-generic')
			
			//This refocus on the other windows (the only one still opened)
			WebUI.switchToWindowUrl(config.getValue('url', 1) + 'main.php?p=65001')
		}
	} else{ WebUI.verifyElementNotPresent(menu, 1) }

	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Pollers', true)

	//This checks the Configuration/Pollers page
	if(file.getValue('Conf/Pollers', 1) == '1'){
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Pollers', true)
		
		WebUI.click(menu)
		
		if(file.getValue('Pollers/Pollers', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if (file.getValue('Pollers/Pollers', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		//If you gave access to Export the configuration, it is considered that you granted this action in acl action
		//You can Export the configuration whether the access to the Pollers is in 'Read Only' or 'Read/Write'
		if(file.getValue('Pollers/Pollers', 1) != '0'){
			WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/Export_configuration'))
			
			//If you are allowed to Export the configuration, the appropriate button is displayed
			if(file.getValue('Pollers/Export configuration', 1) == '1'){
				WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
			} else{	//If you are not allowed to Export the configuration, the button is not displayed
				WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
			}
		}
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Engine configuration', true)
		
		if(file.getValue('Pollers/Engine configuration', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
		} else if(file.getValue('Pollers/Engine configuration', 1) == '2'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button_add'), 3)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Broker configuration', true)
		
		if(file.getValue('Pollers/Broker configuration', 1) == '1'){
			WebUI.click(subMenu)
			
			//This clicks on the button 'Add with wizard'
			WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/add with wizard'))
			
			//If the boxes 'Wizard' and 'WizardAjax' are checked in the ACL menu then you can access to the current page
			//and the sentence "You are not allowed to reach this page" is not displayed
			if(file.getValue('Brok/Wizard', 1) == '1' && file.getValue('Brok/WizardAjax', 1) == '1'){
				WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 1)
			} else{	//Otherwise, the sentence is displayed
				WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 1)
			}
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Resources', true)
		
		if(file.getValue('Pollers/Resources', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }
	
	//Restore the following lines when the awie module is installed
	//menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Import/Export', true)
	
	//This checks the Configuration/Import/Export page
	/*if(file.getValue('Conf/Import/Export', 1) == '1'){
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Export', true)
		
		WebUI.click(menu)
		
		if(file.getValue('Imp/Export', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Import', true)
		
		if(file.getValue('Imp/Import', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }*/

	menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Knowledge Base', true)

	//This checks the Configuration/Knowledge Base page
	if(file.getValue('Conf/Knowledge Base', 1) == '1'){
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Hosts', true)
		
		WebUI.click(menu)
		
		if(file.getValue('KB/Hosts', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Services', true)
		
		if(file.getValue('KB/Services', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Host Templates', true)
		
		if(file.getValue('KB/ht', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
		
		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Service Templates', true)

		if(file.getValue('KB/st', 1) == '1'){
			WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
		} else{ WebUI.verifyElementNotPresent(subMenu, 1) }
	} else{ WebUI.verifyElementNotPresent(menu, 1) }
} else{
	WebUI.verifyElementNotPresent(findTestObject('Old menu/Configuration/a_Configuration'), 1)
}

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
