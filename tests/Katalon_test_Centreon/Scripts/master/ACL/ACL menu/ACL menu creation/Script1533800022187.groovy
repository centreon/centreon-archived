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
import org.eclipse.persistence.internal.jpa.parsing.jpql.antlr.JPQLParser.selectClause_scope
import org.junit.After
import org.openqa.selenium.Keys as Keys

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//**********************************************************go to ACL menu*********************************************************//

WebUI.click(findTestObject('Administration/button_Administration'))

WebUI.mouseOver(findTestObject('Administration/ACL/span_ACL'))

WebUI.click(findTestObject('Administration/ACL/ACL menu/p_Menus Access'))

WebUI.delay(1)

//******************************************************create a new ACL menu******************************************************//

WebUI.click(findTestObject('General/a_Add'))

def file = TestDataFactory.findTestData('ACL menu config/ACL menu')
//This file only contains the name and the alias of the ACL menu

WebUI.setText(findTestObject('Administration/ACL/ACL menu/ACL menu creation/input_acl_topo_name'),
	config.getValue('TimeIndicator', 1) + file.getValue('ACLMenuName', 1))

WebUI.setText(findTestObject('Administration/ACL/ACL menu/ACL menu creation/input_acl_topo_alias'),
	file.getValue('ACLMenuAlias', 1))

//This file is used to get the name of the ACL group previously created
def file2 = TestDataFactory.findTestData('ACL group')

//This select the ACL group then we add it to our ACL menu
WebUI.selectOptionByLabel(findTestObject('Administration/ACL/ACL menu/ACL menu creation/select_ACL_GroupALLKatalon_acl'), 
	config.getValue('TimeIndicator', 1) + file2.getValue('ACLGroupName', 1), true)

WebUI.click(findTestObject('General/input_add'))

WebUI.click(findTestObject('General/input_submitA'))

//Wait to be sure Edge correctly create the new ACL menu
WebUI.waitForPageLoad(3)

WebUI.click(findTestObject('General/button_User profile'))

//This is to avoid Chrome's failure
WebUI.delay(1)

WebUI.click(findTestObject('General/span_Sign out'))

WebUI.closeBrowser()

//Now that the ACL menu is created we will call the other scripts that will grant and revoke access to web pages.
//The script is too long to be contained in a single Test Case

def ACLMenuName = config.getValue('TimeIndicator', 1) + file.getValue('ACLMenuName', 1)

//*****************************************************************Home****************************************************************//

WebUI.callTestCase(findTestCase('master/ACL/ACL menu/ACL menu Home configuration'), ['ACLMenuName' : ACLMenuName])

//**************************************************************Monitoring**************************************************************//

WebUI.callTestCase(findTestCase('master/ACL/ACL menu/ACL menu Monitoring configuration'), ['ACLMenuName' : ACLMenuName])

//**************************************************************Reporting***************************************************************//

WebUI.callTestCase(findTestCase('master/ACL/ACL menu/ACL menu Reporting configuration'), ['ACLMenuName' : ACLMenuName])

//************************************************************Configuration*************************************************************//

WebUI.callTestCase(findTestCase('master/ACL/ACL menu/ACL menu Configuration configuration'), ['ACLMenuName' : ACLMenuName])
	
//************************************************************Administration************************************************************//

WebUI.callTestCase(findTestCase('master/ACL/ACL menu/ACL menu Administration configuration'), ['ACLMenuName' : ACLMenuName])
