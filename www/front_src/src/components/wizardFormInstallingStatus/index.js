/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import classnames from 'classnames';
import { useTranslation } from 'react-i18next';

import { Typography, Paper } from '@mui/material';

import { ContentWithCircularLoading } from '@centreon/ui';

import styles from '../../styles/partials/form/_form.scss';

export default ({ formTitle, statusCreating, statusGenerating, error }) => {
  const { t } = useTranslation();
  const loading = statusCreating === null || statusGenerating === null;
  const hasError =
    (statusCreating === false || statusGenerating === false) && error;

  return (
    <Paper
      className={classnames(styles['form-container'], styles.installation)}
    >
      <div className={styles['form-inner']}>
        <div className={styles['form-heading']}>
          <Typography variant="h6">{formTitle}</Typography>
        </div>
        {/* display loader until tasks are finished or error is displayed */}
        <p className={styles['form-text']}>
          <Typography>{t('Creating Export Task')}</Typography>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span
              className={classnames(
                styles['form-status'],
                styles[statusCreating ? 'valid' : 'failed'],
              )}
            >
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
        <p className={styles['form-text']}>
          <Typography>{t('Generating Export Files')}</Typography>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span
              className={classnames(
                styles['form-status'],
                styles[statusGenerating ? 'valid' : 'failed'],
              )}
            >
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
