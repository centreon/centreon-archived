import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import { not } from 'ramda';

import { Button } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { ConfirmDialog, SaveButton, useMemoComponent } from '@centreon/ui';

import {
  labelCancel,
  labelDoYouWantToResetTheForm,
  labelReset,
  labelResetTheForm,
  labelSave,
  labelSaved,
  labelSaving,
} from './Local/translatedLabels';

const useStyles = makeStyles((theme) => ({
  buttons: {
    alignItems: 'center',
    columnGap: theme.spacing(2),
    display: 'flex',
    flexDirection: 'row',
    marginTop: theme.spacing(2),
  },
}));

const FormButtons = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [submitted, setSubmitted] = React.useState(false);
  const [askingBeforeReset, setAskingBeforeReset] = React.useState(false);

  const { isSubmitting, dirty, isValid, submitForm, resetForm } =
    useFormikContext<FormikValues>();

  const submit = (): void => {
    submitForm()
      .then(() => {
        setSubmitted(true);
        setTimeout(() => {
          setSubmitted(false);
        }, 700);
      })
      .catch(() => undefined);
  };

  const askBeforeReset = (): void => {
    setAskingBeforeReset(true);
  };

  const reset = (): void => {
    resetForm();
    closeAskingBeforeReset();
  };

  const closeAskingBeforeReset = (): void => {
    setAskingBeforeReset(false);
  };

  const canSubmit = not(isSubmitting) && dirty && isValid && not(submitted);
  const canReset = not(isSubmitting) && dirty && not(submitted);

  return useMemoComponent({
    Component: (
      <div className={classes.buttons}>
        <SaveButton
          disabled={not(canSubmit)}
          labelLoading={labelSaving}
          labelSave={labelSave}
          labelSucceeded={labelSaved}
          loading={isSubmitting}
          size="small"
          succeeded={submitted}
          onClick={submit}
        />
        <Button
          disabled={not(canReset)}
          size="small"
          variant="contained"
          onClick={askBeforeReset}
        >
          {t(labelReset)}
        </Button>
        <ConfirmDialog
          labelCancel={t(labelCancel)}
          labelConfirm={t(labelReset)}
          labelMessage={t(labelDoYouWantToResetTheForm)}
          labelTitle={t(labelResetTheForm)}
          open={askingBeforeReset}
          onCancel={closeAskingBeforeReset}
          onClose={closeAskingBeforeReset}
          onConfirm={reset}
        />
      </div>
    ),
    memoProps: [
      canSubmit,
      canReset,
      isSubmitting,
      submitted,
      askingBeforeReset,
    ],
  });
};

export default FormButtons;
