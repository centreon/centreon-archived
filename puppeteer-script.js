const { baseUrl } = require('./lighthouserc');

module.exports = async (browser) => {
  // launch browser for LHCI
  const page = await browser.newPage();
  await page.goto(baseUrl);
  await page.waitForSelector('input[aria-label="Alias"]');
  await page.type('input[aria-label="Alias"]', 'admin');
  await page.type('input[aria-label="Password"]', 'Centreon!2021');
  await page.click('[type="submit"]');
  await page.waitForNavigation();
  // close session for next run
  await page.close();
};
