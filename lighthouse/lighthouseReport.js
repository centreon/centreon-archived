import fs from 'fs';

import puppeteer from 'puppeteer';
import lighthouse from 'lighthouse';

const baseUrl = 'http://localhost:4000/centreon/';

const captureReport = async () => {
  const browser = await puppeteer.launch({
    headless: false,
  });
  const page = await browser.newPage();

  const flow = await lighthouse.startFlow(page, {
    headless: true,
    name: 'Visit login page with cold navigation',
  });
  await flow.navigate(`${baseUrl}login`);

  await browser.close();

  const report = flow.generateReport();
  fs.writeFileSync('.lighthouseci/lighthouseci-index.html', report);
};

captureReport();
