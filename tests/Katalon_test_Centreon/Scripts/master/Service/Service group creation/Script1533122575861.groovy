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

//*******************************************************go to service group*******************************************************//

WebUI.click(findTestObject('Configuration/button_Configuration'))

WebUI.mouseOver(findTestObject('Configuration/Services/span_Services'))

WebUI.click(findTestObject('Configuration/Services/Service Groups/p_Service Groups'))

WebUI.waitForPageLoad(3)

//****************************************************create a new service group***************************************************//

WebUI.click(findTestObject('General/a_Add'))

def file = TestDataFactory.findTestData('Service group')
//This file only contains all the information of service group

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_name'),
	config.getValue('TimeIndicator', 1) + file.getValue('sgName', 1))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_alias'), file.getValue('sgAlias', 1))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'), file.getValue('Service', 1))

//This file contains the name of the hosts
def fileHost = TestDataFactory.findTestData('Host data')

//Links the original host to the service group
def host = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Service Groups/div_pouic - Ping'), 'title', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1 - ' + file.getValue('Service', 1), true)

WebUI.delay(1)

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Services/Service Groups/ul_pouic - Ping'))

//This links the first duplicated host to the service group
host = WebUI.modifyObjectProperty(host, 'title', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_1 - ' + file.getValue('Service', 1), true)

WebUI.delay(1)
	
WebUI.click(host)

WebUI.delay(1)

WebUI.click(findTestObject('Configuration/Services/Service Groups/ul_pouic - Ping'))

//This links the second duplicated host to the service group
host = WebUI.modifyObjectProperty(host, 'title', 'equals',
	config.getValue('TimeIndicator', 1) + fileHost.getValue('hostName', 1) + '1_2 - ' + file.getValue('Service', 1), true)

WebUI.delay(1)

WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL action
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
