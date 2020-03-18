import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { labelRequired } from '../../translatedLabels';
import DialogAcknowledge from './Dialog';
import { Resource } from '../../models';

const validationSchema = Yup.object().shape({
  comment: Yup.string().required(labelRequired),
  notify: Yup.boolean(),
});

interface Props {
  resources: Array<Resource>;
  onClose;
}

const AcknowledgeForm = ({ resources, onClose }: Props): JSX.Element => {
  const form = useFormik({
    initialValues: {
      comment: '',
      notify: false,
    },
    onSubmit: () => onClose(),
    validationSchema,
  });

  const hasResources = resources.length > 0;

  return (
    <DialogAcknowledge
      open={hasResources}
      onConfirm={form.submitForm}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
    />
  );
};

export default AcknowledgeForm;
