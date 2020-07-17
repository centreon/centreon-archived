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
import * as Yup from 'yup';
import { or, isNil, not, all, equals } from 'ramda';

import {
  labelDelete,
  labelRename,
  labelAskDelete,
  labelCancel,
  labelFilterDeleted,
  labelFilterUpdated,
  labelName,
  labelFilter,
  labelRequired,
  labelNameCannotBeEmpty,
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
    gridTemplateColumns: '2fr 1fr',
  },
  filterNameInput: {},
  filterEditActions: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
    justifyContent: 'flex-start',
  },
}));

interface Props {
  filter: Filter;
}

const EditFilterCard = ({ filter }: Props): JSX.Element => {
  const classes = useStyles();

  const {
    loadCustomFilters,
    customFilters,
    sendingListCustomFiltersRequest,
  } = useResourceContext();

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

  const validationSchema = Yup.object().shape({
    name: Yup.string().required(labelNameCannotBeEmpty),
  });

  const form = useFormik({
    initialValues: {
      name,
    },
    validationSchema,
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
  const canSave = all(equals(true), [
    form.isValid,
    form.dirty,
    not(sendingListCustomFiltersRequest),
  ]);

  return (
    <ContentWithLoading loading={loading}>
      <div className={classes.filterCard}>
        <TextField
          className={classes.filterNameInput}
          ariaLabel={`${labelFilter}-${id}-${labelName}`}
          value={form.values.name}
          error={form.errors.name}
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
