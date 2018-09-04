import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import org.junit.After as After
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

//****************************************************Check Administration page****************************************************//

//This file contains the information about Administration's part of the acl menu
def file = TestDataFactory.findTestData('ACL menu config/ACL menu Administration')

//'0' is for 'No access', '1' is for 'Read/Write', '2' is for 'Read Only'

//I change the element's properties to select the correct element according to the name displayed
def menu = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals', 'Parameters', true)

def subMenu = WebUI.modifyObjectProperty(findTestObject('General/a'), 'title', 'equals', 'Centreon UI', true)

//If 'Administration' == '1' the access is granted and the link is displayed
if (file.getValue('Administration', 1) == '1') {
	WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

	WebUI.waitForPageLoad(3)

    //This checks the Administration/Parameters page
    if (file.getValue('Admin/Parameters', 1) == '1') {		
		WebUI.click(menu)

        //If the access is granted, the button exists and I can click on it.
        if (file.getValue('Para/Centreon UI', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { //The access is not granted, the button is not displayed
            WebUI.verifyElementNotPresent(subMenu, 1)
        }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Monitoring', true)
		
        if (file.getValue('Para/Monitoring', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'CentCore', true)

        if (file.getValue('Para/CentCore', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'My Account', true)
		
        if (file.getValue('Para/My Account', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'LDAP', true)

        if (file.getValue('Para/LDAP', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'RRDTool', true)

        if (file.getValue('Para/RRDTool', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Debug', true)

        if (file.getValue('Para/Debug', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'CSS', true)

        if (file.getValue('Para/CSS', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Knowledge Base', true)

        if (file.getValue('Para/Knowledge Base', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Backup', true)

        if (file.getValue('Para/Backup', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Options', true)

        if (file.getValue('Para/Options', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Data', true)

        if (file.getValue('Para/Data', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Images', true)

        if (file.getValue('Para/Images', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Extensions', true)

    //This check the Administration/Extensions page
    if (file.getValue('Admin/Extensions', 1) == '1') {
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Modules', true)

		WebUI.click(menu)

        if (file.getValue('Ext/Modules', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Widgets', true)

        if (file.getValue('Ext/Widgets', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }

    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'ACL', true)

    //This checks the Administration/ACL page
    if (file.getValue('Admin/ACL', 1) == '1') {
		WebUI.click(menu)

		subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Access Groups', true)

        if (file.getValue('ACL/Access Groups', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Menus Access', true)

        if (file.getValue('ACL/Menus Access', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Resources Access', true)

        if (file.getValue('ACL/Resources Access', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Actions Access', true)

        if (file.getValue('ACL/Actions Access', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
        
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Reload ACL', true)

        if (file.getValue('ACL/Reload ACL', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Logs', true)

    //This checks the Administration/Logs page
    if (file.getValue('Admin/Logs', 1) == '1') {
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Visualisation', true)

		WebUI.click(menu)

        if (file.getValue('Logs/Visualisation', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Sessions', true)

    //This checks the Administration/Sessions page
    if (file.getValue('Admin/Sessions', 1) == '1') {
        WebUI.click(menu)
		
		WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'Server Status', true)

    //This checks the Administration/Server status page
    if (file.getValue('Admin/Server Status', 1) == '1') {
		WebUI.click(menu)
		
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'Databases', true)

        if (file.getValue('Server/Databases', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
    
    menu = WebUI.modifyObjectProperty(menu, 'text', 'equals', 'About', true)

    //This checks the Administration/About page
    if (file.getValue('Admin/About', 1) == '1') {
        subMenu = WebUI.modifyObjectProperty(subMenu, 'title', 'equals', 'About', true)

		WebUI.click(menu)

        if (file.getValue('About/About', 1) == '1') {
            WebUI.click(subMenu)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(subMenu, 1) }
    } else { WebUI.verifyElementNotPresent(menu, 1) }
} else { WebUI.verifyElementNotPresent(findTestObject('Old menu/Administration/a_Administration'), 1) }

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
