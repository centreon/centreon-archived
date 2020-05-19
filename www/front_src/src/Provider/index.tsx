import * as React from 'react';

import { Provider } from 'react-redux';
import { pick, pathEq } from 'ramda';

import { useRequest, getData, Loader } from '@centreon/ui';
import {
  loadTranslations,
  setLocale,
  syncTranslationWithStore,
} from 'react-redux-i18n';

import App from '../App';
import createStore from '../store';
import Context from './UserContext';
import { userEndpoint, translationEndpoint, aclEndpoint } from './endpoint';
import { User, Translations, Actions } from './models';
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
  const { sendRequest: getTranslations } = useRequest<Translations>({
    request: getData,
  });
  const { sendRequest: getAcl } = useRequest<Actions>({
    request: getData,
  });

  React.useEffect(() => {
    Promise.all([
      getUser(userEndpoint),
      getTranslations(translationEndpoint),
      getAcl(aclEndpoint),
    ])
      .then(([retrievedUser, retrievedTranslations, retrievedAcl]) => {
        setUser(pick(['username', 'locale', 'timezone'], retrievedUser));
        setActionAcl(retrievedAcl);

        syncTranslationWithStore(store);
        store.dispatch(loadTranslations(retrievedTranslations));
        store.dispatch(setLocale(retrievedUser.locale?.slice(0, 2)));

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
