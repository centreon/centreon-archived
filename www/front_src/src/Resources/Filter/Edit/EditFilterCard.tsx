import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import {
  all,
  equals,
  any,
  reject,
  update,
  findIndex,
  propEq,
  omit,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import DeleteIcon from '@material-ui/icons/Delete';
import { makeStyles } from '@material-ui/core';

import {
  ContentWithCircularLoading,
  TextField,
  IconButton,
  useRequest,
  ConfirmDialog,
  useSnackbar,
  Severity,
} from '@centreon/ui';

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
import { Filter } from '../models';
import { useResourceContext } from '../../Context';
import useFilterModels from '../useFilterModels';
import useAdapters from '../api/adapters';

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

  const { newFilter } = useFilterModels();
  const { toRawFilter } = useAdapters();
  const { t } = useTranslation();
  const {
    setFilter,
    filter: currentFilter,
    setCustomFilters,
    customFilters,
    sortf,
    sorto,
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
    name: Yup.string().required(t(labelNameCannotBeEmpty)),
  });

  const form = useFormik({
    enableReinitialize: true,
    initialValues: {
      name,
    },
    validationSchema,
    onSubmit: (values) => {
      const updatedFilter = { ...filter, name: values.name };

      sendUpdateFilterRequest({
        rawFilter: omit(['id'], toRawFilter(updatedFilter)),
        id: updatedFilter.id,
      }).then(() => {
        showMessage({
          message: t(labelFilterUpdated),
          severity: Severity.success,
        });

        if (equals(updatedFilter.id, currentFilter.id)) {
          setFilter(updatedFilter);
        }

        const index = findIndex(propEq('id', updatedFilter.id), customFilters);

        setCustomFilters(update(index, updatedFilter, customFilters));
      });
    },
  });

  const askDelete = (): void => {
    setDeleting(true);
  };

  const confirmDelete = (): void => {
    setDeleting(false);

    sendDeleteFilterRequest(filter).then(() => {
      showMessage({
        message: t(labelFilterDeleted),
        severity: Severity.success,
      });

      if (equals(filter.id, currentFilter.id)) {
        setFilter({ ...newFilter, sort: [sortf, sorto] } as Filter);
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
        loading={sendingRequest}
        loadingIndicatorSize={24}
        alignCenter={false}
      >
        <IconButton title={t(labelDelete)} onClick={askDelete}>
          <DeleteIcon fontSize="small" />
        </IconButton>
      </ContentWithCircularLoading>
      <TextField
        className={classes.filterNameInput}
        ariaLabel={`${t(labelFilter)}-${id}-${t(labelName)}`}
        value={form.values.name}
        error={form.errors.name}
        onChange={form.handleChange('name') as (event) => void}
        onKeyDown={renameOnEnterKey}
        onBlur={rename}
        transparent
      />

      {deleting && (
        <ConfirmDialog
          labelConfirm={t(labelDelete)}
          labelCancel={t(labelCancel)}
          onConfirm={confirmDelete}
          labelTitle={t(labelAskDelete)}
          onCancel={cancelDelete}
          open
        />
      )}
    </div>
  );
};

export default EditFilterCard;
