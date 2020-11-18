import * as React from 'react';

const setUser = jest.fn();

const setActionAcl = jest.fn();

const useUser = jest.fn(() => ({
  user: 'admin',
  setUser,
}));

const useAcl = jest.fn(() => ({
  actionAcl: {},
  setActionAcl,
}));

const useUserContext = jest.fn(() => ({
  username: 'admin',
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
}));

const Context = {
  Provider: () => <></>,
};

export { useUserContext, useUser, useAcl, Context };
