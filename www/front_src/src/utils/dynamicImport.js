import System from '../../../../node_modules/systemjs/dist/s.js';

export function dynamicImport(url) {
  return new Promise((resolve, reject) => {

    const vector = "$centreonExternalModule$" + url.replace(/(^\.?\/)|(\.js)/g, '').replace(/\//g, '$');
    if (typeof(window[vector]) === "object") {
      return resolve(window[vector]);
    }

    try {
      window.System.import(url).then(module => {
        window[vector] = module;
        return resolve(window[vector]);
      });
    } catch (error) {
      return reject(error);
    }
  });
}

export default dynamicImport;