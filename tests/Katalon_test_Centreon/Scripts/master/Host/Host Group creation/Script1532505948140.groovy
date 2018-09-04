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

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//******************************************************go to host group page******************************************************//

WebUI.click(findTestObject('Configuration/button_Configuration'))

WebUI.mouseOver(findTestObject('Configuration/Host/span_Host'))

WebUI.click(findTestObject('Configuration/Host/Host group creation/p_Host Groups'))

WebUI.waitForPageLoad(3)

//*******************************************************create a host group*******************************************************//

WebUI.click(findTestObject('General/a_Add'))

def file = TestDataFactory.findTestData('Host group')

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_name'),
	config.getValue('TimeIndicator', 1) + file.getValue('hostGroupName', 1))

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_alias'),
	file.getValue('hostGroupAlias', 1))

//This file contains the name of the host to link to the host group
def fileHost = TestDataFactory.findTestData('Host data')

//This links the first duplicated host
def host = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Host group creation/li_pouic_1'),
		'text', 'equals', config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_1', true)

WebUI.click(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'))

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'))

WebUI.delay(1)

//This links the second duplicated host
host = WebUI.modifyObjectProperty(host, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_2', true)

WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new host group
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
