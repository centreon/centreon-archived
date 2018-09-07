import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import org.junit.After

import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*******************************************************go to Contact Groups******************************************************//

CustomKeywords.'custom.NavigationConfiguration.accessContactGroups'()

//********************************************links the first contact group to a contact*******************************************//

def cgFile = TestDataFactory.findTestData('Contact group')

def searchField = WebUI.modifyObjectProperty(findTestObject('General/input_Search'), 'name', 'equals', 'searchCG', true)

WebUI.setText(searchField, config.getValue('TimeIndicator', 1) + cgFile.getValue('cgName', 1) + '1')

WebUI.click(findTestObject('General/button_Search'))

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
	config.getValue('TimeIndicator', 1) + cgFile.getValue('cgName', 1) + '1', true)

WebUI.click(a)

def userFile = TestDataFactory.findTestData('User config')

WebUI.setText(findTestObject('Configuration/Users/Contact Groups/input_Linked Contacts'),
	userFile.getValue('UserAlias', 2))

def div = WebUI.modifyObjectProperty(findTestObject('General/div'), 'text', 'equals',
	userFile.getValue('UserAlias', 2) + '1', true)

WebUI.click(div)

WebUI.click(findTestObject('General/input_submitC'))

WebUI.delay(3)

//********************************************links the second contact group to contacts*******************************************//

WebUI.setText(searchField, config.getValue('TimeIndicator', 1) + cgFile.getValue('cgName', 1) + '2')

WebUI.click(findTestObject('General/button_Search'))

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + cgFile.getValue('cgName', 1) + '2', true)

WebUI.click(a)

WebUI.setText(findTestObject('Configuration/Users/Contact Groups/input_Linked Contacts'),
	userFile.getValue('UserAlias', 2))

div = WebUI.modifyObjectProperty(div, 'text', 'equals',
	userFile.getValue('UserAlias', 2) + '2', true)

WebUI.click(div)

WebUI.click(findTestObject('Configuration/Users/Contact Groups/span_contact group'))

div = WebUI.modifyObjectProperty(div, 'text', 'equals',
	userFile.getValue('UserAlias', 2) + '3', true)

WebUI.click(div)

WebUI.click(findTestObject('General/input_submitC'))

WebUI.delay(5)

//********************************************links the third contact group to a contact*******************************************//

WebUI.setText(searchField, config.getValue('TimeIndicator', 1) + cgFile.getValue('cgName', 1) + '3')

WebUI.click(findTestObject('General/button_Search'))

a = WebUI.modifyObjectProperty(a, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + cgFile.getValue('cgName', 1) + '3', true)

WebUI.click(a)

WebUI.setText(findTestObject('Configuration/Users/Contact Groups/input_Linked Contacts'),
	userFile.getValue('UserAlias', 2))

div = WebUI.modifyObjectProperty(div, 'text', 'equals',
	userFile.getValue('UserAlias', 2) + '4', true)

WebUI.click(div)

WebUI.click(findTestObject('General/input_submitC'))

WebUI.delay(5)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()

