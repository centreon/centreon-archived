import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';

import logoCentreon from '../assets/centreon.png';

import { labelCentreonLogo } from './translatedLabels';

const useStyles = makeStyles({
  centreonLogo: {
    height: 'auto',
    width: 'auto',
  },
});

const Logo = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <img
      alt={t(labelCentreonLogo)}
      aria-label={t(labelCentreonLogo)}
      className={classes.centreonLogo}
      src={logoCentreon}
    />
  );
};

export default Logo;
