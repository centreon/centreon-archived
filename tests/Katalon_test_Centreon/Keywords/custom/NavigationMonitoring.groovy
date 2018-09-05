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

public class NavigationMonitoring {
	@Keyword
	public void accessStatusDetailsServices(){
		WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Monitoring/a_Status Details'))

		WebUI.click(findTestObject('Old menu/Monitoring/a_Services'))
	}

	@Keyword
	public void accessStatusDetailsHosts(){
		WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Monitoring/a_Status Details'))

		WebUI.click(findTestObject('Old menu/Monitoring/a_Hosts'))
	}

	@Keyword
	public void accessGraphs(){
		WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Monitoring/a_Performances'))
	}

	@Keyword
	public void accessMetrics(){
		WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Monitoring/a_Performances'))

		WebUI.click(findTestObject('Old menu/Monitoring/a_Metrics'))
	}

	@Keyword
	public void accessDowntimes(){
		WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Monitoring/a_Downtimes'))
	}

	@Keyword
	public void accessRecurrentDowntimes(){
		WebUI.click(findTestObject('Old menu/Monitoring/a_Monitoring'))

		WebUI.delay(1)

		WebUI.click(findTestObject('Old menu/Monitoring/a_Downtimes'))

		WebUI.click(findTestObject('Old menu/Monitoring/a_Recurrent downtimes'))
	}
}
