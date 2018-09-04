import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
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
import org.openqa.selenium.Keys as Keys

//Before launching this script, a View and a Widget view must have been created.

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), userName)

WebUI.setText(findTestObject('General/Login/input_password'), userPassword)

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*********************************************************Check Home page*********************************************************//

//This file contains the information about Home's part of the acl menu
def home = TestDataFactory.findTestData('ACL menu config/ACL menu Home')

def button = findTestObject('Administration/ACL/ACL menu/ACL menu check/button')

def p = findTestObject('Administration/ACL/ACL menu/ACL menu check/p')

//I change the element's properties to select the correct element according to the name displayed
def span = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu check/span'),
	'text', 'equals', 'Custom Views', true)

//If 'Administration' == '1' the access is granted and the link is displayed
if (home.getValue('Home', 1) == '1') {
	WebUI.click(findTestObject('Home/button_Home'))
	
	//This checks the Home/Custom Views page
    if (home.getValue('Home/Custom Views', 1) == '1') {
        WebUI.click(span)

        def bool = home.getValue('Cust/Widget Parameters', 1) == '1'

        if (bool) {
            WebUI.click(findTestObject('Administration/ACL/ACL menu/ACL menu check/img_editView'))
        } else {
            WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/img_editView'), 1)
        }
        
		//If the access to 'Add view' is granted just like the access to 'Widget Parameters', then the button 'Add view' is displayed
        if (bool && (home.getValue('Cust/Add View', 1) == '1')) {
            WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button'), 1)
        } else {	//Otherwise the button is not displayed ('access to 'Add view' revoked or hidden access to 'Widget Parameters' revoked
            if (WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/button'), 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(findTestObject('Home/create view/button_Add view'))
            }
        }
        
		//The text of the button is modified to match another button
        button = WebUI.modifyObjectProperty(button, 'text', 'equals', 'Edit view', true)

        if (bool && (home.getValue('Cust/Edit View', 1) == '1')) {
            WebUI.verifyElementPresent(button, 1)
        } else {
            if (WebUI.verifyElementPresent(button, 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(button)
            }
        }
        
        button = WebUI.modifyObjectProperty(button, 'text', 'equals', 'Share view', true)

        if (bool && (home.getValue('Cust/Share View', 1) == '1')) {
            WebUI.verifyElementPresent(button, 1)
        } else {
            if (WebUI.verifyElementPresent(button, 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(button)
            }
        }
        
        button = WebUI.modifyObjectProperty(button, 'text', 'equals', 'Add widget', true)

        if (bool && (home.getValue('Cust/Add Widget', 1) == '1')) {
            WebUI.verifyElementPresent(button, 1)
			
			//Restore this line when you can create a widget
			//WebUI.verifyElementPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/span_Delete widget'), 1)
        } else {
            if (WebUI.verifyElementPresent(button, 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(button)
            }
        }
        
        button = WebUI.modifyObjectProperty(button, 'text', 'equals', 'Rotation', true)

        if (bool && (home.getValue('Cust/Rotation', 1) == '1')) {
            WebUI.verifyElementPresent(button, 1)
        } else {
            if (WebUI.verifyElementPresent(button, 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(button)
            }
        }
        
        button = WebUI.modifyObjectProperty(button, 'text', 'equals', 'Delete view', true)

        if (bool && (home.getValue('Cust/Delete View', 1) == '1')) {
            WebUI.verifyElementPresent(button, 1)
        } else {
            if (WebUI.verifyElementPresent(button, 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(button)
            }
        }
        
        button = WebUI.modifyObjectProperty(button, 'text', 'equals', 'Set default', true)

        if (bool && (home.getValue('Cust/Set Default', 1) == '1')) {
            WebUI.verifyElementPresent(button, 1)
        } else {
            if (WebUI.verifyElementPresent(button, 1, FailureHandling.OPTIONAL)) {
                WebUI.verifyElementNotVisible(button)
            }
        }
    } else { WebUI.verifyElementNotPresent(span, 1) }
	
	span = WebUI.modifyObjectProperty(span, 'text', 'equals', 'Poller Statistics', true)
	
	//This checks the Home/Poller Statistics page
    if (home.getValue('Home/Poller statistics', 1) == '1') {
		p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Broker Statistics', true)
		
        WebUI.mouseOver(span)

        if (home.getValue('Poller/Broker Statistics', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 3) }
		
		p = WebUI.modifyObjectProperty(p, 'text', 'equals', 'Graphs', true)
		
		WebUI.mouseOver(findTestObject('General/button_User profile'))
		
		WebUI.mouseOver(span)
		
        if (home.getValue('Poller/Graphs', 1) == '1') {
            WebUI.click(p)
			
			WebUI.verifyElementNotPresent(findTestObject('Administration/ACL/ACL menu/ACL menu check/div_Not allowed'), 3)
        } else { WebUI.verifyElementNotPresent(p, 3) }
    } else { WebUI.verifyElementNotPresent(span, 1) }
} else { WebUI.verifyElementNotPresent(findTestObject('Home/button_Home'), 1) }

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
