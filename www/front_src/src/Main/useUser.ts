import { useAtom, atom } from 'jotai';
import { useUpdateAtom } from 'jotai/utils';
import { gt, isNil, not, or, pathEq, __ } from 'ramda';

import { User, userAtom } from '@centreon/ui-context';
import {
  useRequest,
  getData,
  useSnackbar,
  useLocaleDateTimeFormat,
} from '@centreon/ui';

import { userDecoder } from '../api/decoders';
import { userEndpoint } from '../api/endpoint';

import { labelYourPasswordWillExpireIn } from './translatedLabels';

export const areUserParametersLoadedAtom = atom<boolean | null>(null);

const sevenDays = 60 * 60 * 24 * 7;

const isGreaterThanSevenDays = gt(__, sevenDays);

const useUser = (
  changeLanguage: (locale: string) => void,
): (() => null | Promise<void>) => {
  const { sendRequest: getUser } = useRequest<User>({
    decoder: userDecoder,
    httpCodesBypassErrorSnackbar: [403, 401],
    request: getData,
  });

  const { showWarningMessage } = useSnackbar();
  const { toHumanizedDuration } = useLocaleDateTimeFormat();

  const [areUserParametersLoaded, setAreUserParametersLoaded] = useAtom(
    areUserParametersLoadedAtom,
  );
  const setUser = useUpdateAtom(userAtom);

  const loadUser = (): null | Promise<void> => {
    if (areUserParametersLoaded) {
      return null;
    }

    return getUser({
      endpoint: userEndpoint,
    })
      .then((retrievedUser) => {
        if (isNil(retrievedUser)) {
          return;
        }

        const {
          alias,
          isExportButtonEnabled,
          locale,
          name,
          timezone,
          useDeprecatedPages,
          defaultPage,
          passwordRemainingTime,
        } = retrievedUser as User;

        setUser({
          alias,
          defaultPage: defaultPage || '/monitoring/resources',
          isExportButtonEnabled,
          locale: locale || 'en',
          name,
          passwordRemainingTime,
          timezone,
          useDeprecatedPages,
        });
        changeLanguage((retrievedUser as User).locale.substring(0, 2));
        setAreUserParametersLoaded(true);

        if (
          isNil(passwordRemainingTime) ||
          isGreaterThanSevenDays(passwordRemainingTime)
        ) {
          return;
        }

        showWarningMessage(
          `${labelYourPasswordWillExpireIn} ${toHumanizedDuration(
            passwordRemainingTime,
          )}`,
        );
      })
      .catch((error) => {
        const isUserAllowed = not(
          or(
            pathEq(['response', 'status'], 403)(error),
            pathEq(['response', 'status'], 401)(error),
          ),
        );

        if (isUserAllowed) {
          return;
        }

        setAreUserParametersLoaded(false);
      });
  };

  return loadUser;
};

export default useUser;
