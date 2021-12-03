import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import { equals, not } from 'ramda';

import { Button, makeStyles } from '@material-ui/core';

import { ConfirmDialog, SaveButton, useMemoComponent } from '@centreon/ui';

import {
  labelCancel,
  labelDoYouWantToResetTheForm,
  labelReset,
  labelResetTheForm,
  labelSave,
  labelSaved,
  labelSaving,
} from '../translatedLabels';

import { defaultSecurityPolicy } from './defaults';

const useStyles = makeStyles((theme) => ({
  buttons: {
    alignItems: 'center',
    columnGap: theme.spacing(2),
    display: 'flex',
    flexDirection: 'row',
    marginTop: theme.spacing(1),
  },
}));

const FormButtons = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [submitted, setSubmitted] = React.useState(false);
  const [askingBeforeReset, setAskingBeforeReset] = React.useState(false);

  const { isSubmitting, dirty, isValid, values, submitForm, resetForm } =
    useFormikContext<FormikValues>();

  const submit = (): void => {
    submitForm()
      .then(() => {
        setSubmitted(true);
        setTimeout(() => {
          setSubmitted(false);
        }, 700);
        resetForm(values);
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

  const areValuesEqualsToDefault = equals(values, defaultSecurityPolicy);
  const formHasChanged = areValuesEqualsToDefault || dirty;

  const canSubmit =
    not(isSubmitting) && formHasChanged && isValid && not(submitted);
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
