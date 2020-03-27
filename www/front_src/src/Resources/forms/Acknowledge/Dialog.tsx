import * as React from 'react';

import { Typography, Checkbox, FormHelperText, Grid } from '@material-ui/core';

import { Dialog, TextField, Loader } from '@centreon/ui';

import {
  labelCancel,
  labelAcknowledge,
  labelComment,
  labelNotify,
  labelNotifyHelpCaption,
  labelAcknowledgeServices,
} from '../../translatedLabels';
import { Resource } from '../../models';

interface Props {
  resources: Array<Resource>;
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
  resources,
  canConfirm,
  onCancel,
  onConfirm,
  errors,
  values,
  submitting,
  handleChange,
  loading,
}: Props): JSX.Element => {
  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

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
      <Grid direction="column" container spacing={1}>
        <Grid item>
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
        <Grid container item direction="column">
          <Grid item container xs alignItems="center">
            <Grid item xs={1}>
              <Checkbox
                inputProps={{ 'aria-label': labelNotify }}
                color="primary"
                value={values.notify}
                onChange={handleChange('notify')}
              />
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
        {hasHosts && (
          <Grid container item direction="column">
            <Grid item container xs alignItems="center">
              <Grid item xs={1}>
                <Checkbox
                  checked={values.acknowledgeAttachedResources}
                  inputProps={{ 'aria-label': labelAcknowledgeServices }}
                  color="primary"
                  onChange={handleChange('acknowledgeAttachedResources')}
                />
              </Grid>
              <Grid item xs>
                <Typography>{labelAcknowledgeServices}</Typography>
              </Grid>
            </Grid>
          </Grid>
        )}
      </Grid>
    </Dialog>
  );
};

export default DialogAcknowledge;
