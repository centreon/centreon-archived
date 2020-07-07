import React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';

import { Dialog, TextField, useRequest } from '@centreon/ui';

import { path, not, or } from 'ramda';
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
  onCreate: (id: number) => void;
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
  const { sendRequest, sending } = useRequest<number>({
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
      sendRequest({
        name: values.name,
        criterias: filter.criterias,
      })
        .then((id: number) => {
          onCreate(id);
        })
        .catch((requestError) => {
          form.setFieldError(
            'name',
            path(['response', 'data', 'message'], requestError),
          );
        });
    },
  });

  const confirmDisabled = or(not(form.isValid), not(form.dirty));

  return (
    <Dialog
      open={open}
      labelCancel={labelCancel}
      labelTitle={labelNewFilter}
      labelConfirm={labelSave}
      onConfirm={form.submitForm}
      confirmDisabled={confirmDisabled}
      onCancel={onCancel}
      submitting={sending}
    >
      <TextField
        label={labelName}
        value={form.values.name}
        error={form.errors.name}
        onChange={form.handleChange('name') as InputChangeEvent}
        ariaLabel={labelName}
      />
    </Dialog>
  );
};

export default CreateFilterDialog;
