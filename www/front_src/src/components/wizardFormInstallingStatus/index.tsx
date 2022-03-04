/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography, Paper } from '@mui/material';

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
          <Typography>{t('Creating Export Task')}</Typography>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span className={classes.statusCreating}>
              {statusCreating != null ? (
                <Typography variant="body2">
                  {statusCreating ? '[OK]' : '[FAIL]'}
                </Typography>
              ) : (
                '...'
              )}
            </span>
          </ContentWithCircularLoading>
        </p>
        <p className={classes.formText}>
          <Typography>{t('Generating Export Files')}</Typography>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span className={classes.statusGenerating}>
              {statusGenerating != null ? (
                <Typography variant="body2">
                  {statusGenerating ? '[OK]' : '[FAIL]'}
                </Typography>
              ) : (
                '...'
              )}
            </span>
          </ContentWithCircularLoading>
        </p>
        {hasError && (
          <Typography color="error" variant="body2">
            {error}
          </Typography>
        )}
      </div>
    </Paper>
  );
};
