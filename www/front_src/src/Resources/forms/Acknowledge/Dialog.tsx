import * as React from 'react';

import { Typography, Checkbox, FormHelperText, Grid } from '@material-ui/core';

import { Dialog, TextField, Loader } from '@centreon/ui';

import {
  labelCancel,
  labelAcknowledge,
  labelComment,
  labelNotify,
  labelNotifyHelpCaption,
} from '../../translatedLabels';

interface Props {
  open: boolean;
  canConfirm: boolean;
  onCancel;
  onConfirm;
  errors?;
  values;
  handleChange;
  submitting: boolean;
  loading: boolean;
}

const DialogAcknowledge = ({
  open,
  canConfirm,
  onCancel,
  onConfirm,
  errors,
  values,
  submitting,
  handleChange,
  loading,
}: Props): JSX.Element => {
  return (
    <Dialog
      labelCancel={labelCancel}
      labelConfirm={labelAcknowledge}
      labelTitle={labelAcknowledge}
      open={open}
      onClose={onCancel}
      onCancel={onCancel}
      onConfirm={onConfirm}
      confirmDisabled={!canConfirm}
      submitting={submitting}
    >
      {loading && <Loader fullContent />}
      <Grid direction="column" container spacing={2}>
        <Grid>
          <TextField
            value={values.comment}
            onChange={handleChange('comment')}
            multiline
            label={labelComment}
            fullWidth
            rows={3}
            error={errors?.comment !== undefined}
            helperText={errors?.comment}
          />
        </Grid>
        <Grid item>
          <Grid container direction="column">
            <Grid item container xs alignItems="center">
              <Grid item xs={1}>
                <Checkbox onChange={handleChange('notify')} />
              </Grid>
              <Grid item xs>
                <Typography>{labelNotify}</Typography>
              </Grid>
            </Grid>
            <Grid item container xs>
              <Grid item xs={1} />
              <Grid item xs>
                <FormHelperText>{labelNotifyHelpCaption}</FormHelperText>
              </Grid>
            </Grid>
          </Grid>
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default DialogAcknowledge;
