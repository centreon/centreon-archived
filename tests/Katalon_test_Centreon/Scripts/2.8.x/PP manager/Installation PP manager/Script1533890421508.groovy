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
import java.io.IOException;
import org.apache.poi.xssf.usermodel.XSSFCell;
import org.apache.poi.xssf.usermodel.XSSFRow;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import java.lang.String
import org.junit.After
import groovy.time.TimeCategory

//******************************************************Get the time indicator*****************************************************//

def today

use(TimeCategory, {
	today = new Date()
})

str = today.format('yyyyddMM')

str = str + '_' + today.format('HHmmss')

def userDir = System.getProperty("user.dir")
	
//The programm will open the excel sheet where the user's information are stored
FileInputStream writeFile = new FileInputStream (new File(userDir + '/Excel file/Premiere_automatisation_classeur.xlsx'));
	
XSSFWorkbook workbook = new XSSFWorkbook(writeFile);
	
XSSFSheet sheet = workbook.getSheet('Configuration');
	
//This modify the excel sheet to store the autologin key
sheet.getRow(1).getCell(3).setCellValue(str);

//This close the excel sheet
writeFile.close();

//This store the new sheet in the excel userFile
FileOutputStream outFile = new FileOutputStream(new File(userDir + "/Excel file/Premiere_automatisation_classeur.xlsx"))

workbook.write(outFile);
	
//This close the excel userFile
outFile.close();

//**********************************************************Open a browser*********************************************************//

def config = TestDataFactory.findTestData('Configuration')

WebUI.openBrowser(config.getValue('url', 1))

//**************************************************************Login**************************************************************//

WebUI.setText(findTestObject('General/Login/input_useralias'), config.getValue('login', 1))

WebUI.setText(findTestObject('General/Login/input_password'), config.getValue('password', 1))

WebUI.click(findTestObject('General/Login/input_submitLogin'))

//********************************************************go to modules page*******************************************************//

WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

WebUI.click(findTestObject('Old menu/Administration/a_Extensions'))

WebUI.delay(1)

//*****************************************************Install the PP manager******************************************************//

//This installs the Plugin Pack manager
WebUI.click(findTestObject('Administration/Extensions/Modules/img_PP installation'))

WebUI.delay(1)

WebUI.click(findTestObject('Administration/Extensions/Modules/input_install'))

WebUI.delay(1)

WebUI.click(findTestObject('Administration/Extensions/Modules/input_list'))

WebUI.verifyElementPresent(findTestObject('Administration/Extensions/Modules/img_PP_manager_delete'), 15)

WebUI.click(findTestObject('Old menu/a_Logout'))

WebUI.closeBrowser()
