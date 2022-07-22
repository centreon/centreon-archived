const cypress = require('cypress');

cypress.cli
  .parseRunArguments(process.argv.slice(2))
  .then((runOptions) => {
    cypress
      .run(runOptions)
      .then((results) => {
        if (results.totalFailed > 0) {
          process.exit(results.totalFailed);
        }
      })
      .catch(() => {
        process.exit(1);
      });
  })
  .catch(() => {
    process.exit(1);
  });
