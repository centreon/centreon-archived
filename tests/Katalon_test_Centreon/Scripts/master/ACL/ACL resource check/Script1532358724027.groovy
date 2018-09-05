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

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

def fileConfiguration = TestDataFactory.findTestData('ACL menu config/ACL menu Configuration')

//********************************************************Login as an admin********************************************************//

//I need to access to certain pages to verify if the ACL resources is correctly handled
WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//**********************************************************go to ACL menu*********************************************************//

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))

WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

WebUI.waitForElementClickable(findTestObject('Administration/ACL/span_ACL'), 3)

//*************************************************Give access to the correct pages************************************************//

def fileACLMenu = TestDataFactory.findTestData('ACL menu config/ACL menu')

WebUI.setText(findTestObject('Administration/ACL/ACL menu/input_Search'),
	fileACLMenu.getValue('ACLMenuName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.delay(1)

def a = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + fileACLMenu.getValue('ACLMenuName', 1), true)

WebUI.click(a)

def img = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home'),
	'id', 'equals', 'img_3', true)

WebUI.delay(1)

WebUI.click(img)

WebUI.scrollToElement(img, 3)

//Give access to hosts pages
img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_0', true)

WebUI.click(img)

def radio = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/radio'),
	'value', 'equals', '2', true)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_0', true)

WebUI.click(radio)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_1', true)
	
WebUI.click(radio)

WebUI.click(img)

//Give access to services pages
img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_1', true)
	
WebUI.click(img)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_0', true)

WebUI.click(radio)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_2', true)

WebUI.click(radio)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_5', true)

WebUI.click(radio)

WebUI.click(img)

WebUI.click(findTestObject('General/input_submitC'))

WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

//*******************************************************Login as a non admin******************************************************//

def fileUser = TestDataFactory.findTestData('User config')

WebUI.setText(findTestObject('General/Login/input_useralias'),
	config.getValue('TimeIndicator', 1) + fileUser.getValue('UserName', 1) + '1')

WebUI.setText(findTestObject('General/Login/input_password'), fileUser.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*******************************************************Go to the host page*******************************************************//

WebUI.click(findTestObject('Configuration/button_Configuration'))

WebUI.mouseOver(findTestObject('Configuration/Host/span_Host'))

WebUI.click(findTestObject('Configuration/Host/Host creation/p_Hosts'))

//**************************************************Verify the acl resource effet**************************************************//

def fileHost = TestDataFactory.findTestData('Host data')

def search = WebUI.modifyObjectProperty(findTestObject('General/input_Search'), 'name', 'equals', 'searchH', true)

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1', true)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.waitForPageLoad(3)

WebUI.verifyElementPresent(a, 1)

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_2', true)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_2')

WebUI.click(findTestObject('General/button_Search'))

WebUI.waitForPageLoad(3)

WebUI.verifyElementPresent(a, 1)

//*****************************************************Go to the services page*****************************************************//

WebUI.mouseOver(findTestObject('Configuration/Services/span_Services'))

WebUI.click(findTestObject('Configuration/Services/p_Services by host'))

WebUI.waitForElementClickable(findTestObject('Configuration/Services/span_Services'), 3)

//**************************************************Verify the acl resource effet**************************************************//

def fileSg = TestDataFactory.findTestData('Service group')

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1', true)

search = WebUI.modifyObjectProperty(search, 'name', 'equals', 'searchS', true)

WebUI.setText(search, fileSg.getValue('Service', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.waitForPageLoad(3)

WebUI.verifyElementPresent(a, 1)

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_1', true)

WebUI.verifyElementPresent(a, 1)

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_2', true)

WebUI.verifyElementPresent(a, 1)

//***************************************************Go to the service group page**************************************************//

//Without this line, the test is not working on Firefox
WebUI.mouseOver(findTestObject('Configuration/Host/span_Host'))

WebUI.mouseOver(findTestObject('Configuration/Services/span_Services'))

WebUI.click(findTestObject('Configuration/Services/Service Groups/p_Service Groups'))

WebUI.waitForElementClickable(findTestObject('Configuration/Services/span_Services'), 3)

//**************************************************Verify the acl resource effet**************************************************//

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileSg.getValue('sgName', 1), true)

search = WebUI.modifyObjectProperty(search, 'name', 'equals', 'searchSG', true)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + fileSg.getValue('sgName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.waitForPageLoad(3)

WebUI.verifyElementPresent(a, 1)

//***************************************************Go to the meta service page***************************************************//

//Without this line, the test is not working on Firefox
WebUI.mouseOver(findTestObject('Configuration/Host/span_Host'))

WebUI.mouseOver(findTestObject('Configuration/Services/span_Services'))

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/p_Meta Services'))

WebUI.waitForElementClickable(findTestObject('Configuration/Services/span_Services'), 3)

//**************************************************Verify the acl resource effet**************************************************//

def fileMs = TestDataFactory.findTestData('Meta service')

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileMs.getValue('metaServiceName', 1), true)

search = WebUI.modifyObjectProperty(search, 'name', 'equals', 'searchMS', true)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + fileMs.getValue('metaServiceName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.waitForPageLoad(3)

WebUI.verifyElementPresent(a, 1)

WebUI.click(findTestObject('General/button_User profile'))

WebUI.verifyElementClickable(findTestObject('General/span_Sign out'))

WebUI.click(findTestObject('General/span_Sign out'))

//********************************************************Login as an admin********************************************************//

//I need to access to certain pages to verify if the ACL resources is correctly handled
WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//**********************************************************go to ACL menu*********************************************************//

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))

WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

//*******************************************************Restore the acl menu******************************************************//

WebUI.setText(findTestObject('Administration/ACL/ACL menu/input_Search'),
	fileACLMenu.getValue('ACLMenuName', 1))

WebUI.click(findTestObject('General/button_Search'))

WebUI.delay(1)

a = WebUI.modifyObjectProperty(findTestObject('General/a'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + fileACLMenu.getValue('ACLMenuName', 1), true)

WebUI.click(a)

img = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/img_Home'),
	'id', 'equals', 'img_3', true)

WebUI.delay(1)

WebUI.click(img)

WebUI.scrollToElement(img, 3)

img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_0', true)

WebUI.waitForElementClickable(img, 3)

WebUI.click(img)

radio = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL menu/ACL menu creation/radio'),
	'value', 'equals', fileConfiguration.getValue('Hosts/Hosts', 1), true)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_0', true)

WebUI.click(radio)

radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Hosts/hg', 1), true)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_0_1', true)

WebUI.click(radio)

WebUI.click(img)

img = WebUI.modifyObjectProperty(img, 'id', 'equals', 'img_3_1', true)

WebUI.click(img)

radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Services by hosts', 1), true)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_0', true)

WebUI.click(radio)

radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/sg', 1), true)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_2', true)

WebUI.click(radio)

radio = WebUI.modifyObjectProperty(radio, 'value', 'equals', fileConfiguration.getValue('Ser/Meta Services', 1), true)

radio = WebUI.modifyObjectProperty(radio, 'id', 'equals', 'i3_1_5', true)
	
WebUI.click(radio)

WebUI.click(img)

WebUI.click(findTestObject('General/input_submitC'))

WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
