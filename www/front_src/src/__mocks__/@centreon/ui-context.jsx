import * as React from 'react';

const setUser = jest.fn();

const setActionAcl = jest.fn();

const setDowntime = jest.fn();

const setRefreshInterval = jest.fn();

const useUser = jest.fn(() => ({
  user: 'admin',
  setUser,
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

const useUserContext = jest.fn(() => ({
  alias: 'admin',
  name: 'admin',
  locale: 'en',
  timezone: 'Europe/Paris',

  acl: {
    actions: {
      service: {
        downtime: true,
        acknowledgement: true,
        disacknowledgement: true,
        check: true,
        submit_status: true,
      },
      host: {
        downtime: true,
        acknowledgement: true,
        disacknowledgement: true,
        check: true,
        submit_status: true,
      },
    },
  },

  downtime: {
    default_duration: 7200,
  },
  refresh_interval: 15,
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
  Context,
};
