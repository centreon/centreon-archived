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
  User,
  Actions,
} from '@centreon/ui-context';

import App from '../App';
import createStore from '../store';

import {
  parametersEndpoint,
  translationEndpoint,
  aclEndpoint,
  userEndpoint,
} from './endpoint';
import { DefaultParameters } from './models';

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

  const { sendRequest: getUser } = useRequest<User>({
    request: getData,
  });
  const { sendRequest: getParameters } = useRequest<DefaultParameters>({
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
      getUser(userEndpoint),
      getParameters(parametersEndpoint),
      getTranslations(translationEndpoint),
      getAcl(aclEndpoint),
    ])
      .then(
        ([
          retrievedUser,
          retrievedParameters,
          retrievedTranslations,
          retrievedAcl,
        ]) => {
          setUser({
            alias: retrievedUser.alias,
            name: retrievedUser.name,
            locale: retrievedUser.locale || 'en',
            timezone: retrievedUser.timezone,
          });
          setDowntime({
            default_duration: parseInt(
              retrievedParameters.monitoring_default_downtime_duration,
              10,
            ),
          });
          setRefreshInterval(
            parseInt(
              retrievedParameters.monitoring_default_refresh_interval,
              10,
            ),
          );
          setActionAcl(retrievedAcl);

          initializeI18n({
            retrievedUser,
            retrievedTranslations,
          });

          setDataLoaded(true);
        },
      )
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
