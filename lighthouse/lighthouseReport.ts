import fs from 'fs';

import puppeteer from 'puppeteer';
import { startFlow } from 'lighthouse/lighthouse-core/fraggle-rock/api';

import { generateReportForResourceStatusPage } from './pages/resourceStatus';
import { baseConfigContext, baseUrl } from './defaults';

const createReportFile = (report): void => {
  const lighthouseFolderExists = fs.existsSync('report');

  if (!lighthouseFolderExists) {
    fs.mkdirSync('report');
  }

  fs.writeFileSync('report/lighthouseci-index.html', report);
};

const captureReport = async (): Promise<void> => {
  const browser = await puppeteer.launch({
    headless: true,
  });
  const page = await browser.newPage();

  const flow = await startFlow(page, {
    configContext: baseConfigContext,
    name: 'Centreon Web pages',
  });

  await page.goto(baseUrl);

  await page.waitForSelector('input[name="useralias"]');

  await page.type('input[name="useralias"]', 'admin');
  await page.type('input[name="password"]', 'centreon');
  await page.click('input[type="submit"]');

  await generateReportForResourceStatusPage({ flow, page });

  await browser.close();

  createReportFile(flow.generateReport());
};

captureReport();
