import * as React from 'react';

import { Provider } from 'react-redux';
import { pick, pathEq, toPairs, pipe, reduce, mergeAll } from 'ramda';

import { useRequest, getData, Loader } from '@centreon/ui';
import i18n, { Resource, ResourceLanguage } from 'i18next';
import { initReactI18next } from 'react-i18next';
import App from '../App';
import createStore from '../store';
import Context from './UserContext';
import { userEndpoint, translationEndpoint, aclEndpoint } from './endpoint';
import { User, Actions } from './models';
import useUser from './useUser';
import useAcl from './useAcl';

const store = createStore();

const AppProvider = (): JSX.Element | null => {
  const { user, setUser } = useUser();
  const { actionAcl, setActionAcl } = useAcl();
  const [dataLoaded, setDataLoaded] = React.useState(false);

  const { sendRequest: getUser } = useRequest<User>({
    request: getData,
  });
  const { sendRequest: getTranslations } = useRequest<ResourceLanguage>({
    request: getData,
  });
  const { sendRequest: getAcl } = useRequest<Actions>({
    request: getData,
  });

  const initializeI18n = ({ retrievedUser, retrievedTranslations }): void => {
    const locale = retrievedUser.locale?.slice(0, 2);

    i18n.use(initReactI18next).init({
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
      getTranslations(translationEndpoint),
      getAcl(aclEndpoint),
    ])
      .then(([retrievedUser, retrievedTranslations, retrievedAcl]) => {
        setUser(pick(['username', 'locale', 'timezone'], retrievedUser));
        setActionAcl(retrievedAcl);

        initializeI18n({ retrievedUser, retrievedTranslations });

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
      }}
    >
      <Provider store={store}>
        <App />
      </Provider>
    </Context.Provider>
  );
};

export default AppProvider;
