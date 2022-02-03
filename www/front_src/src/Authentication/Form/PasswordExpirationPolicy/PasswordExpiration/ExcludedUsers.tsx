import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import { equals, has, inc, map, pluck } from 'ramda';
import { filter } from 'domutils';

import { IconButton, ListItemText, Tooltip } from '@mui/material';
import { makeStyles } from '@mui/styles';
import SupervisorAccountIcon from '@mui/icons-material/SupervisorAccount';

import {
  MultiConnectedAutocompleteField,
  buildListingEndpoint,
} from '@centreon/ui';

import { labelAdmin, labelExcludedUsers } from '../../../translatedLabels';
import { getField } from '../../utils';
import { Contact } from '../../../models';
import { contactsEndpoint } from '../../../api/endpoints';

const excludedUsersFieldName = 'passwordExpiration.excludedUsers';

const useStyles = makeStyles((theme) => ({
  option: {
    alignItems: 'center',
    display: 'flex',
    width: theme.spacing(25),
  },
  tooltip: {
    zIndex: inc(theme.zIndex.tooltip),
  },
}));

const ExcludedUsers = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { values, setFieldValue } = useFormikContext<FormikValues>();

  const getEndpoint = (parameters): string =>
    buildListingEndpoint({
      baseEndpoint: contactsEndpoint,
      parameters,
    });

  const getRenderedOptionText = React.useCallback((option): JSX.Element => {
    const { alias, email, is_admin: isAdmin } = option;

    return (
      <div className={classes.option}>
        <ListItemText primary={alias} secondary={email} />
        {isAdmin ? (
          <Tooltip
            classes={{
              popper: classes.tooltip,
            }}
            placement="top"
            title={t(labelAdmin) as string}
          >
            <IconButton edge="end" size="small">
              <SupervisorAccountIcon fontSize="small" />
            </IconButton>
          </Tooltip>
        ) : undefined}
      </div>
    );
  }, []);

  const change = (_, newExcludedUsers: Array<Contact>): void => {
    setFieldValue(excludedUsersFieldName, pluck('alias', newExcludedUsers));
  };

  const isOptionEqualToValue = (option: Contact, value: Contact): boolean =>
    equals(option.alias, value.alias);

  const filterOptions = (options): Array<unknown> =>
    filter((option) => has('email', option), options);

  const excludedUsers = getField<Array<string>>({
    field: excludedUsersFieldName,
    object: values,
  });

  const formattedUsers = React.useMemo(
    () => map((user) => ({ alias: user, id: user, name: user }), excludedUsers),
    [excludedUsers],
  );

  return (
    <MultiConnectedAutocompleteField
      disableClearable={false}
      field="alias"
      filterOptions={filterOptions}
      getEndpoint={getEndpoint}
      getOptionLabel={(option): string => option.alias}
      getRenderedOptionText={getRenderedOptionText}
      isOptionEqualToValue={isOptionEqualToValue}
      label={t(labelExcludedUsers)}
      name="excludedUsers"
      size="small"
      value={formattedUsers}
      onChange={change}
    />
  );
};

export default ExcludedUsers;
