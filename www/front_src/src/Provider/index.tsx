import * as React from 'react';

import 'dayjs/locale/en';
import 'dayjs/locale/pt';
import 'dayjs/locale/fr';
import 'dayjs/locale/es';

import dayjs from 'dayjs';
import timezonePlugin from 'dayjs/plugin/timezone';
import utcPlugin from 'dayjs/plugin/utc';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import { Provider } from 'react-redux';
import { pathEq, toPairs, pipe, reduce, mergeAll } from 'ramda';
import i18n, { Resource, ResourceLanguage } from 'i18next';
import { initReactI18next } from 'react-i18next';

import { useRequest, getData, Loader } from '@centreon/ui';
import {
  Context,
  useUser,
  useAcl,
  useDowntime,
  useRefreshInterval,
} from '@centreon/ui-context';

import App from '../App';
import createStore from '../store';
import {
  parametersEndpoint,
  translationEndpoint,
  aclEndpoint,
} from './endpoint';
import { Parameters, Actions } from './models';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);

const store = createStore();

const AppProvider = (): JSX.Element | null => {
  const { user, setUser } = useUser();
  const { downtime, setDowntime } = useDowntime();
  const { refreshInterval, setRefreshInterval } = useRefreshInterval();
  const { actionAcl, setActionAcl } = useAcl();
  const [dataLoaded, setDataLoaded] = React.useState(false);

  const { sendRequest: getParameters } = useRequest<Parameters>({
    request: getData,
  });
  const { sendRequest: getTranslations } = useRequest<ResourceLanguage>({
    request: getData,
  });
  const { sendRequest: getAcl } = useRequest<Actions>({
    request: getData,
  });

  const initializeI18n = ({ retrievedUser, retrievedTranslations }): void => {
    const locale = (retrievedUser.locale || navigator.language)?.slice(0, 2);

    i18n.use(initReactI18next).init({
      nsSeparator: false,
      keySeparator: false,
      fallbackLng: 'en',
      lng: locale,
      resources: pipe(
        toPairs as (t) => Array<[string, ResourceLanguage]>,
        reduce(
          (acc, [language, values]) =>
            mergeAll([acc, { [language]: { translation: values } }]),
          {},
        ),
      )(retrievedTranslations) as Resource,
    });
  };

  React.useEffect(() => {
    Promise.all([
      getParameters(parametersEndpoint),
      getTranslations(translationEndpoint),
      getAcl(aclEndpoint),
    ])
      .then(([retrievedParameters, retrievedTranslations, retrievedAcl]) => {
        setUser({
          alias: retrievedParameters.user.alias,
          name: retrievedParameters.user.name,
          locale: retrievedParameters.user.locale || 'en',
          timezone: retrievedParameters.user.timezone,
        });
        setDowntime(retrievedParameters.downtime);
        setRefreshInterval(retrievedParameters.refresh_interval);
        setActionAcl(retrievedAcl);

        initializeI18n({
          retrievedUser: retrievedParameters.user,
          retrievedTranslations,
        });

        setDataLoaded(true);
      })
      .catch((error) => {
        if (pathEq(['response', 'status'], 401)(error)) {
          window.location.href = 'index.php?disconnect=1';
        }
      });
  }, []);

  if (!dataLoaded) {
    return <Loader fullContent />;
  }

  return (
    <Context.Provider
      value={{
        ...user,
        acl: {
          actions: actionAcl,
        },
        downtime,
        refreshInterval,
      }}
    >
      <Provider store={store}>
        <App />
      </Provider>
    </Context.Provider>
  );
};

export default AppProvider;
