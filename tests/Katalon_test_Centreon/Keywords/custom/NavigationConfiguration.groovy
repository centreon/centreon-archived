package custom

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import org.junit.After

import com.kms.katalon.core.annotation.Keyword
import com.kms.katalon.core.checkpoint.Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords
import com.kms.katalon.core.model.FailureHandling
import com.kms.katalon.core.testcase.TestCase
import com.kms.katalon.core.testcase.TestCaseFactory
import com.kms.katalon.core.testdata.TestData
import com.kms.katalon.core.testdata.TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository
import com.kms.katalon.core.testobject.TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords

import internal.GlobalVariable

import MobileBuiltInKeywords as Mobile
import WSBuiltInKeywords as WS
import WebUiBuiltInKeywords as WebUI

public class NavigationConfiguration {
	@Keyword
	public void accessHosts(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Hosts'))

		WebUI.click(findTestObject('Old menu/Configuration/Hosts/a_Host'))
	}

	@Keyword
	public void accessHostGroups(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)
		
		WebUI.click(findTestObject('Old menu/Configuration/a_Hosts'))

		WebUI.click(findTestObject('Old menu/Configuration/Hosts/a_Host groups'))
	}

	@Keyword
	public void accessHostTemplates(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Hosts'))

		WebUI.click(findTestObject('Old menu/Configuration/Hosts/a_Host templates'))
	}

	@Keyword
	public void accessServices(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Services'))

		WebUI.click(findTestObject('Old menu/Configuration/Services/a_Services by host'))
	}

	@Keyword
	public void accessServiceGroups(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Services'))

		WebUI.click(findTestObject('Old menu/Configuration/Services/a_Service Groups'))
	}

	@Keyword
	public void accessMetaServices(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Services'))

		WebUI.click(findTestObject('Old menu/Configuration/Services/a_Meta Services'))
	}

	@Keyword
	public void accessUsers(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Users'))
	}

	@Keyword
	public void accessPluginPacks(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Plugin Packs'))
	}

	@Keyword
	public void accessPollers(){
		WebUI.click(findTestObject('Old menu/Configuration/a_Configuration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Configuration/a_Pollers'))
	}
}
