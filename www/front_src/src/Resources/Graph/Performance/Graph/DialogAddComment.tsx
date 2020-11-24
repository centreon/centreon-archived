import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isEmpty, isNil } from 'ramda';

import { Grid } from '@material-ui/core';

import { Dialog, TextField } from '@centreon/ui';

import {
  labelAdd,
  labelAddComment,
  labelComment,
  labelRequired,
} from '../../../translatedLabels';

const DialogAddComment = ({ onClose, onAddComment }): JSX.Element => {
  const { t } = useTranslation();
  const [comment, setComment] = React.useState<string>();

  const changeComment = (event: React.ChangeEvent<HTMLInputElement>): void => {
    setComment(event.target.value);
  };

  const confirm = (): void => {
    onAddComment();
  };

  const error = isEmpty(comment) ? t(labelRequired) : undefined;

  const canConfirm = isNil(error) && !isNil(comment);

  return (
    <Dialog
      open
      onClose={onClose}
      onCancel={onClose}
      onConfirm={confirm}
      labelConfirm={t(labelAdd)}
      labelTitle={t(labelAddComment)}
      confirmDisabled={!canConfirm}
    >
      <Grid direction="column" container spacing={1}>
        <Grid item>
          <TextField
            error={error}
            label={t(labelComment)}
            value={comment}
            required
            onChange={changeComment}
            fullWidth
            rows={3}
            multiline
          />
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default DialogAddComment;
