import * as React from 'react';

import { useTranslation } from 'react-i18next';

import {
  Checkbox,
  FormControlLabel,
  FormHelperText,
  Grid,
} from '@material-ui/core';
import { Alert } from '@material-ui/lab';

import { Dialog, TextField } from '@centreon/ui';

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
  canConfirm: boolean;
  errors?;
  handleChange;
  onCancel;
  onConfirm;
  resources: Array<Resource>;
  submitting: boolean;
  values;
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
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const { getAcknowledgementDeniedTypeAlert, canAcknowledgeServices } =
    useAclQuery();

  const deniedTypeAlert = getAcknowledgementDeniedTypeAlert(resources);

  const open = resources.length > 0;

  const hasHosts = resources.find((resource) => resource.type === 'host');

  return (
    <Dialog
      confirmDisabled={!canConfirm}
      labelCancel={t(labelCancel)}
      labelConfirm={t(labelAcknowledge)}
      labelTitle={t(labelAcknowledge)}
      open={open}
      submitting={submitting}
      onCancel={onCancel}
      onClose={onCancel}
      onConfirm={onConfirm}
    >
      <Grid container direction="column" spacing={1}>
        {deniedTypeAlert && (
          <Grid item>
            <Alert severity="warning">{deniedTypeAlert}</Alert>
          </Grid>
        )}
        <Grid item>
          <TextField
            fullWidth
            multiline
            error={errors?.comment}
            label={t(labelComment)}
            rows={3}
            value={values.comment}
            onChange={handleChange('comment')}
          />
        </Grid>
        <Grid item>
          <FormControlLabel
            control={
              <Checkbox
                checked={values.notify}
                color="primary"
                inputProps={{ 'aria-label': t(labelNotify) }}
                size="small"
                onChange={handleChange('notify')}
              />
            }
            label={t(labelNotify) as string}
          />
          <FormHelperText>{t(labelNotifyHelpCaption)}</FormHelperText>
        </Grid>
        {hasHosts && (
          <Grid item>
            <FormControlLabel
              control={
                <Checkbox
                  checked={
                    canAcknowledgeServices() &&
                    values.acknowledgeAttachedResources
                  }
                  color="primary"
                  disabled={!canAcknowledgeServices()}
                  inputProps={{ 'aria-label': t(labelAcknowledgeServices) }}
                  size="small"
                  onChange={handleChange('acknowledgeAttachedResources')}
                />
              }
              label={t(labelAcknowledgeServices) as string}
            />
          </Grid>
        )}
      </Grid>
    </Dialog>
  );
};

export default DialogAcknowledge;
