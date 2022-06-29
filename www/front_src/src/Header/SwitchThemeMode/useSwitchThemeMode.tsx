import { useAtom } from 'jotai';
import { equals } from 'ramda';

import { userAtom, ThemeMode } from '@centreon/ui-context';

const useSwitchThemeMode = (): [
  isDarkMode: boolean,
  themeMode: ThemeMode,
  updateUser: () => void,
] => {
  const [user, setUser] = useAtom(userAtom);
  const isDarkMode = equals(user.themeMode, ThemeMode.dark);

  const themeMode = isDarkMode ? ThemeMode.light : ThemeMode.dark;
  const updateUser = (): void =>
    setUser({
      ...user,
      themeMode,
    });

  return [isDarkMode, themeMode, updateUser];
};

export default useSwitchThemeMode;
