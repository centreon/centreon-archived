/* eslint-disable */
const initPendo = (data) => {
  (function (apiKey, platformData) {
    (function (p, e, n, d, o) {
      let v;
      let w;
      let x;
      let y;
      let z;
      o = p[d] = p[d] || {};
      o._q = o._q || [];
      v = ['initialize', 'identify', 'updateOptions', 'pageLoad', 'track'];
      for (w = 0, x = v.length; w < x; ++w)
        (function (m) {
          o[m] =
            o[m] ||
            function () {
              o._q[m === v[0] ? 'unshift' : 'push'](
                [m].concat([].slice.call(arguments, 0)),
              );
            };
        })(v[w]);
      y = e.createElement(n);
      y.async = !0;
      y.src = `https://cdn.eu.pendo.io/agent/static/${apiKey}/pendo.js`;
      z = e.getElementsByTagName(n)[0];
      z.parentNode.insertBefore(y, z);
    })(window, document, 'script', 'pendo');

    // Call this whenever information about your visitors becomes available
    // Please use Strings, Numbers, or Bools for value types.
    pendo.initialize(platformData);
  })('b06b875d-4a10-4365-7edf-8efeaf53dfdd', data);
};

export default initPendo;