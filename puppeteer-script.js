const { baseUrl } = require('./lighthouserc');

module.exports = async (browser) => {
  // launch browser for LHCI
  const page = await browser.newPage();
  await page.goto(baseUrl);
  await page.type('[name="useralias"]', 'admin');
  await page.type('[name="password"]', 'Centreon!2021');
  await page.click('[type="submit"]');
  await page.waitForNavigation();
  // close session for next run
  await page.close();
};
