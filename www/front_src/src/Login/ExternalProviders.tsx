import { isEmpty, isNil, or } from 'ramda';
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

  if (or(isNil(providersConfiguration), isEmpty(providersConfiguration))) {
    return null;
  }

  return (
    <div className={classes.otherProvidersContainer}>
      <Divider>
        <Typography>{t(labelOr)}</Typography>
      </Divider>
      {providersConfiguration?.map(({ name, authenticationUri }) => (
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
