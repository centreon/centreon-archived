/* eslint-disable consistent-return */
/* eslint-disable no-unused-vars */
/* eslint-disable import/extensions */
/* eslint-disable @typescript-eslint/explicit-function-return-type */

import '../../../../node_modules/systemjs/dist/s.js'; // IIFE format so it's imported on window
import '../../../../node_modules/systemjs/dist/extras/use-default.js'; // avoid to check module.default.default
import './extras/global.js'; // fork global.js from systemjs to embed patch for IE (https://github.com/systemjs/systemjs/pull/2035)
import systemCss from 'systemjs-plugin-css'; // used to import css in <head>

const importModule = ({ basename, file }) => {
  return new Promise(async (resolve, reject) => {
    try {
      const fileVector = `$centreonExternalModule$${file
        .replace(/(^\.?\/)|(\.js)/g, '')
        .replace(/\//g, '$')}`;
      // Check if current chunk is not imported
      if (typeof window[fileVector] !== 'object') {
        const module = await window.System.import(basename + file);
        window[fileVector] = module;
      }
      // If chunk is correctly imported, we return his chunk vector object
      resolve(window[fileVector]);
    } catch (error) {
      // When something does not going welll, we reject the error
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
export function dynamicImport(basename, parameters) {
  return new Promise(async (resolve, _reject) => {
    const { js: { commons, chunks, bundle }, css } = parameters;
    if (!bundle) {
      console.error(new Error('dynamic import should contains js parameter.'));
      return null;
    }

    try {
      // dynamically import css if external component needs one
      if (css && css.length > 0) {
        await systemCss.fetch({ address: basename + css });
      }

      // We must import commons and vendor chunks before modules chunk
      // parameters.js is an array that contains in the following order:
      // ['/path/to/commons', '/path/to/vendor', '/path/to/module']
      await importModules({
        basename,
        files: commons,
      });

      // Import specific bundle chunks
      await importModules({
        basename,
        files: chunks,
      });

      // We must import separately module object which is a specific module
      // that contains our React component
      const moduleObject = await importModule({ basename, file: bundle });

      return resolve(moduleObject);
    } catch (error) {
      console.error(error);
    }
  });
}

export default dynamicImport;
