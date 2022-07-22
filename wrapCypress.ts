const cypress = require('cypress');

cypress.cli.parseRunArguments(process.argv.slice(2)).then((res) => {
  cypress
    .run(res)
    .then((testRes) => {
      return process.exit(testRes.totalFailed);
    })
    .catch((err) => {
      return process.exit(1);
    });
});
