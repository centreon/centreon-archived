/* eslint-disable consistent-return */
/* eslint-disable no-unused-vars */
/* eslint-disable import/extensions */

import 'systemjs/dist/s'; // IIFE format so it's imported on window
import 'systemjs/dist/extras/use-default'; // avoid to check module.default.default
import './extras/global.js'; // fork global.js from systemjs to embed patch for IE (https://github.com/systemjs/systemjs/pull/2035)
import systemCss from 'systemjs-plugin-css'; // used to import css in <head>

interface Parameters {
  js: string;
}

// this function allows to import dynamically js and css using systemjs
// it is compatible with IE, Edge, firefox and chrome
export function dynamicImport(
  basename: string,
  parameters: Parameters,
): Promise {
  return new Promise(async (resolve: Function, _reject: Function) => {
    if (!parameters.js) {
      console.error(new Error('dynamic import should contains js parameter.'));
      return null;
    }

    try {
      // dynamically import css if external component needs one
      if (parameters.css) {
        await systemCss.fetch({ address: basename + parameters.css });
      }

      // check external component in memory to avoid to reimport it
      const vector = `$centreonExternalModule$${parameters.js
        .replace(/(^\.?\/)|(\.js)/g, '')
        .replace(/\//g, '$')}`;
      if (typeof window[vector] === 'object') {
        return resolve(window[vector]);
      }
      const module = await window.System.import(basename + parameters.js);
      window[vector] = module;
      return resolve(window[vector]);
    } catch (error) {
      console.error(error);
    }
  });
}

export default dynamicImport;
