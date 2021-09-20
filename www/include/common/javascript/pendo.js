// Pendo.io
const initPendo = (data) => {
  (function(apiKey, platformData){
  (function(p,e,n,d,o){var v,w,x,y,z;o=p[d]=p[d]||{};o._q=o._q||[];
  v=['initialize','identify','updateOptions','pageLoad','track'];for(w=0,x=v.length;w<x;++w)(function(m){
    o[m]=o[m]||function(){o._q[m===v[0]?'unshift':'push']([m].concat([].slice.call(arguments,0)));};})(v[w]);
    y=e.createElement(n);y.async=!0;y.src='https://cdn.eu.pendo.io/agent/static/'+apiKey+'/pendo.js';
    z=e.getElementsByTagName(n)[0];z.parentNode.insertBefore(y,z);})(window,document,'script','pendo');

    // Call this whenever information about your visitors becomes available
    // Please use Strings, Numbers, or Bools for value types.
    pendo.initialize(platformData);
  })('b06b875d-4a10-4365-7edf-8efeaf53dfdd', data);
};

if (window.fetch) {
  let shouldGetCeipInfo = false;

  if (localStorage.getItem('centreonPlatformData') === null) {
    shouldGetCeipInfo = true;
  } else {
    try {
      let centreonPlatformData = JSON.parse(localStorage.getItem('centreonPlatformData'));
      if ((centreonPlatformData.cacheGenerationDate + (24 * 60 * 60 * 1000)) < Date.now()) {
        shouldGetCeipInfo = true;
      } else if (centreonPlatformData.ceip === true) {
        initPendo(centreonPlatformData);
      }
    } catch (e) {
      shouldGetCeipInfo = true;
    }
  }

  if (shouldGetCeipInfo) {
    fetch(
      './api/internal.php?object=centreon_ceip&action=ceipInfo',
      { method: 'GET' }
    ).then((response) => {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.indexOf("application/json") !== -1) {
        response.json().then(function(data) {
          if (data.ceip === true) {
            initPendo(data);

            // Create localStorage cache
            const platformData = {
              cacheGenerationDate: Date.now(),
              visitor: data.visitor,
              account: data.account,
              excludeAllText: data.excludeAllText,
              ceip: true
            };
            localStorage.setItem('centreonPlatformData', JSON.stringify(platformData));
          } else {
            localStorage.setItem('centreonPlatformData', JSON.stringify({ ceip: false }));
          }
        });
      }
    });
  }
}