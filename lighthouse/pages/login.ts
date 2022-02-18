import { baseUrl } from '../defaults';

export const generateReportForLoginPage = async ({
  flow,
  page,
}): Promise<void> => {
  await flow.navigate(`${baseUrl}login`, { stepName: 'Login Cold navigation' });

  await flow.navigate(`${baseUrl}login`, {
    stepName: 'Login Warm navigation',
  });

  await flow.snapshot({ stepName: 'Login Snapshot' });

  await page.waitForSelector('input[aria-label="Alias"]');

  await flow.startTimespan({ stepName: 'Type alias' });
  await page.type('input[aria-label="Alias"]', 'admin');
  await flow.endTimespan();

  await flow.startTimespan({ stepName: 'Type password' });
  await page.type('input[aria-label="Password"]', 'Centreon!2021');
  await flow.endTimespan();

  await flow.startTimespan({ stepName: 'Click submit button' });
  await page.click('button[aria-label="Connect"]');
  await flow.endTimespan();
};
