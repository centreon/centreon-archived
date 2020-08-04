/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';
import classnames from 'classnames';
import { useTranslation } from 'react-i18next';

import styles from '../../styles/partials/form/_form.scss';
import Loader from '../loader';

export default ({
  formTitle,
  statusCreating,
  statusGenerating,
  statusProcessing,
  error,
}) => {
  const { t } = useTranslation();

  return (
    <div className={classnames(styles['form-wrapper'], styles.installation)}>
      <div className={styles['form-inner']}>
        <div className={styles['form-heading']}>
          <h2 className={styles['form-title']}>{formTitle}</h2>
        </div>
        {/* display loader until tasks are finished or error is displayed */}
        {!error && <Loader />}
        <p className={styles['form-text']}>
          {t('Creating Export Task')}
          <span
            className={classnames(
              styles['form-status'],
              styles[statusCreating ? 'valid' : 'failed'],
            )}
          >
            {statusCreating != null ? (
              <span>{statusCreating ? '[OK]' : '[FAIL]'}</span>
            ) : (
              '...'
            )}
          </span>
        </p>
        <p className={styles['form-text']}>
          {t('Generating Export Files')}
          <span
            className={classnames(
              styles['form-status'],
              styles[statusGenerating ? 'valid' : 'failed'],
            )}
          >
            {statusGenerating != null ? (
              <span>{statusGenerating ? '[OK]' : '[FAIL]'}</span>
            ) : (
              '...'
            )}
          </span>
        </p>
        <p className={styles['form-text']}>
          {t('Processing Remote Import/Configuration')}
          <span
            className={classnames(
              styles['form-status'],
              styles[statusProcessing ? 'valid' : 'failed'],
            )}
          >
            {statusProcessing != null ? (
              <span>{statusProcessing ? '[OK]' : '[FAIL]'}</span>
            ) : (
              '...'
            )}
          </span>
        </p>
        {error ? (
          <span className={styles['form-error-message']}>{error}</span>
        ) : null}
      </div>
    </div>
  );
};
