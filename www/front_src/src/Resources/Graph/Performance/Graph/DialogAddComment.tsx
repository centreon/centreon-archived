import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Grid } from '@material-ui/core';

import { Dialog, TextField } from '@centreon/ui';

import { labelAdd, labelAddComment } from '../../../translatedLabels';

const DialogAddComment = ({ onClose, onAddComment }): JSX.Element => {
  const { t } = useTranslation();
  const [comment, setComment] = React.useState('');

  const changeComment = (event: React.ChangeEvent<HTMLInputElement>): void => {
    setComment(event.target.value);
  };

  const confirm = (): void => {
    onAddComment();
  };

  return (
    <Dialog
      open
      onClose={onClose}
      onCancel={onClose}
      onConfirm={confirm}
      labelConfirm={t(labelAdd)}
      labelTitle={t(labelAddComment)}
    >
      <Grid direction="column" container spacing={1}>
        <Grid item>
          <TextField value="comment" required onChange={changeComment} />
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default DialogAddComment;
