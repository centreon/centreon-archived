import { baseUrl } from '../defaults';

export const generateReportForAuthenticationPage = async ({
  flow,
  page,
}): Promise<void> => {
  await flow.navigate(`${baseUrl}administration/authentication`, { stepName: 'Authentication Cold navigation' });

  await flow.navigate(`${baseUrl}administration/authentication`, {
    stepName: 'Authentication Warm navigation',
  });

  await flow.snapshot({ stepName: 'Authentication Snapshot' });

  await page.waitForSelector('input[aria-label="Password minimum length"]');

  
  await flow.startTimespan({ stepName: 'Change letter case' });
  await page.click('input[aria-label="Force to use lower case"]');
  await flow.endTimespan();
  
  await page.click('input[aria-label="Force to use lower case"]');
  
  await flow.startTimespan({ stepName: 'Change tab' });
  await page.click('button[role="tab"]:nth-child(2)');
  await flow.endTimespan();

  await flow.startTimespan({ stepName: 'Type base URL' });
  await page.type('input[aria-label="Password minimum length"]', 'http://localhost/');
  await flow.endTimespan();
};
