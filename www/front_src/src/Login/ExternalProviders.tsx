import * as React from 'react';

import { filter, isEmpty, propEq } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, Divider, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import { ProviderConfiguration } from './models';
import { labelLoginWith, labelOr } from './translatedLabels';

interface Props {
  providersConfiguration: Array<ProviderConfiguration> | null;
}

const useStyles = makeStyles((theme) => ({
  otherProvidersContainer: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(1),
    width: '100%',
  },
}));

const ExternalProviders = ({
  providersConfiguration,
}: Props): JSX.Element | null => {
  const classes = useStyles();
  const { t } = useTranslation();

  const activeProviders = filter(
    propEq('isActive', true),
    providersConfiguration || [],
  );

  if (isEmpty(activeProviders)) {
    return null;
  }

  return (
    <div className={classes.otherProvidersContainer}>
      <Divider>
        <Typography>{t(labelOr)}</Typography>
      </Divider>
      {activeProviders.map(({ name, authenticationUri }) => (
        <Button
          color="primary"
          href={authenticationUri}
          key={name}
          variant="contained"
        >{`${t(labelLoginWith)} ${name}`}</Button>
      ))}
    </div>
  );
};

export default ExternalProviders;
