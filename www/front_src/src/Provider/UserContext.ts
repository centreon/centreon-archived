import * as React from 'react';
import { UserContext } from './models';

const defaultUser = {
  alias: '',
  name: '',
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
      submit_status: false,
    },
    service: {
      check: false,
      acknowledgement: false,
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
