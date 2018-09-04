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

//*****************************************************go to meta service page*****************************************************//

WebUI.click(findTestObject('Configuration/button_Configuration'))

WebUI.mouseOver(findTestObject('Configuration/Services/span_Services'))

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/p_Meta Services'))

WebUI.waitForPageLoad(3)

//******************************************************create a meta service******************************************************//

WebUI.click(findTestObject('General/a_Add'))

def file = TestDataFactory.findTestData('Meta service')

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_meta_name'),
	config.getValue('TimeIndicator', 1) + file.getValue('metaServiceName', 1))

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_meta_display'),
	file.getValue('metaServiceOutput', 1))

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_warning'),
	file.getValue('warningLevel', 1))

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_critical'),
	file.getValue('criticalLevel', 1))

WebUI.selectOptionByValue(findTestObject('Configuration/Services/Meta Service creation/select_buffercachedcpu0load1lo'), 
    file.getValue('metric', 1), true)

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/span_Check Period'))

def element = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Meta Service creation/div_24x7'),
	'title', 'equals', file.getValue('checkPeriod', 1), true)

WebUI.click(element)

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_max_check_attempts'),
	file.getValue('maxCheckAttempts', 1))

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_normal_check_interval'),
	file.getValue('normalCheckInterval', 1))

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_retry_check_interval'),
	file.getValue('retryCheckInterval', 1))

WebUI.scrollToElement(findTestObject('Configuration/Services/Meta Service creation/input_retry_check_interval'), 2)

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/input_notifications_enablednot'))

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/input_linked_cg'))

element = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Meta Service creation/div_Supervisors'),
	'title', 'equals', file.getValue('contactGroup', 1), true)

WebUI.click(element)

WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_notification_interval'),
	file.getValue('notificationInterval', 1))

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/span_Notification Period'))

element = WebUI.modifyObjectProperty(findTestObject('Configuration/Services/Meta Service creation/div_24x7'),
	'title', 'equals', file.getValue('notificationPeriod', 1), true)

WebUI.click(element)

def array = file.getValue('notificationType', 1).split(' ')

for(def index : (0..array.length - 1))
{
	element = WebUI.modifyObjectProperty(
		findTestObject('Configuration/Services/Meta Service creation/input_ms_notifOptsw'),
		'name', 'equals', 'ms_notifOpts[' + array[index] + ']', true)
	
	WebUI.click(element)
}

WebUI.click(findTestObject('General/input_submitA'))

//***************************************************Add a meta service indicator**************************************************//

//This links the meta service to hosts
WebUI.setText(findTestObject('Configuration/Services/Meta Service creation/input_Meta service name'),
	config.getValue('TimeIndicator', 1) + file.getValue('metaServiceName', 1))

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/icon_add host'))

WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/a_Add'))

def hostFile = TestDataFactory.findTestData('Host data')

//This select the host
WebUI.selectOptionByLabel(findTestObject('Configuration/Services/Meta Service creation/select_host'),
	config.getValue('TimeIndicator', 1) + hostFile.getValue('hostName', 1) + '1', false)

WebUI.delay(1)

def sgFile = TestDataFactory.findTestData('Service group')

//This select the service
WebUI.selectOptionByLabel(findTestObject('Configuration/Services/Meta Service creation/select_Service'),
	sgFile.getValue('Service', 1), false)

WebUI.delay(1)

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/select_metric'))

WebUI.click(findTestObject('Configuration/Services/Meta Service creation/option_service metric'))

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly update the meta service
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This it to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()
