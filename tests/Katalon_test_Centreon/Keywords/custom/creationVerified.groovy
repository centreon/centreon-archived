package custom

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import com.kms.katalon.core.annotation.Keyword
import com.kms.katalon.core.checkpoint.Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling
import com.kms.katalon.core.testcase.TestCase
import com.kms.katalon.core.testdata.TestData
import com.kms.katalon.core.testobject.TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI

import internal.GlobalVariable

public class creationVerified {
	@Keyword
	public void verifyObjectCreated(searchName, objectName, isType = true){
		def searchField = WebUI.modifyObjectProperty(findTestObject('General/input_Search'), 'name', 'equals', searchName, true)
		
		if(!isType) { searchField = WebUI.removeObjectProperty(searchField, 'type') }
		
		WebUI.setText(searchField, objectName)
		
		WebUI.click(findTestObject('General/button_Search'))
		
		def element = WebUI.modifyObjectProperty(findTestObject('General/a'), 'text', 'equals', objectName, true)
		
		WebUI.verifyElementPresent(element, 3)
	}
}
