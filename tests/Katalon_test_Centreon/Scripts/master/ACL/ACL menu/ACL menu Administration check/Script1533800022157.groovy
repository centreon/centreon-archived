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
def span = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/span'),
	'text', 'equals', 'Parameters', true)

def p = findTestObject('Administration/ACL/ACL menu/ACL menu check/p')

//If 'Administration' == '1' the access is granted and the link is displayed
if (file.getValue('Administration', 1) == '1') {
	WebUI.click(findTestObject('Administration/button_Administration'))

	WebUI.waitForPageLoad(3)

    //This checks the Administration/Parameters page
    if (file.getValue('Admin/Parameters', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Centreon UI', true)
		
		WebUI.mouseOver(span)

        //If the access is granted, the button exists and I can click on it.
        if (file.getValue('Para/Centreon UI', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { //The access is not granted, the button is not displayed
            WebUI.verifyElementNotPresent(p, 1)
        }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Monitoring', true)
		
		WebUI.mouseOver(span)

        if (file.getValue('Para/Monitoring', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'CentCore', true)

		WebUI.mouseOver(span)
		
        if (file.getValue('Para/CentCore', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'My Account', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('Para/My Account', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'LDAP', true)

		WebUI.mouseOver(span)

        if (file.getValue('Para/LDAP', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'RRDTool', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('Para/RRDTool', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Debug', true)

		WebUI.mouseOver(span)

        if (file.getValue('Para/Debug', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'CSS', true)

		WebUI.mouseOver(span)

        if (file.getValue('Para/CSS', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Knowledge Base', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('Para/Knowledge Base', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Backup', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('Para/Backup', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Options', true)

		WebUI.mouseOver(span)

        if (file.getValue('Para/Options', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Data', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('Para/Data', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Images', true)

		WebUI.mouseOver(span)

        if (file.getValue('Para/Images', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Extensions', true)

    //This check the Administration/Extensions page
    if (file.getValue('Admin/Extensions', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Modules', true)

		WebUI.mouseOver(span)

        if (file.getValue('Ext/Modules', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Widgets', true)

		WebUI.mouseOver(span)

        if (file.getValue('Ext/Widgets', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'ACL', true)

    //This checks the Administration/ACL page
    if (file.getValue('Admin/ACL', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Access Groups', true)

		WebUI.mouseOver(span)

        if (file.getValue('ACL/Access Groups', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Menus Access', true)

		WebUI.mouseOver(span)

        if (file.getValue('ACL/Menus Access', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Resources Access', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('ACL/Resources Access', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Actions Access', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))

		WebUI.mouseOver(span)

        if (file.getValue('ACL/Actions Access', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
        
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Reload ACL', true)

		WebUI.mouseOver(span)

        if (file.getValue('ACL/Reload ACL', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Logs', true)

    //This checks the Administration/Logs page
    if (file.getValue('Admin/Logs', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Visualisation', true)

		WebUI.mouseOver(span)

        if (file.getValue('Logs/Visualisation', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Sessions', true)

    //This checks the Administration/Sessions page
    if (file.getValue('Admin/Sessions', 1) == '1') {
        WebUI.click(span)
		
		WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Server Status', true)

    //This checks the Administration/Server status page
    if (file.getValue('Admin/Server Status', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Databases', true)

		WebUI.mouseOver(span)

        if (file.getValue('Server/Databases', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
    
    span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'About', true)

    //This checks the Administration/About page
    if (file.getValue('Admin/About', 1) == '1') {
        p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'About', true)

		WebUI.mouseOver(span)

        if (file.getValue('About/About', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 1) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
} else { WebUI.verifyElementNotPresent(findTestObject('Administration/button_Administration'), 1) }

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
