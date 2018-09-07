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

CustomKeywords.'custom.NavigationConfiguration.accessHostGroups'()

WebUI.waitForPageLoad(3)

//***************************************************create the first host group***************************************************//

WebUI.click(findTestObject('General/a_Add'))

def hostFile = TestDataFactory.findTestData('Host data')

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_name'),
	config.getValue('TimeIndicator', 1) + 'weekly_hg')

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_alias'),
	'hg for rec downtimes 1')

//This links the first duplicated host
def host = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Host group creation/li_pouic_1'),
		'text', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_3', true)

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_3')

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'))

//This links the second duplicated host
host = WebUI.modifyObjectProperty(host, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_4', true)

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'),
	config.getValue('TimeIndicator', 2) + hostFile.getValue('hostName', 2) + '1_4')

WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//******************************************************Verify element created*****************************************************//

//Wait to be sure Edge correctly create the new host group
WebUI.waitForPageLoad(3)

CustomKeywords.'custom.creationVerified.verifyObjectCreated'('searchHg',
	config.getValue('TimeIndicator', 1) + 'weekly_hg')

//**************************************************create the second host group***************************************************//

WebUI.click(findTestObject('General/a_Add'))

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_name'),
	config.getValue('TimeIndicator', 1) + 'monthly_hg')

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_alias'),
	'hg for rec downtimes 2')

//This links the first duplicated host
host = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Host group creation/li_pouic_1'),
		'text', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_9', true)

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_9')

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'))

//This links the second duplicated host
host = WebUI.modifyObjectProperty(host, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_10', true)

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_10')

WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//******************************************************Verify element created*****************************************************//

//Wait to be sure Edge correctly create the new host group
WebUI.waitForPageLoad(3)

CustomKeywords.'custom.creationVerified.verifyObjectCreated'('searchHg',
	config.getValue('TimeIndicator', 1) + 'monthly_hg')

//***************************************************create the third host group***************************************************//

WebUI.click(findTestObject('General/a_Add'))

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_name'),
	config.getValue('TimeIndicator', 1) + 'specific_date_hg')

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_alias'),
	'hg for rec downtimes 3')

//This links the first duplicated host
host = WebUI.modifyObjectProperty(findTestObject('Configuration/Host/Host group creation/li_pouic_1'),
		'text', 'equals', config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_15', true)

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_15')

WebUI.click(host)

WebUI.click(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'))

//This links the second duplicated host
host = WebUI.modifyObjectProperty(host, 'text', 'equals',
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_16', true)

WebUI.setText(findTestObject('Configuration/Host/Host group creation/input_hg_hosts'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 2) + '1_16')

WebUI.click(host)

WebUI.click(findTestObject('General/input_submitA'))

//******************************************************Verify element created*****************************************************//

//Wait to be sure Edge correctly create the new host group
WebUI.waitForPageLoad(3)

CustomKeywords.'custom.creationVerified.verifyObjectCreated'('searchHg',
	config.getValue('TimeIndicator', 1) + 'specific_date_hg')

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
