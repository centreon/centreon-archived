import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import { equals, not } from 'ramda';

import { Button, makeStyles } from '@material-ui/core';

import { SaveButton, useMemoComponent } from '@centreon/ui';

import {
  labelReset,
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

  const reset = (): void => {
    resetForm();
  };

  const areValuesEqualsToDefault = equals(values, defaultSecurityPolicy);
  const formHasChanged = areValuesEqualsToDefault || dirty;

  const canSubmit =
    not(isSubmitting) && formHasChanged && isValid && not(submitted);
  const canReset = not(isSubmitting) && formHasChanged && not(submitted);

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
          onClick={reset}
        >
          {t(labelReset)}
        </Button>
      </div>
    ),
    memoProps: [canSubmit, isSubmitting, submitted],
  });
};

export default FormButtons;
