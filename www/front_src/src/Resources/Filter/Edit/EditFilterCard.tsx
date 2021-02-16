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
import { ResourceContext, useResourceContext } from '../../Context';
import memoizeComponent from '../../memoizedComponent';

const useStyles = makeStyles((theme) => ({
  filterCard: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    alignItems: 'center',
    gridTemplateColumns: 'auto 1fr',
  },
}));

interface EditFilterCardProps {
  filter: Filter;
}

interface Props
  extends EditFilterCardProps,
    Pick<
      ResourceContext,
      'customFilters' | 'setFilter' | 'setCustomFilters' | 'setNewFilter'
    > {
  currentFilter: Filter;
}

const EditFilterCardContent = ({
  filter,
  currentFilter,
  customFilters,
  setFilter,
  setCustomFilters,
  setNewFilter,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

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
        filter: omit(['id'], updatedFilter),
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
        setNewFilter();
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

const memoProps = ['filter', 'currentFilter', 'customFilters'];

const MemoizedEditFilterCardContent = memoizeComponent<Props>({
  memoProps,
  Component: EditFilterCardContent,
});

const EditFilterCard = ({ filter }: EditFilterCardProps): JSX.Element => {
  const {
    setFilter,
    filter: currentFilter,
    setCustomFilters,
    customFilters,
    setNewFilter,
  } = useResourceContext();

  return (
    <MemoizedEditFilterCardContent
      filter={filter}
      currentFilter={currentFilter}
      customFilters={customFilters}
      setFilter={setFilter}
      setCustomFilters={setCustomFilters}
      setNewFilter={setNewFilter}
    />
  );
};

export default EditFilterCard;
