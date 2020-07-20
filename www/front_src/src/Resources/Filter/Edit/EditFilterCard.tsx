import * as React from 'react';

import { makeStyles } from '@material-ui/core';
import DeleteIcon from '@material-ui/icons/Delete';

import {
  ContentWithCircularLoading,
  TextField,
  IconButton,
  useRequest,
  ConfirmDialog,
  useSnackbar,
  Severity,
} from '@centreon/ui';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { not, all, equals, any, reject } from 'ramda';

import {
  labelDelete,
  labelAskDelete,
  labelCancel,
  labelFilterDeleted,
  labelFilterUpdated,
  labelName,
  labelFilter,
  labelNameCannotBeEmpty,
} from '../../translatedLabels';
import { updateFilter, deleteFilter } from '../api';
import { Filter, newFilter } from '../models';
import { useResourceContext } from '../../Context';

const useStyles = makeStyles((theme) => ({
  filterCard: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    alignItems: 'center',
    gridTemplateColumns: 'auto 1fr',
  },
  filterNameInput: {},
}));

interface Props {
  filter: Filter;
}

const EditFilterCard = ({ filter }: Props): JSX.Element => {
  const classes = useStyles();

  const {
    setFilter,
    filter: currentFilter,
    loadCustomFilters,
    setCustomFilters,
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
    enableReinitialize: true,
    initialValues: {
      name,
    },
    validationSchema,
    onSubmit: (values) => {
      sendUpdateFilterRequest({ ...filter, name: values.name }).then(
        (updatedFilter) => {
          if (equals(updatedFilter.id, currentFilter.id)) {
            setFilter(updatedFilter);
          }

          showMessage({
            message: labelFilterUpdated,
            severity: Severity.success,
          });

          loadCustomFilters();
        },
      );
    },
  });

  const askDelete = (): void => {
    setDeleting(true);
  };

  const confirmDelete = (): void => {
    setDeleting(false);

    sendDeleteFilterRequest(filter).then(() => {
      showMessage({ message: labelFilterDeleted, severity: Severity.success });

      if (equals(filter.id, currentFilter.id)) {
        setFilter(newFilter as Filter);
      }

      setCustomFilters(reject(equals(filter), customFilters as Array<Filter>));
    });
  };

  const cancelDelete = (): void => {
    setDeleting(false);
  };

  const sendingRequest = any(equals(true), [
    sendingDeleteFilterRequest,
    sendingUpdateFilterRequest,
  ]);

  const canRename = all(equals(true), [
    form.isValid,
    form.dirty,
    not(sendingListCustomFiltersRequest),
  ]);

  const rename = (): void => {
    if (!canRename) {
      return;
    }

    form.submitForm();
  };

  const renameOnEnterKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      rename();
    }
  };

  return (
    <div className={classes.filterCard}>
      <ContentWithCircularLoading
        loading={sendingRequest}
        loadingIndicatorSize={24}
        alignCenter={false}
      >
        <IconButton title={labelDelete} onClick={askDelete}>
          <DeleteIcon fontSize="small" />
        </IconButton>
      </ContentWithCircularLoading>
      <TextField
        className={classes.filterNameInput}
        ariaLabel={`${labelFilter}-${id}-${labelName}`}
        value={form.values.name}
        error={form.errors.name}
        onChange={form.handleChange('name') as (event) => void}
        onKeyDown={renameOnEnterKey}
        onBlur={rename}
        transparent
      />

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
  );
};

export default EditFilterCard;
