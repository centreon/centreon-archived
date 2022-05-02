import { isNil, not } from 'ramda';

import { CreateCSSProperties, makeStyles } from '@mui/styles';
import { Fade, Theme } from '@mui/material';

import { labelCentreonWallpaper } from './translatedLabels';

export const defaultBackground = `
linear-gradient(270deg, rgb(4, 76, 135), rgb(12, 114, 196), rgba(102, 183, 227, 0.2)),
linear-gradient(180deg, rgb(1, 36, 56), rgba(104, 186, 229, 0.2)),
linear-gradient(0deg, rgb(2, 40, 62), rgba(113, 195, 237, 0.2))`;

interface Props {
  image: string | null;
}

const useStyles = makeStyles<Theme, Props>({
  wallpaper: ({ image }): CreateCSSProperties => ({
    background: defaultBackground,
    backgroundImage: `url(${image})`,
    backgroundPosition: '50%',
    backgroundRepeat: 'no-repeat',
    backgroundSize: 'cover',
    bottom: 0,
    left: 0,
    position: 'fixed',
    right: 0,
    top: 0,
  }),
});

const BackgroundImage = ({ image }: Props): JSX.Element => {
  const classes = useStyles({ image });

  return (
    <Fade in={not(isNil(image))}>
      <div aria-label={labelCentreonWallpaper} className={classes.wallpaper} />
    </Fade>
  );
};

export default BackgroundImage;
