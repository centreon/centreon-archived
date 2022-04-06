import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import { equals, not } from 'ramda';
import { useAtom } from 'jotai';

import { Button } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import {
  ConfirmDialog,
  SaveButton,
  useMemoComponent,
  UnsavedChangesDialog,
} from '@centreon/ui';

import {
  labelCancel,
  labelDoYouWantToResetTheForm,
  labelReset,
  labelResetTheForm,
  labelSave,
  labelSaved,
  labelSaving,
} from './Local/translatedLabels';
import { tabAtom, appliedTabAtom } from './tabAtoms';

const useStyles = makeStyles((theme) => ({
  buttons: {
    alignItems: 'center',
    columnGap: theme.spacing(2),
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginTop: theme.spacing(2),
  },
}));

const FormButtons = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [submitted, setSubmitted] = React.useState(false);
  const [askingBeforeReset, setAskingBeforeReset] = React.useState(false);

  const { isSubmitting, dirty, isValid, submitForm, resetForm, errors } =
    useFormikContext<FormikValues>();

  console.log(errors);

  const [unsavedDialogOpened, setUnsavedDialogOpened] = React.useState(false);

  const [appliedTab, setAppliedTab] = useAtom(appliedTabAtom);
  const [tab, setTab] = useAtom(tabAtom);

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

  const closeUnsavedDialog = (): void => {
    setUnsavedDialogOpened(false);
    setTab(appliedTab);
  };

  const saveChanges = (): void => {
    submitForm().then(() => setAppliedTab(tab));
  };

  const discardChanges = (): void => setAppliedTab(tab);

  const canSubmit = not(isSubmitting) && dirty && isValid && not(submitted);
  const canReset = not(isSubmitting) && dirty && not(submitted);

  React.useEffect(() => {
    if (not(dirty) || equals(tab, appliedTab)) {
      setAppliedTab(tab);

      return;
    }

    setUnsavedDialogOpened(true);
  }, [tab, appliedTab]);

  return useMemoComponent({
    Component: (
      <div className={classes.buttons}>
        <Button
          aria-label={t(labelReset)}
          disabled={not(canReset)}
          size="small"
          onClick={askBeforeReset}
        >
          {t(labelReset)}
        </Button>
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
        <UnsavedChangesDialog
          closeDialog={closeUnsavedDialog}
          dialogOpened={unsavedDialogOpened}
          discardChanges={discardChanges}
          isSubmitting={isSubmitting}
          isValidForm={isValid}
          saveChanges={saveChanges}
        />
      </div>
    ),
    memoProps: [
      canSubmit,
      canReset,
      isSubmitting,
      submitted,
      askingBeforeReset,
      tab,
      unsavedDialogOpened,
    ],
  });
};

export default FormButtons;
