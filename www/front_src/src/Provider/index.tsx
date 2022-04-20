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

import { useRequest, getData } from '@centreon/ui';
import {
  Context,
  useUser,
  useAcl,
  useDowntime,
  useRefreshInterval,
  useAcknowledgement,
  User,
  Actions,
} from '@centreon/ui-context';

import createStore from '../store';
import PageLoader from '../components/PageLoader';

import {
  parametersEndpoint,
  translationEndpoint,
  aclEndpoint,
  userEndpoint,
} from './endpoint';
import { DefaultParameters } from './models';
import usePendo from './usePendo';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);

const App = React.lazy(() => import('../App'));

const store = createStore();

const AppProvider = (): JSX.Element | null => {
  const { user, setUser } = useUser();
  const { downtime, setDowntime } = useDowntime();
  const { acknowledgement, setAcknowledgement } = useAcknowledgement();
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
  usePendo();

  const initializeI18n = ({ retrievedUser, retrievedTranslations }): void => {
    const locale = (retrievedUser.locale || navigator.language)?.slice(0, 2);

    i18n.use(initReactI18next).init({
      fallbackLng: 'en',
      keySeparator: false,
      lng: locale,
      nsSeparator: false,
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
            locale: retrievedUser.locale || 'en',
            name: retrievedUser.name,
            timezone: retrievedUser.timezone,
            use_deprecated_pages: retrievedUser.use_deprecated_pages,
          });
          setDowntime({
            duration: parseInt(
              retrievedParameters.monitoring_default_downtime_duration,
              10,
            ),
            fixed: retrievedParameters.monitoring_default_downtime_fixed,
            with_services:
              retrievedParameters.monitoring_default_downtime_with_services,
          });
          setRefreshInterval(
            parseInt(
              retrievedParameters.monitoring_default_refresh_interval,
              10,
            ),
          );
          setActionAcl(retrievedAcl);
          setAcknowledgement({
            force_active_checks:
              retrievedParameters.monitoring_default_acknowledgement_force_active_checks,
            notify:
              retrievedParameters.monitoring_default_acknowledgement_notify,
            persistent:
              retrievedParameters.monitoring_default_acknowledgement_persistent,
            sticky:
              retrievedParameters.monitoring_default_acknowledgement_sticky,
            with_services:
              retrievedParameters.monitoring_default_acknowledgement_with_services,
          });

          initializeI18n({
            retrievedTranslations,
            retrievedUser,
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
    return <PageLoader />;
  }

  return (
    <Context.Provider
      value={{
        ...user,
        acknowledgement,
        acl: {
          actions: actionAcl,
        },
        downtime,
        refreshInterval,
      }}
    >
      <Provider store={store}>
        <React.Suspense fallback={<PageLoader />}>
          <App />
        </React.Suspense>
      </Provider>
    </Context.Provider>
  );
};

export default AppProvider;
