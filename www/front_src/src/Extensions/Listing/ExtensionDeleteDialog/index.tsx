import React from 'react';

import { Typography, Alert } from '@mui/material';

import { Dialog } from '@centreon/ui';

import { EntityDeleting } from '../models';

interface Props {
  deletingEntity: EntityDeleting;
  onCancel: () => void;
  onConfirm: (id: string, type: string) => void;
}
const ExtensionDeletePopup = ({
  deletingEntity,
  onConfirm,
  onCancel,
}: Props): JSX.Element => {
  const confirmDelete = (): void => {
    onConfirm(deletingEntity.id, deletingEntity.type);
  };

  return (
    <Dialog
      open
      labelConfirm="Delete"
      onCancel={onCancel}
      onClose={onCancel}
      onConfirm={confirmDelete}
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
