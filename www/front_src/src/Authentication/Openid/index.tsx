import { useMemo, useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import { LinearProgress, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import LoadingSkeletonForm from '../FormInputs/LoadingSkeleton';
import useTab from '../useTab';

import { labelDefineOpenIDConnectConfiguration } from './translatedLabels';
import useOpenid from './useOpenid';
import Form from './Form';
import { OpenidConfiguration } from './models';
import { inputs } from './Form/inputs';

const useStyles = makeStyles((theme) => ({
  loading: {
    height: theme.spacing(0.5),
  },
}));

const OpenidConfigurationForm = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    sendingGetOpenidConfiguration,
    initialOpenidConfiguration,
    loadOpenidConfiguration,
  } = useOpenid();

  const isOpenidConfigurationEmpty = useMemo(
    () => isNil(initialOpenidConfiguration),
    [initialOpenidConfiguration],
  );

  useTab(isOpenidConfigurationEmpty);

  useEffect(() => {
    loadOpenidConfiguration();
  }, []);

  return (
    <div>
      <Typography variant="h4">
        {t(labelDefineOpenIDConnectConfiguration)}
      </Typography>
      <div className={classes.loading}>
        {not(isOpenidConfigurationEmpty) && sendingGetOpenidConfiguration && (
          <LinearProgress />
        )}
      </div>
      {isOpenidConfigurationEmpty ? (
        <LoadingSkeletonForm inputs={inputs} />
      ) : (
        <Form
          initialValues={initialOpenidConfiguration as OpenidConfiguration}
          loadOpenidConfiguration={loadOpenidConfiguration}
        />
      )}
    </div>
  );
};

export default OpenidConfigurationForm;
