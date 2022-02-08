import fs from 'fs';

import puppeteer from 'puppeteer';
import { startFlow } from 'lighthouse/lighthouse-core/fraggle-rock/api.js';

const baseUrl = 'http://localhost:4000/centreon/';

const createReportFile = (report) => {
  const lighthouseFolderExists = fs.existsSync('report');

  if (!lighthouseFolderExists) {
    fs.mkdirSync('report');
  }

  fs.writeFileSync('report/lighthouseci-index.html', report);
};

const captureReport = async () => {
  const browser = await puppeteer.launch({
    headless: true,
  });
  const page = await browser.newPage();

  const flow = await startFlow(page, {
    name: 'Visit login page with cold navigation',
  });
  await flow.navigate(`${baseUrl}login`);

  await browser.close();

  createReportFile(flow.generateReport());
};

captureReport();
