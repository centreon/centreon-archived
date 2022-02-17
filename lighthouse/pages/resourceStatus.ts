import { baseUrl } from '../defaults';

export const generateReportForResourceStatusPage = async ({
  flow,
  page,
}): Promise<void> => {
  await page.setCacheEnabled(false);

  await flow.navigate(`${baseUrl}monitoring/resources`, {
    stepName: 'Resource Status Cold navigation',
  });

  await page.setCacheEnabled(true);

  await flow.navigate(`${baseUrl}monitoring/resources`, {
    stepName: 'Resource Status Warm navigation',
  });

  await flow.snapshot({ stepName: 'Resource Status Snapshot' });

  await page.waitForSelector('input[placeholder="Search"]');

  await flow.startTimespan({ stepName: 'Type search query' });
  await page.type('input[placeholder="Search"]', 'Centreon');
  await flow.endTimespan();
};
