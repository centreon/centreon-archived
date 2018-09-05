package custom

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

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

public class NavigationAdministration {
	@Keyword
	public void accessMyAccount(){
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_Parameters'))

		WebUI.click(findTestObject('Old menu/Administration/a_My Account'))
	}

	@Keyword
	public void accessModules(){
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_Extensions'))
	}

	@Keyword
	public void accessWidgets(){
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_Extensions'))

		WebUI.click(findTestObject('Old menu/Administration/a_Widgets'))
	}

	@Keyword
	public void accessAccessGroups(){
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

		WebUI.click(findTestObject('Old menu/Administration/a_Access Groups'))
	}

	@Keyword
	public void accessMenusAccess() {
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

		WebUI.click(findTestObject('Old menu/Administration/a_Menus Access'))
	}

	@Keyword
	public void accessResourcesAccess(){
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

		WebUI.click(findTestObject('Old menu/Administration/a_Resources Access'))
	}

	@Keyword
	public void accessActionsAccess(){
		WebUI.click(findTestObject('Old menu/Administration/a_Administration'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Administration/a_ACL'))

		WebUI.click(findTestObject('Old menu/Administration/a_Actions Access'))
	}
}
