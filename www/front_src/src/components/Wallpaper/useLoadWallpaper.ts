import * as React from 'react';

import { always, lte, cond } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';

import { useTheme } from '@mui/material';

import centreonWallpaperXl from '../../assets/centreon-wallpaper-xl.jpg';
import centreonWallpaperLg from '../../assets/centreon-wallpaper-lg.jpg';
import centreonWallpaperSm from '../../assets/centreon-wallpaper-sm.jpg';

import { loadImageDerivedAtom } from './loadImageAtom';

const useLoadWallpaper = (): void => {
  const theme = useTheme();

  const loadImage = useUpdateAtom(loadImageDerivedAtom);

  const imagePath = React.useMemo(
    (): string =>
      cond<number, string>([
        [lte(theme.breakpoints.values.xl), always(centreonWallpaperXl)],
        [lte(theme.breakpoints.values.lg), always(centreonWallpaperLg)],
        [lte(theme.breakpoints.values.sm), always(centreonWallpaperSm)],
      ])(window.screen.width),
    [],
  );

  React.useEffect(() => {
    loadImage(imagePath);
  }, []);
};

export default useLoadWallpaper;
