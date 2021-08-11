/* eslint-disable no-console */
/* eslint-disable no-async-promise-executor */
/* eslint-disable consistent-return */
/* eslint-disable no-unused-vars */
/* eslint-disable import/extensions */
/* eslint-disable @typescript-eslint/explicit-function-return-type */

import '../../../../node_modules/systemjs/dist/s.js'; // IIFE format so it's imported on window
import '../../../../node_modules/systemjs/dist/extras/use-default.js'; // avoid to check module.default.default
import './extras/global.js'; // fork global.js from systemjs to embed patch for IE (https://github.com/systemjs/systemjs/pull/2035)
import systemCss from 'systemjs-plugin-css'; // used to import css in <head>

const getGlobalName = (filename) => {
  const normalizedFilename = filename
    .replace(/(^\.?\/)|(\.js)/g, '')
    .replace(/\//g, '$');

  return `$centreonExternalModule$${normalizedFilename}`;
};

const importModule = ({ basename, file }) => {
  return new Promise(async (resolve, reject) => {
    try {
      const globalName = getGlobalName(file);
      // Check if current chunk is not imported
      if (typeof window[globalName] !== 'object') {
        const module = await window.System.import(basename + file);
        window[globalName] = module;
      }
      // If chunk is correctly imported, we return its chunk vector object
      resolve(window[globalName]);
    } catch (error) {
      // When something does not going well, we reject the error
      reject(error);
    }
  });
};

// This function asynchronously imports a chunk from a path passed as parameter
// Firstly, we check if the chunk is not already imported
// If not, we import it
const importModules = ({ basename, files }) => {
  const promises = files.map((file) => {
    return importModule({ basename, file });
  });

  return Promise.all(promises);
};

// this function allows to import dynamically js and css using systemjs
// it is compatible with IE, Edge, firefox and chrome
export const dynamicImport = (basename, parameters) =>
  new Promise(async (resolve, _reject) => {
    const {
      js: { commons, chunks, bundle },
      css,
    } = parameters;
    if (!bundle) {
      console.error(new Error('dynamic import should contains js parameter.'));

      return null;
    }

    try {
      // dynamically import css if external component needs one
      if (css && css.length > 0) {
        await systemCss.fetch({ address: basename + css });
      }

      // import commons and vendor chunks
      await importModules({
        basename,
        files: commons,
      });

      // import specific bundle chunks
      await importModules({
        basename,
        files: chunks,
      });

      // import bundle itself
      const moduleObject = await importModule({ basename, file: bundle });

      return resolve(moduleObject);
    } catch (error) {
      console.error(error);
    }
  });

export default dynamicImport;
