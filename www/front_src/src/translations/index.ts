import {
  loadTranslations,
  setLocale,
  syncTranslationWithStore,
} from 'react-redux-i18n';
import { Store, CombinedState } from 'redux';
import axios from '../axios';

const translationService = axios(
  'internal.php?object=centreon_i18n&action=translation',
);
const userService = axios(
  'internal.php?object=centreon_topcounter&action=user',
);

export default function setTranslations(
  store: Store<CombinedState>,
  callback: Function,
): void {
  const localePromise = userService.get();
  const translationsPromise = translationService.get();

  Promise.all([localePromise, translationsPromise])
    .then((response: Array) => {
      let { locale } = response[0].data;
      locale = locale !== null ? locale.slice(0, 2) : navigator.language;
      const translations = response[1].data;
      syncTranslationWithStore(store);
      store.dispatch(loadTranslations(translations));
      store.dispatch(setLocale(locale));
      callback();
    })
    .catch((error: Error) => {
      if (error.response && error.response.status === 401) {
        // redirect to login page
        window.location.href = 'index.php?disconnect=1';
      }
    });
}
