import * as React from 'react';

import 'dayjs/locale/en';
import 'dayjs/locale/pt';
import 'dayjs/locale/fr';
import 'dayjs/locale/es';
import dayjs from 'dayjs';
import timezonePlugin from 'dayjs/plugin/timezone';
import utcPlugin from 'dayjs/plugin/utc';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import isToday from 'dayjs/plugin/isToday';
import isYesterday from 'dayjs/plugin/isYesterday';
import weekday from 'dayjs/plugin/weekday';
import isBetween from 'dayjs/plugin/isBetween';
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore';
import { Provider as ReduxProvider } from 'react-redux';
import { pathEq, toPairs, pipe, reduce, mergeAll } from 'ramda';
import i18n, { Resource, ResourceLanguage } from 'i18next';
import { initReactI18next } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { useRequest, getData } from '@centreon/ui';
import {
  userAtom,
  User,
  Actions,
  downtimeAtom,
  refreshIntervalAtom,
  acknowledgementAtom,
  aclAtom,
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
// TODO uncomment after https://github.com/centreon/centreon/pull/10507
// import { userDecoder } from './decoder';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
dayjs.extend(isToday);
dayjs.extend(isYesterday);
dayjs.extend(weekday);
dayjs.extend(isBetween);
dayjs.extend(isSameOrBefore);

const store = createStore();

interface Props {
  children: React.ReactNode;
}

const Provider = ({ children }: Props): JSX.Element => {
  const [dataLoaded, setDataLoaded] = React.useState(false);

  const { sendRequest: getUser } = useRequest<User>({
    // TODO uncomment after https://github.com/centreon/centreon/pull/10507
    // decoder: userDecoder,
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

  const setUser = useUpdateAtom(userAtom);
  const setDowntime = useUpdateAtom(downtimeAtom);
  const setRefreshInterval = useUpdateAtom(refreshIntervalAtom);
  const setAcl = useUpdateAtom(aclAtom);
  const setAcknowledgement = useUpdateAtom(acknowledgementAtom);

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
      getUser({
        endpoint: userEndpoint,
      }),
      getParameters({
        endpoint: parametersEndpoint,
      }),
      getTranslations({
        endpoint: translationEndpoint,
      }),
      getAcl({
        endpoint: aclEndpoint,
      }),
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
            default_page: retrievedUser.default_page,
            isExportButtonEnabled: retrievedUser.isExportButtonEnabled,
            locale: retrievedUser.locale || 'en',
            name: retrievedUser.name,
            timezone: retrievedUser.timezone,
            use_deprecated_pages: retrievedUser.use_deprecated_pages,
          });
          setDowntime({
            default_duration: parseInt(
              retrievedParameters.monitoring_default_downtime_duration,
              10,
            ),
            default_fixed:
              retrievedParameters.monitoring_default_downtime_fixed,
            default_with_services:
              retrievedParameters.monitoring_default_downtime_with_services,
          });
          setRefreshInterval(
            parseInt(
              retrievedParameters.monitoring_default_refresh_interval,
              10,
            ),
          );
          setAcl({ actions: retrievedAcl });
          setAcknowledgement({
            persistent:
              retrievedParameters.monitoring_default_acknowledgement_persistent,
            sticky:
              retrievedParameters.monitoring_default_acknowledgement_sticky,
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
    <ReduxProvider store={store}>
      <React.Suspense fallback={<PageLoader />}>{children}</React.Suspense>
    </ReduxProvider>
  );
};

export default Provider;
