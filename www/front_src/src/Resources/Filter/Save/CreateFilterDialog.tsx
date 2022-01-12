import React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { path, not, or } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Dialog, TextField, useRequest } from '@centreon/ui';

import {
  labelSave,
  labelCancel,
  labelName,
  labelNewFilter,
  labelRequired,
} from '../../translatedLabels';
import { createFilter } from '../api';
import { Filter } from '../models';

type InputChangeEvent = (event: React.ChangeEvent<HTMLInputElement>) => void;

interface Props {
  filter: Filter;
  onCancel: () => void;
  onCreate: (filter) => void;
  open: boolean;
}

const CreateFilterDialog = ({
  filter,
  onCreate,
  open,
  onCancel,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const { sendRequest, sending } = useRequest<Filter>({
    request: createFilter,
  });
  const form = useFormik({
    initialValues: {
      name: '',
    },
    onSubmit: (values) => {
      sendRequest({ criterias: filter.criterias, name: values.name })
        .then(onCreate)
        .catch((requestError) => {
          form.setFieldError(
            'name',
            path(['response', 'data', 'message'], requestError),
          );
        });
    },
    validationSchema: Yup.object().shape({
      name: Yup.string().required(labelRequired),
    }),
  });

  const submitFormOnEnterKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      form.submitForm();
    }
  };

  const confirmDisabled = or(not(form.isValid), not(form.dirty));

  return (
    <Dialog
      confirmDisabled={confirmDisabled}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelSave)}
      labelTitle={t(labelNewFilter)}
      open={open}
      submitting={sending}
      onCancel={onCancel}
      onConfirm={form.submitForm}
    >
      <TextField
        autoFocus
        ariaLabel={t(labelName)}
        error={form.errors.name}
        label={t(labelName)}
        value={form.values.name}
        onChange={form.handleChange('name') as InputChangeEvent}
        onKeyDown={submitFormOnEnterKey}
      />
    </Dialog>
  );
};

export default CreateFilterDialog;
