import { useTransition } from 'react';

import { useAtom } from 'jotai';
import { equals } from 'ramda';

import { userAtom, ThemeMode } from '@centreon/ui-context';

const useSwitchThemeMode = (): [
  isDarkMode: boolean,
  isPending: boolean,
  themeMode: ThemeMode,
  updateUser: () => void
] => {
  const [user, setUser] = useAtom(userAtom);
  const isDarkMode = equals(user.themeMode, ThemeMode.dark);
  const [isPending, startTransition] = useTransition();

  const themeMode = isDarkMode ? ThemeMode.light : ThemeMode.dark;
  const updateUser = (): void =>
    startTransition(() => {
      setUser({
        ...user,
        themeMode
      });
    });

  return [isPending, isDarkMode, themeMode, updateUser];
};

export default useSwitchThemeMode;
