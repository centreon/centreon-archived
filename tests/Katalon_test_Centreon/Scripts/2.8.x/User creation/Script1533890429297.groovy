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

//*******************************************************go to Contacts/Users******************************************************//

WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

WebUI.delay(1)

WebUI.click(findTestObject('Old menu/Configuration/a_Users'))

//********************************************************create a new user********************************************************//

//This is to avoid Chrome's failure
WebUI.delay(1)

//This userFile contains all the information about the new user
def userFile = TestDataFactory.findTestData('User config')

for(def line : (1..userFile.getRowNumbers()))
{
	for(def userNumber : (1..userFile.getValue('Number', line).toInteger()))
	{
		WebUI.click(findTestObject('General/a_Add'))
		
		WebUI.setText(findTestObject('Configuration/User creation/input_contact_alias'),
			config.getValue('TimeIndicator', 1) + userFile.getValue('UserName', line) + userNumber)
		
		WebUI.setText(findTestObject('Configuration/User creation/input_contact_name'),
			userFile.getValue('UserAlias', line) + userNumber)
		
		WebUI.setText(findTestObject('Configuration/User creation/input_contact_email'), userFile.getValue('UserAddress', 1))
		
		//This contains the list of host notification options
		def array = userFile.getValue('HostNotifStatus', line).split(" ")
		
		//This goes through the list of host notification options and select them
		for (def index : (0..array.length - 1)) {
			def hostTemplate = WebUI.modifyObjectProperty(findTestObject('Configuration/User creation/input_contact_hostNotifOptsd'),
				'id', 'equals', 'h' + array[index], true)
			
			WebUI.click(hostTemplate)
		}
		
		//This select the host notification period
		WebUI.click(findTestObject('Configuration/User creation/span_Host Notif Peri'))
		
		WebUI.scrollToElement(findTestObject('Configuration/User creation/span_Host Notif Peri'), 3)
		
		def hostNotif = WebUI.modifyObjectProperty(findTestObject('Configuration/User creation/div_24x7'),
				'title', 'equals', userFile.getValue('HostNotifPeriod', line), true)
		
		WebUI.click(hostNotif)
		
		//This select the host notification command
		WebUI.click(findTestObject('Configuration/User creation/input_Host Notif Comm'))
		
		hostNotif = WebUI.modifyObjectProperty(findTestObject('Configuration/User creation/div_host-notify-by-email'),
			'title', 'equals', userFile.getValue('HostNotifComm', 1), true)
		
		WebUI.click(hostNotif)
		
		//This contains the list of service notification options
		array = userFile.getValue('ServNotifStatus', line).split(" ")
		
		//This goes through the list of service notification status and select them
		for (def index : (0..array.length - 1)) {
			def serviceTemplate = WebUI.modifyObjectProperty(
				findTestObject('Configuration/User creation/input_contact_svNotifOptsw'),
				'id', 'equals', 's' + array[index], true)
			
			WebUI.click(serviceTemplate)
		}
		
		//This select the service notification period
		WebUI.click(findTestObject('Configuration/User creation/span_Service Notification Peri'))
		
		def serviceNotif = WebUI.modifyObjectProperty(findTestObject('Configuration/User creation/div_24x7'),
			'title', 'equals', userFile.getValue('ServNotifPeriod', line), true)
		
		WebUI.click(serviceNotif)
		
		//This select the service notification command
		WebUI.click(findTestObject('Configuration/User creation/input_Serv Notif Comm'))
		
		serviceNotif = WebUI.modifyObjectProperty(findTestObject('Configuration/User creation/div_service-notify-by-email'),
			'title', 'equals', userFile.getValue('ServNotifComm', line), true)
		
		WebUI.click(serviceNotif)
		
		//This goes to the Centreon Authentication tab
		WebUI.click(findTestObject('Configuration/User creation/a_Centreon Authentication'))
		
		WebUI.setText(findTestObject('Configuration/User creation/input_contact_pwd'), userFile.getValue('password', 1))
		
		WebUI.setText(findTestObject('Configuration/User creation/input_contact_pwd2'), userFile.getValue('password', 1))
		
		//This set the language to english
		WebUI.selectOptionByValue(findTestObject('Configuration/User creation/select_Language'), 'en_US', true)
			
		WebUI.click(findTestObject('General/input_submitA'))
		
		//Wait to be sure Edge correctly create the new user
		WebUI.delay(1)
	}
}

def search = WebUI.modifyObjectProperty(findTestObject('General/input_Search'),
	'name', 'equals', 'searchC', true)

WebUI.setText(search, config.getValue('TimeIndicator', 1) + userFile.getValue('UserName', 1) + '1')

WebUI.click(findTestObject('General/button_Search'))

def a = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals',
	config.getValue('TimeIndicator', 1) + userFile.getValue('UserName', 1) + '1', true)

WebUI.click(a)

WebUI.click(findTestObject('Configuration/User creation/a_Centreon Authentication'))

//This generate the autologin key
WebUI.click(findTestObject('Configuration/User creation/input_contact_gen_akey'))
	
//The get the new autologin key
def str = WebUI.getAttribute(findTestObject('Configuration/User creation/input_contact_autologin'), 'value')

def userDir = System.getProperty("user.dir")
	
//The programm will open the excel sheet where the user's information are stored
FileInputStream writeFile = new FileInputStream (new File(userDir + '/Excel file/Premiere_automatisation_classeur.xlsx'))
	
XSSFWorkbook workbook = new XSSFWorkbook(writeFile)
	
XSSFSheet sheet = workbook.getSheet('User config')
	
//This modify the excel sheet to store the autologin key
sheet.getRow(1).getCell(10).setCellValue(str)
	
//This close the excel sheet
writeFile.close()

//This store the new sheet in the excel userFile
FileOutputStream outFile = new FileOutputStream(new File(userDir + "/Excel file/Premiere_automatisation_classeur.xlsx"))

workbook.write(outFile)
	
//This close the excel userFile
outFile.close()

WebUI.click(findTestObject('General/input_submitC'))

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
	
//This launch the test which test the autologin
WebUI.callTestCase(findTestCase('2.8.x/Login/Autologin'),
	['userName' : config.getValue('TimeIndicator', 1) + userFile.getValue('UserName', 1) + '1','autologin' : str])


//This launch the test which test the basic login
WebUI.callTestCase(findTestCase('2.8.x/Login/Login'), ['userName' : config.getValue('TimeIndicator', 1)
	+ userFile.getValue('UserName', 1) + '1', 'password' : userFile.getValue('password', 1)])
