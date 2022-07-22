const cypress = require('cypress');

cypress.cli.parseRunArguments(process.argv.slice(2)).then((res) => {
  cypress
    .run(res)
    .then((testRes) => {
      process.exit(testRes.totalFailed);
    })
    .catch((err) => {
      process.exit(1);
    });
});
