import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import javax.xml.datatype.DatatypeFactory
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
import java.io.IOException;
import org.apache.poi.xssf.usermodel.XSSFCell;
import org.apache.poi.xssf.usermodel.XSSFRow;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import java.lang.String
import org.junit.After

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//********************************************************go to Escalations********************************************************//

CustomKeywords.'custom.NavigationConfiguration.accessEscalations'()

//*****************************************************create a new escalation*****************************************************//

//This is to avoid Chrome's failure
WebUI.delay(1)

//This escFile contains all the information about the new user
def escFile = TestDataFactory.findTestData('Escalations')

for(def line : (1..escFile.getRowNumbers()))
{
	WebUI.click(findTestObject('General/a_Add'))
	
	WebUI.setText(findTestObject('Configuration/Escalations/input_esc_name'),
		config.getValue('TimeIndicator', 1) + escFile.getValue('escalationName', line))
	
	WebUI.setText(findTestObject('Configuration/Escalations/input_esc_alias'),
		escFile.getValue('escalationAlias', line))
	
	WebUI.setText(findTestObject('Configuration/Escalations/input_first_notif'),
		escFile.getValue('firstNotif', line))
	
	WebUI.setText(findTestObject('Configuration/Escalations/input_last_notif'),
		escFile.getValue('lastNotif', line))
	
	WebUI.setText(findTestObject('Configuration/Escalations/input_notif_interval'),
		escFile.getValue('notifInterval', line))
	
	//This contains the list of service escalation options
	def array = escFile.getValue('serviceEscOptions', line).split(" ")
		
	//This goes through the list of service escalation options
	for (def index : (0..array.length - 1)) {
		def escOption = WebUI.modifyObjectProperty(findTestObject('Configuration/Escalations/input_service_esc_option'),
			'name', 'equals', 'escalation_options2[' + array[index] + ']', true)

		WebUI.click(escOption)
	}

	WebUI.setText(findTestObject('Configuration/Escalations/input_cg'), 
		config.getValue('TimeIndicator', 1) + escFile.getValue('contactGroup', line))

	def element = WebUI.modifyObjectProperty(findTestObject('General/div'), 'text', 'equals',
		config.getValue('TimeIndicator', 1) + escFile.getValue('contactGroup', line), true)

	WebUI.click(element)
	
	WebUI.click(findTestObject('General/input_submitA'))
	
	//Wait to be sure Edge correctly create the new user
	WebUI.delay(1)
}

CustomKeywords.'custom.creationVerified.verifyObjectCreated'('searchE',
	config.getValue('TimeIndicator', 1) + escFile.getValue('escalationName', 1))

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()

