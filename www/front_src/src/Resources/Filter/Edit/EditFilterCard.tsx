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
import { isNil, not, all, equals, any } from 'ramda';

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
    setFilter,
    filter: currentFilter,
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

      loadCustomFilters();
    });
  };

  const cancelDelete = (): void => {
    setDeleting(false);
  };

  const customFiltersEmpty = isNil(customFilters);
  const sendingRequest = any(equals(true), [
    sendingDeleteFilterRequest,
    sendingUpdateFilterRequest,
    sendingListCustomFiltersRequest,
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
    <ContentWithCircularLoading loading={loading}>
      <div className={classes.filterCard}>
        <TextField
          className={classes.filterNameInput}
          ariaLabel={`${labelFilter}-${id}-${labelName}`}
          value={form.values.name}
          error={form.errors.name}
          onChange={form.handleChange('name') as (event) => void}
          onKeyDown={renameOnEnterKey}
          onBlur={rename}
        />
        <div className={classes.filterEditActions}>
          <ContentWithCircularLoading
            loading={sendingRequest}
            loadingIndicatorSize={24}
            alignCenter={false}
          >
            <IconButton title={labelDelete} onClick={askDelete}>
              <DeleteIcon fontSize="small" />
            </IconButton>
          </ContentWithCircularLoading>
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
    </ContentWithCircularLoading>
  );
};

export default EditFilterCard;
