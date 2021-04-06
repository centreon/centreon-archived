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
import { all, equals, any, reject, update, findIndex, propEq } from 'ramda';

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
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
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
    setCustomFilters,
    customFilters,
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
    onSubmit: (values) => {
      const updatedFilter = { ...filter, name: values.name };

      sendUpdateFilterRequest(updatedFilter).then(() => {
        showMessage({
          message: labelFilterUpdated,
          severity: Severity.success,
        });

        if (equals(updatedFilter.id, currentFilter.id)) {
          setFilter(updatedFilter);
        }

        const index = findIndex(propEq('id', updatedFilter.id), customFilters);

        setCustomFilters(update(index, updatedFilter, customFilters));
      });
    },
    validationSchema,
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

      setCustomFilters(reject(equals(filter), customFilters));
    });
  };

  const cancelDelete = (): void => {
    setDeleting(false);
  };

  const sendingRequest = any(equals(true), [
    sendingDeleteFilterRequest,
    sendingUpdateFilterRequest,
  ]);

  const canRename = all(equals(true), [form.isValid, form.dirty]);

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
        alignCenter={false}
        loading={sendingRequest}
        loadingIndicatorSize={24}
      >
        <IconButton title={labelDelete} onClick={askDelete}>
          <DeleteIcon fontSize="small" />
        </IconButton>
      </ContentWithCircularLoading>
      <TextField
        transparent
        ariaLabel={`${labelFilter}-${id}-${labelName}`}
        className={classes.filterNameInput}
        error={form.errors.name}
        value={form.values.name}
        onBlur={rename}
        onChange={form.handleChange('name') as (event) => void}
        onKeyDown={renameOnEnterKey}
      />

      {deleting && (
        <ConfirmDialog
          open
          labelCancel={labelCancel}
          labelConfirm={labelDelete}
          labelTitle={labelAskDelete}
          onCancel={cancelDelete}
          onConfirm={confirmDelete}
        />
      )}
    </div>
  );
};

export default EditFilterCard;
