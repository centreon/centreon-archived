import System from '../../../../node_modules/systemjs/dist/s.js'; // IIFE format so it's imported on window
import systemCss from "systemjs-plugin-css"; // used to import css in <head>

// this function allows to import dynamically js and css using systemjs
// it is compatible with IE, Edge, firefox and chrome
export function dynamicImport(parameters) {
  return new Promise(async (resolve, reject) => {
    if (!parameters.js) {
      return reject(new Error('dynamic import should contains js parameter.'));
    }

    try {
      // dynamically import css if external component needs one
      if (parameters.css) {
        await systemCss.fetch({address: '.' + parameters.css});
      }

      // check external component in memory to avoid to reimport it
      const vector = "$centreonExternalModule$" + parameters.js.replace(/(^\.?\/)|(\.js)/g, '').replace(/\//g, '$');
      if (typeof(window[vector]) === "object") {
        return resolve(window[vector]);
      } else {
        const module = await(window.System.import('.' + parameters.js));
        window[vector] = module;
        return resolve(window[vector]);
      }
    } catch (error) {
      return reject(error);
    }
  });
}

export default dynamicImport;