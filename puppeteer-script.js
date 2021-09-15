module.exports = async (browser) => {
  // launch browser for LHCI
  const page = await browser.newPage();
  await page.goto('http://localhost:4000/');
  await page.type('[name="useralias"]', 'admin');
  await page.type('[name="password"]', 'centreon');
  await page.click('[type="submit"]');
  await page.waitForNavigation();
  // close session for next run
  await page.close();
};
