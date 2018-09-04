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

//********************************************************go to ACL resource*******************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

WebUI.click(findTestObject('Old menu/Administration/a_Resources Access'))

WebUI.waitForPageLoad(3)

//****************************************************create a new ACL resource****************************************************//

WebUI.click(findTestObject('General/a_Add'))

//The Data File 'ACL resource' stores the name and description of the ALC resource
def file = TestDataFactory.findTestData('ACL resource')

WebUI.setText(findTestObject('Administration/ACL/ACL resource/input_acl_res_name'),
	config.getValue('TimeIndicator', 1) + file.getValue('ACLResourceName', 1))

WebUI.setText(findTestObject('Administration/ACL/ACL resource/input_acl_res_alias'),
	file.getValue('ACLResourceDescription', 1))

//The Data File 'ACL group' is used to get the name of the ACL group
file = TestDataFactory.findTestData('ACL group')

//This links a ACL group to the ACL resource
def element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL resource/option_aclg'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('ACLGroupName', 1), true)

WebUI.click(element)

WebUI.click(findTestObject('Administration/ACL/ACL resource/add_acl_group'))

WebUI.click(findTestObject('Administration/ACL/ACL resource/a_Hosts Resources'))

//The Data File 'Host config' is used to get the name of the host
file = TestDataFactory.findTestData('Host data')

//This links a host to the ACL resource
element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL resource/option_hosts'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('hostName', 1) + '1', true)

WebUI.click(element)

WebUI.click(findTestObject('Administration/ACL/ACL resource/add_host'))

WebUI.scrollToElement(element, 3)

//This excludes a host from the ACL resource
element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL resource/option_exclude_host'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('hostName', 1) + '1_1', true)

WebUI.click(element)

WebUI.click(findTestObject('Administration/ACL/ACL resource/add_exclude_host'))

//The Data File 'Host group' is used to get the name of the host group
file = TestDataFactory.findTestData('Host group')

//This links a host group to the ACL resource
element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL resource/option_hg'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('hostGroupName', 1), true)

WebUI.click(element)

WebUI.click(findTestObject('Administration/ACL/ACL resource/add_host_group'))

WebUI.click(findTestObject('Administration/ACL/ACL resource/a_Services Resources'))

//The Data File 'Service group' is used to get the name of the service group
file = TestDataFactory.findTestData('Service group')

//This links a service group to the ACL resource
element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL resource/option_sg'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('sgName', 1), true)

WebUI.click(element)

WebUI.click(findTestObject('Administration/ACL/ACL resource/add_sg'))

WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('Administration/ACL/ACL resource/a_Meta Services'))

//The Data File 'Meta service' is used to get the name of the meta service
file = TestDataFactory.findTestData('Meta service')

//This links a meta service to the ACL resource
element = WebUI.modifyObjectProperty(findTestObject('Administration/ACL/ACL resource/option_metaService'),
	'text', 'equals', config.getValue('TimeIndicator', 1) + file.getValue('metaServiceName', 1), true)

WebUI.click(element)

WebUI.click(findTestObject('Administration/ACL/ACL resource/add_metaservice'))

WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL resource
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
