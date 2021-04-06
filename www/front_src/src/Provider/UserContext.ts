import * as React from 'react';

import { UserContext } from './models';

const defaultUser = {
  alias: '',
  locale: navigator.language,
  name: '',
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
};

const defaultAcl = {
  actions: {
    host: {
      acknowledgement: false,
      check: false,
      disacknowledgement: false,
      downtime: false,
      submit_status: false,
    },
    service: {
      acknowledgement: false,
      check: false,
      disacknowledgement: false,
      downtime: false,
      submit_status: false,
    },
  },
};

const defaultDowntime = {
  default_duration: 7200,
};

const defaultRefreshInterval = 15;

const defaultContext = {
  ...defaultUser,
  acl: defaultAcl,
  downtime: defaultDowntime,
  refreshInterval: defaultRefreshInterval,
};

const Context = React.createContext<UserContext>(defaultContext);

const useUserContext = (): UserContext => React.useContext(Context);

export default Context;

export { useUserContext, defaultUser, defaultAcl };
