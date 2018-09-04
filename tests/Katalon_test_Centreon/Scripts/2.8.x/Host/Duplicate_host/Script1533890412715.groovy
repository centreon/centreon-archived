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

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))
	
WebUI.click(findTestObject('General/Login/input_submitLogin'))

//*********************************************************go to host page*********************************************************//

WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

WebUI.delay(1)

WebUI.click(findTestObject('Old menu/Configuration/a_Hosts'))

//*********************************************************duplicate a host********************************************************//

WebUI.waitForPageLoad(3)

//This file contains the name of the host targeted
def file = TestDataFactory.findTestData('Host data')

def search = WebUI.modifyObjectProperty(findTestObject('General/input_Search'), 'name', 'equals', 'searchH', false)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + file.getValue('hostName', 1) + '1')

WebUI.click(findTestObject('General/button_Search'))

WebUI.waitForPageLoad(3)

def host = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Duplicate host/a_pouic'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('hostName', 1) + '1', true)

//This is used to get the id of the host targeted
id = WebUI.getAttribute(host, 'href')

def array = id.split('id=')

//Then the id is used to cross the correct box (the box must be found with the id)
def box = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Duplicate host/input_select15'),
	'name', 'equals', ('select[' + (array[1])) + ']', true)

WebUI.click(box)

//This set '2' in the number of hosts to duplicate
def input = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Duplicate host/input_dupNbr15'), 'name',
	'equals', 'dupNbr[' + array[1] + ']', true)

WebUI.setText(input, '2')

//This select the 'Duplicate' action. Chrome has errors because of the alert so the Failure handling is set to Optional 
WebUI.selectOptionByValue(findTestObject('General/select_More actions'), 'm',
    true, FailureHandling.OPTIONAL)

WebUI.acceptAlert()

//Wait to be sure Edge correctly duplicate the hosts
WebUI.delay(2)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
