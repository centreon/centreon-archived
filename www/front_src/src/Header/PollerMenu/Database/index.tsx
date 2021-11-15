import React from 'react';

import { useTranslation } from 'react-i18next';
import * as yup from 'yup';
import { isNil } from 'ramda';

import { Avatar, makeStyles } from '@material-ui/core';

import { IconHeader } from '@centreon/ui';

import { labelDatabase } from '../translatedLabels';
import PollerMenu from '..';

const useStyles = makeStyles((theme) => ({
  icon: {
    backgroundColor: '#84BD00',
    display: 'flex',
    fontSize: theme.typography.body1.fontSize,
    height: theme.spacing(5),
    width: theme.spacing(5),
  },
}));

const databaseEndpoint =
  'internal.php?object=centreon_topcounter&action=pollersListIssues';

const numberFormat = yup.number().required().integer();

const statusSchema = yup.object().shape({
  critical: yup.object().shape({
    total: numberFormat,
  }),

  warning: yup.object().shape({
    total: numberFormat,
  }),
});

const DatabaseIcon = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const databaseClassOk =
    isNil(data.issue.database.warning) && isNil(issue.database.critical);

  const databaseClassIssue = !databaseClassOk;

  return (
    <PollerMenu
      endpoint={databaseEndpoint}
      loaderWidth={27}
      schema={statusSchema}
    >
      {({ data, issue, toggleDetailedView, toggled }): JSX.Element => (
        <span>
          <Avatar className={classes.icon}>
            <IconHeader Icon={DatabaseIcon} iconName={t(labelDatabase)} />
          </Avatar>
        </span>
      )}
      ;
    </PollerMenu>
  );
};

export default DatabaseIcon;
