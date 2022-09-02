export const baseUrl = 'http://localhost:4000/centreon/';

const screenEmulation = {
  deviceScaleFactor: 1,
  disabled: false,
  height: 720,
  mobile: false,
  width: 1280,
};

export const baseConfigContext = {
  settingsOverrides: {
    formFactor: 'desktop',
    screenEmulation,
  },
};
