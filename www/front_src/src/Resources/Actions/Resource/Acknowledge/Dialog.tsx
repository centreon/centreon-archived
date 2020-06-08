import * as React from 'react';

import {
  Checkbox,
  FormControlLabel,
  FormHelperText,
  Grid,
} from '@material-ui/core';
import { Alert } from '@material-ui/lab';

import { Dialog, TextField, Loader } from '@centreon/ui';

import {
  labelCancel,
  labelAcknowledge,
  labelComment,
  labelNotify,
  labelNotifyHelpCaption,
  labelAcknowledgeServices,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import useAclQuery from '../aclQuery';

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
  const {
    getAcknowledgementDeniedTypeAlert,
    canAcknowledgeServices,
  } = useAclQuery();

  const deniedTypeAlert = getAcknowledgementDeniedTypeAlert(resources);

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
        {deniedTypeAlert && (
          <Grid item>
            <Alert severity="warning">{deniedTypeAlert}</Alert>
          </Grid>
        )}
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
        <Grid item>
          <FormControlLabel
            control={
              <Checkbox
                checked={values.notify}
                inputProps={{ 'aria-label': labelNotify }}
                color="primary"
                onChange={handleChange('notify')}
                size="small"
              />
            }
            label={labelNotify}
          />
          <FormHelperText>{labelNotifyHelpCaption}</FormHelperText>
        </Grid>
        {hasHosts && (
          <Grid item>
            <FormControlLabel
              control={
                <Checkbox
                  checked={values.acknowledgeAttachedResources}
                  disabled={!canAcknowledgeServices()}
                  inputProps={{ 'aria-label': labelAcknowledgeServices }}
                  color="primary"
                  onChange={handleChange('acknowledgeAttachedResources')}
                  size="small"
                />
              }
              label={labelAcknowledgeServices}
            />
          </Grid>
        )}
      </Grid>
    </Dialog>
  );
};

export default DialogAcknowledge;
