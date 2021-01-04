import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isEmpty, isNil } from 'ramda';

import { Grid, Typography } from '@material-ui/core';

import {
  Dialog,
  TextField,
  useSnackbar,
  useRequest,
  Severity,
  useLocaleDateTimeFormat,
} from '@centreon/ui';

import {
  labelAdd,
  labelAddComment,
  labelComment,
  labelRequired,
  labelCommentAdded,
} from '../../../../translatedLabels';
import { commentResources } from '../../../../Actions/api';
import { Resource } from '../../../../models';
import { ResourceDetails } from '../../../../Details/models';

interface Props {
  onClose: () => void;
  onSuccess: (comment) => void;
  date: Date;
  resource: Resource | ResourceDetails;
}

const AddCommentForm = ({
  onClose,
  onSuccess,
  resource,
  date,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const { toIsoString, toDateTime } = useLocaleDateTimeFormat();
  const { showMessage } = useSnackbar();
  const [comment, setComment] = React.useState<string>();

  const { sendRequest, sending } = useRequest({
    request: commentResources,
  });

  const changeComment = (event: React.ChangeEvent<HTMLInputElement>): void => {
    setComment(event.target.value);
  };

  const confirm = (): void => {
    const parameters = {
      comment,
      date: toIsoString(date),
    };

    sendRequest({
      resources: [resource],
      parameters,
    }).then(() => {
      showMessage({
        message: t(labelCommentAdded),
        severity: Severity.success,
      });
      onSuccess(parameters);
    });
  };

  const error = isEmpty(comment) ? t(labelRequired) : undefined;

  const canConfirm = isNil(error) && !isNil(comment) && !sending;

  return (
    <Dialog
      open
      onClose={onClose}
      onCancel={onClose}
      onConfirm={confirm}
      labelConfirm={t(labelAdd)}
      labelTitle={t(labelAddComment)}
      confirmDisabled={!canConfirm}
      submitting={sending}
    >
      <Grid direction="column" container spacing={2}>
        <Grid item>
          <Typography variant="h6">{toDateTime(date)}</Typography>
        </Grid>
        <Grid item>
          <TextField
            autoFocus
            error={error}
            label={t(labelComment)}
            ariaLabel={t(labelComment)}
            value={comment}
            required
            onChange={changeComment}
            style={{ width: 300 }}
            rows={3}
            multiline
          />
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default AddCommentForm;
