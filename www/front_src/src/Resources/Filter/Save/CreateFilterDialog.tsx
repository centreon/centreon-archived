import React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { path, not, or, omit } from 'ramda';
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
import { RawFilter, Filter } from '../models';
import useAdapters from '../api/adapters';

type InputChangeEvent = (event: React.ChangeEvent<HTMLInputElement>) => void;

interface Props {
  onCreate: (filter) => void;
  onCancel: () => void;
  open: boolean;
  filter: Filter;
}

const CreateFilterDialog = ({
  filter,
  onCreate,
  open,
  onCancel,
}: Props): JSX.Element => {
  const { toFilter, toRawFilter } = useAdapters();

  const { t } = useTranslation();

  const { sendRequest, sending } = useRequest<RawFilter>({
    request: createFilter,
  });
  const form = useFormik({
    initialValues: {
      name: '',
    },
    validationSchema: Yup.object().shape({
      name: Yup.string().required(labelRequired),
    }),
    onSubmit: (values) => {
      sendRequest(
        omit(
          ['id'],
          toRawFilter({
            id: '',
            name: values.name,
            criterias: filter.criterias,
            sort: filter.sort,
          }),
        ),
      )
        .then(toFilter)
        .then(onCreate)
        .catch((requestError) => {
          form.setFieldError(
            'name',
            path(['response', 'data', 'message'], requestError),
          );
        });
    },
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
      open={open}
      labelCancel={t(labelCancel)}
      labelTitle={t(labelNewFilter)}
      labelConfirm={t(labelSave)}
      onConfirm={form.submitForm}
      confirmDisabled={confirmDisabled}
      onCancel={onCancel}
      submitting={sending}
    >
      <TextField
        label={t(labelName)}
        value={form.values.name}
        error={form.errors.name}
        onChange={form.handleChange('name') as InputChangeEvent}
        onKeyDown={submitFormOnEnterKey}
        ariaLabel={t(labelName)}
        autoFocus
      />
    </Dialog>
  );
};

export default CreateFilterDialog;
