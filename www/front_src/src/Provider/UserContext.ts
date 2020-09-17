import * as React from 'react';
import { UserContext } from './models';

const defaultUser = {
  username: '',
  locale: navigator.language,
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
};

const defaultAcl = {
  actions: {
    host: {
      check: false,
      acknowledgement: false,
      disacknowledgement: false,
      downtime: false,
    },
    service: {
      check: false,
      acknowledgement: false,
      disacknowledgement: false,
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
