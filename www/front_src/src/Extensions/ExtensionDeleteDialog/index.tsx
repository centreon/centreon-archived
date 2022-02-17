/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import { Typography, Alert } from '@mui/material';

import { Dialog } from '@centreon/ui';

const ExtensionDeletePopup = ({
  deletingEntity,
  onConfirm,
  onCancel,
}): JSX.Element => {
  return (
    <Dialog
      open
      labelConfirm="Delete"
      onCancel={onCancel}
      onClose={onCancel}
      onConfirm={(): void => onConfirm(deletingEntity.id, deletingEntity.type)}
    >
      <Typography variant="h6">{deletingEntity.description}</Typography>

      <Alert severity="warning">
        Do you want to delete this extension? This action will remove all
        associated data.
      </Alert>
    </Dialog>
  );
};

export default ExtensionDeletePopup;
