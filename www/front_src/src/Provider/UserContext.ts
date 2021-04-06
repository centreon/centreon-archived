import * as React from 'react';
import { UserContext } from './models';

const defaultUser = {
  locale: navigator.language,
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  username: '',
};

const defaultAcl = {
  actions: {
    host: {
      acknowledgement: false,
      check: false,
      downtime: false,
    },
    service: {
      acknowledgement: false,
      check: false,
      downtime: false,
    },
  },
};

const defaultContext = {
  ...defaultUser,
  acl: defaultAcl,
};

const Context = React.createContext<UserContext>(defaultContext);

const useUserContext = (): UserContext => React.useContext(Context);

export default Context;

export { useUserContext, defaultUser, defaultAcl };
