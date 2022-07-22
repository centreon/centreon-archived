const cypress = require('cypress');

// cypress.cli.parseRunArguments(process.argv.slice(2)).then((res) => {
//   cypress
//     .run(res)
//     .then((testRes) => {
//       return process.exit(testRes.totalFailed);
//     })
//     .catch((_) => {
//       return process.exit(1);
//     });
// });

cypress.cli
  .parseRunArguments(process.argv.slice(2))
  .then((runOptions) => {
    cypress
      .run(runOptions)
      .then(async (results) => {
        if (results.totalFailed > 0) {
          process.exit(results.totalFailed);
        }
      })
      .catch((error) => {
        process.exit(1);
      });
  })
  .catch((err) => {
    process.exit(1);
  });
