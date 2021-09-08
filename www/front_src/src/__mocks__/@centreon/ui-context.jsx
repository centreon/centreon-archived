import * as React from 'react';

const setUser = jest.fn();

const setActionAcl = jest.fn();

const setDowntime = jest.fn();

const setRefreshInterval = jest.fn();

const setAreCloudServicesEnabled = jest.fn();
const areCloudServicesEnabled = false;

const useUser = jest.fn(() => ({
  setUser,
  user: 'admin',
}));

const useAcl = jest.fn(() => ({
  actionAcl: {},
  setActionAcl,
}));

const useDowntime = jest.fn(() => ({
  downtime: {},
  setDowntime,
}));

const useRefreshInterval = jest.fn(() => ({
  refreshInterval: 0,
  setRefreshInterval,
}));

const useCloudServices = jest.fn(() => ({
  areCloudServicesEnabled,
  setAreCloudServicesEnabled,
}));

const useUserContext = jest.fn(() => ({
  acl: {
    actions: {
      host: {
        acknowledgement: true,
        check: true,
        disacknowledgement: true,
        downtime: true,
        submit_status: true,
      },
      service: {
        acknowledgement: true,
        check: true,
        disacknowledgement: true,
        downtime: true,
        submit_status: true,
      },
    },
  },
  alias: 'admin',
  cloudServices: {
    areCloudServicesEnabled,
    setAreCloudServicesEnabled,
  },
  downtime: {
    default_duration: 7200,
  },

  locale: 'en',

  name: 'admin',
  refresh_interval: 15,
  timezone: 'Europe/Paris',
}));

const Context = {
  Provider: () => <></>,
};

export {
  useUserContext,
  useUser,
  useAcl,
  useDowntime,
  useRefreshInterval,
  useCloudServices,
  Context,
};
