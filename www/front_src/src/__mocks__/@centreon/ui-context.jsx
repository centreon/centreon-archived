import * as React from 'react';

const setUser = jest.fn();

const setActionAcl = jest.fn();

const setDowntime = jest.fn();

const setAcknowledgement = jest.fn();

const setRefreshInterval = jest.fn();

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

const useAcknowledgement = jest.fn(() => ({
  acknowledgement: {},
  setAcknowledgement,
}));

const useRefreshInterval = jest.fn(() => ({
  refreshInterval: 0,
  setRefreshInterval,
}));

const useUserContext = jest.fn(() => ({
  acknowledgement: {
    force_active_checks: false,
    notify: false,
    persistent: true,
    sticky: false,
    with_services: false,
  },
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
  downtime: {
    duration: 7200,
    fixed: true,
    with_services: false,
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
  useAcknowledgement,
  Context,
};
