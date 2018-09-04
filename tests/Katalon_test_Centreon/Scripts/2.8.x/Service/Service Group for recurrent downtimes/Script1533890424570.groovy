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

WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

WebUI.delay(1)

WebUI.click(findTestObject('Old menu/Configuration/Services/a_Services'))

WebUI.click(findTestObject('Old menu/Configuration/Services/a_Service Groups'))

WebUI.waitForPageLoad(3)

//**************************************************create the first service group*************************************************//

WebUI.click(findTestObject('General/a_Add'))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_name'),
	config.getValue('TimeIndicator', 1) + 'weekly_sg')

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_alias'), 'sg for rec downtimes 1')

//This file contains the name of the hosts
def hostFile = TestDataFactory.findTestData('Host data')

def sgFile = TestDataFactory.findTestData('Service group')

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_5 - ' + sgFile.getValue('Service', 1))

//Links the original host to the service group
def host = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Service Groups/div_pouic - Ping'), 'title', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_5 - ' + sgFile.getValue('Service', 1), true)

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Services/Service Groups/ul_pouic - Ping'))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_6 - ' + sgFile.getValue('Service', 1))

//This links the first duplicated host to the service group
host = WebUI.modifyObjectProperty(host, 'title', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_6 - ' + sgFile.getValue('Service', 1), true)
	
WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL action
WebUI.waitForPageLoad(3)

//*************************************************create the second service group*************************************************//

WebUI.click(findTestObject('General/a_Add'))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_name'),
	config.getValue('TimeIndicator', 1) + 'monthly_sg')

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_alias'), 'sg for rec downtimes 2')

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_11 - ' + sgFile.getValue('Service', 1))

//Links the original host to the service group
host = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Service Groups/div_pouic - Ping'), 'title', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_11 - ' + sgFile.getValue('Service', 1), true)

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Services/Service Groups/ul_pouic - Ping'))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_12 - ' + sgFile.getValue('Service', 1))

//This links the first duplicated host to the service group
host = WebUI.modifyObjectProperty(host, 'title', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_12 - ' + sgFile.getValue('Service', 1), true)
	
WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL action
WebUI.waitForPageLoad(3)

//**************************************************create the third service group*************************************************//

WebUI.click(findTestObject('General/a_Add'))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_name'),
	config.getValue('TimeIndicator', 1) + 'specific_date_sg')

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_sg_alias'), 'sg for rec downtimes 3')

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_17 - ' + sgFile.getValue('Service', 1))

//Links the original host to the service group
host = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Service Groups/div_pouic - Ping'), 'title', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_17 - ' + sgFile.getValue('Service', 1), true)

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Services/Service Groups/ul_pouic - Ping'))

WebUI.setText(findTestObject('Configuration/Services/Service Groups/input_select2-search__field'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_18 - ' + sgFile.getValue('Service', 1))

//This links the first duplicated host to the service group
host = WebUI.modifyObjectProperty(host, 'title', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_18 - ' + sgFile.getValue('Service', 1), true)
	
WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL action
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
