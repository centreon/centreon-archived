import * as React from 'react';

import { makeStyles } from '@material-ui/core';
import SaveIcon from '@material-ui/icons/Save';
import DeleteIcon from '@material-ui/icons/Delete';

import {
  TextField,
  IconButton,
  useRequest,
  ConfirmDialog,
  useSnackbar,
  Severity,
} from '@centreon/ui';

import { useFormik } from 'formik';
import { or, isNil, and } from 'ramda';
import {
  labelDelete,
  labelRename,
  labelAskDelete,
  labelCancel,
  labelFilterDeleted,
  labelFilterUpdated,
  labelName,
  labelFilter,
} from '../../translatedLabels';
import { updateFilter, deleteFilter } from '../api';
import { Filter } from '../models';
import { useResourceContext } from '../../Context';
import ContentWithLoading from '../../ContentWithLoading';

const useStyles = makeStyles((theme) => ({
  filterCard: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    alignItems: 'center',
    gridTemplateColumns: '200px auto',
  },
  filterEditActions: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
  },
}));

interface Props {
  filter: Filter;
}

const EditFilterCard = ({ filter }: Props): JSX.Element => {
  const classes = useStyles();

  const { loadCustomFilters, customFilters } = useResourceContext();

  const { showMessage } = useSnackbar();

  const [deleting, setDeleting] = React.useState(false);

  const {
    sendRequest: sendUpdateFilterRequest,
    sending: sendingUpdateFilterRequest,
  } = useRequest({
    request: updateFilter,
  });

  const {
    sendRequest: sendDeleteFilterRequest,
    sending: sendingDeleteFilterRequest,
  } = useRequest({
    request: deleteFilter,
  });

  const { name, id } = filter;

  const form = useFormik({
    initialValues: {
      name,
    },
    onSubmit: (values) => {
      sendUpdateFilterRequest({ ...filter, name: values.name }).then(() => {
        showMessage({
          message: labelFilterUpdated,
          severity: Severity.success,
        });
        loadCustomFilters();
      });
    },
  });

  const askDelete = (): void => {
    setDeleting(true);
  };

  const confirmDelete = (): void => {
    setDeleting(false);

    sendDeleteFilterRequest(filter).then(() => {
      showMessage({ message: labelFilterDeleted, severity: Severity.success });
      loadCustomFilters();
    });
  };

  const cancelDelete = (): void => {
    setDeleting(false);
  };

  const loading = isNil(customFilters);
  const sendingRequest = or(
    sendingDeleteFilterRequest,
    sendingUpdateFilterRequest,
  );
  const canSave = and(form.isValid, form.dirty);

  return (
    <ContentWithLoading loading={loading}>
      <div className={classes.filterCard}>
        <TextField
          ariaLabel={`${labelFilter}-${id}-${labelName}`}
          value={form.values.name}
          onChange={form.handleChange('name') as (event) => void}
        />
        <div className={classes.filterEditActions}>
          <ContentWithLoading
            loading={sendingRequest}
            loadingIndicatorSize={24}
            alignCenter={false}
          >
            <>
              <IconButton title={labelDelete} onClick={askDelete}>
                <DeleteIcon fontSize="small" />
              </IconButton>
              {canSave && (
                <IconButton title={labelRename} onClick={form.submitForm}>
                  <SaveIcon fontSize="small" />
                </IconButton>
              )}
            </>
          </ContentWithLoading>
        </div>
        {deleting && (
          <ConfirmDialog
            labelConfirm={labelDelete}
            labelCancel={labelCancel}
            onConfirm={confirmDelete}
            labelTitle={labelAskDelete}
            onCancel={cancelDelete}
            open
          />
        )}
      </div>
    </ContentWithLoading>
  );
};

export default EditFilterCard;
