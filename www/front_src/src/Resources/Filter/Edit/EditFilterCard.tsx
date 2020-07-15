import * as React from 'react';

import { Typography, CircularProgress, makeStyles } from '@material-ui/core';
import EditIcon from '@material-ui/icons/Edit';
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

import { useFormik, Formik } from 'formik';
import { or, isNil } from 'ramda';
import {
  labelDelete,
  labelRename,
  labelAskDelete,
  labelCancel,
  labelFilterDeleted,
  labelFilterUpdated,
  labelAskDelete,
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

  const loading = or(sendingDeleteFilterRequest, sendingUpdateFilterRequest);

  return (
    <ContentWithLoading loading={isNil(customFilters)}>
      <div className={classes.filterCard}>
        <TextField
          value={form.values.name}
          onChange={form.handleChange('name')}
        />
        <div className={classes.filterEditActions}>
          {loading && <CircularProgress size={24} />}
          {!loading && (
            <>
              <IconButton title={labelDelete} onClick={askDelete}>
                <DeleteIcon fontSize="small" />
              </IconButton>
              {form.isValid && form.dirty && (
                <IconButton title={labelRename} onClick={form.submitForm}>
                  <SaveIcon fontSize="small" />
                </IconButton>
              )}
            </>
          )}
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
