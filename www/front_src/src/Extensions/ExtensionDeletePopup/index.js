/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import { Typography } from '@material-ui/core';
import { Alert } from '@material-ui/lab';

import { Dialog } from '@centreon/ui';

class ExtensionDeletePopup extends React.Component {
  render() {
    const { deletingEntity, onConfirm, onCancel } = this.props;

    return (
      <Dialog
        open
        labelConfirm="Delete"
        onCancel={onCancel}
        onClose={onCancel}
        onConfirm={() => onConfirm(deletingEntity.id, deletingEntity.type)}
      >
        <Typography variant="h6">{deletingEntity.description}</Typography>

        <Alert severity="warning">
          Do you want to delete this extension? This action will remove all
          associated data.
        </Alert>
      </Dialog>
    );
  }
}

export default ExtensionDeletePopup;
