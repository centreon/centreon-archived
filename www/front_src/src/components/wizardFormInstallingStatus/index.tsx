/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography, Paper, Grid, Alert } from '@mui/material';
import Chip from '@mui/material/Chip';
import CheckIcon from '@mui/icons-material/Check';
import SmsFailedIcon from '@mui/icons-material/SmsFailed';

import { ContentWithCircularLoading } from '@centreon/ui';

import { useStylesWithProps } from '../../styles/partials/form/PollerWizardStyle';

interface Props {
  error: string | null;
  formTitle: string;
  statusCreating: boolean | null;
  statusGenerating: boolean | null;
}

export default ({
  formTitle,
  statusCreating,
  statusGenerating,
  error,
}: Props): JSX.Element => {
  const classes = useStylesWithProps({ statusCreating, statusGenerating });

  const { t } = useTranslation();
  const loading = statusCreating === null || statusGenerating === null;
  const hasError =
    (statusCreating === false || statusGenerating === false) && error;

  return (
    <Paper
    // className={
    //   classnames(styles['form-container'], styles.installation)
    // }
    >
      <div
      // className={styles['form-inner']}
      >
        <div className={classes.formHeading}>
          <Typography variant="h6">{formTitle}</Typography>
        </div>
        {/* display loader until tasks are finished or error is displayed */}
        <p className={classes.formText}>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span className={classes.statusCreating}>
              {statusCreating != null ? (
                <Typography variant="body2">
                  <Chip
                    color={statusCreating ? 'success' : 'error'}
                    icon={statusCreating ? <CheckIcon /> : <SmsFailedIcon />}
                    label={t('Creating Export Task')}
                    style={{ width: '100%' }}
                  />
                </Typography>
              ) : (
                '...'
              )}
            </span>
          </ContentWithCircularLoading>
        </p>
        <p className={classes.formText}>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span className={classes.statusGenerating}>
              {statusGenerating != null ? (
                <Typography variant="body2">
                  <Chip
                    color={statusGenerating ? 'success' : 'error'}
                    icon={statusGenerating ? <CheckIcon /> : <SmsFailedIcon />}
                    label={t('Generating Export Files')}
                    style={{ width: '100%' }}
                  />
                </Typography>
              ) : (
                '...'
              )}
            </span>
          </ContentWithCircularLoading>
        </p>
        {hasError && (
          <Grid item>
            <Alert severity="error">
              <Typography>{error}</Typography>
            </Alert>
          </Grid>
        )}
      </div>
    </Paper>
  );
};
