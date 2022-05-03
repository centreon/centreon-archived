import { memo } from 'react';

import { useTranslation } from 'react-i18next';
import { equals } from 'ramda';

import { Divider, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import centreonLogo from '../img/centreon.png';
import { labelCentreonLogo } from '../Login/translatedLabels';

const useStyles = makeStyles((theme) => ({
  logo: {
    height: 'auto',
    width: 'auto',
  },
  page: {
    alignItems: 'center',
    display: 'grid',
    gridTemplateColumns: '1fr',
    height: '100%',
    justifyItems: 'center',
    width: '100%',
  },
  wrapper: {
    alignItems: 'center',
    columnGap: theme.spacing(3),
    display: 'flex',
    flexDirection: 'row',
    height: theme.spacing(6),
    justifyContent: 'center',
    marginTop: theme.spacing(3),
  },
}));

interface Props {
  message: string;
  statusCode?: number;
}

const NotFoundPage = ({ message, statusCode }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.page}>
      <div>
        <img
          alt={t(labelCentreonLogo)}
          className={classes.logo}
          src={centreonLogo}
        />
        <div className={classes.wrapper}>
          {statusCode && (
            <>
              <Typography variant="h6">{statusCode}</Typography>
              <Divider flexItem orientation="vertical" />
            </>
          )}
          <Typography>{t(message)}</Typography>
        </div>
      </div>
    </div>
  );
};

export default memo(NotFoundPage, equals);
