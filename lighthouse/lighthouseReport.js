import fs from 'fs';

import puppeteer from 'puppeteer';
import { startFlow } from 'lighthouse/lighthouse-core/fraggle-rock/api.js';

const baseUrl = 'http://localhost:4000/centreon/';

const screenEmulation = {
  deviceScaleFactor: 1,
  disabled: false,
  height: 720,
  mobile: false,
  width: 1280,
};

const baseConfigContext = {
  settingsOverrides: {
    formFactor: 'desktop',
    screenEmulation,
  },
};

const createReportFile = (report) => {
  const lighthouseFolderExists = fs.existsSync('report');

  if (!lighthouseFolderExists) {
    fs.mkdirSync('report');
  }

  fs.writeFileSync('report/lighthouseci-index.html', report);
};

const generateReportForLoginPage = async ({ flow, page }) => {
  await flow.navigate(`${baseUrl}login`, { stepName: 'Login Cold navigation' });

  await flow.navigate(`${baseUrl}login`, {
    stepName: 'Login Warm navigation',
  });

  await flow.snapshot({ stepName: 'Login Snapshot' });

  await page.waitForSelector('input[aria-label="Alias"]');

  await flow.startTimespan({ stepName: 'Type login credentials' });
  await page.type('input[aria-label="Alias"]', 'admin');
  await page.type('input[aria-label="Password"]', 'Centreon!2021');
  await page.click('button[aria-label="Connect"]');
  await flow.endTimespan();
};

const generateReportForResourceStatusPage = async ({ flow, page }) => {
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

const captureReport = async () => {
  const browser = await puppeteer.launch({
    headless: true,
  });
  const page = await browser.newPage();

  const flow = await startFlow(page, {
    configContext: baseConfigContext,
    name: 'Centreon Web pages',
  });

  await generateReportForLoginPage({ flow, page });

  await generateReportForResourceStatusPage({ flow, page });

  await browser.close();

  createReportFile(flow.generateReport());
};

captureReport();
