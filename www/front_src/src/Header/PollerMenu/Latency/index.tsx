import React from 'react';

import * as yup from 'yup';
import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import LatencyIcon from '@material-ui/icons/Speed';
import { Avatar, makeStyles } from '@material-ui/core';

import { IconHeader, SubmenuHeader } from '@centreon/ui';

import { labelLatency } from '../translatedLabels';
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

const latencyEndpoint =
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

const LatencyStatus = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const issue = !isNil(data);

  return (
    <PollerMenu
      endpoint={latencyEndpoint}
      loaderWidth={27}
      schema={statusSchema}
    >
      {({ data, toggleDetailedView, toggled }): JSX.Element => (
        <div>
        <SubmenuHeader active={toggled}>
          <Avatar className={classes.icon} onClick={toggleDetailedView}>
            <IconHeader Icon={LatencyIcon} iconName={t(labelLatency)} />
          </Avatar>
          </div>
          </SubmenuHeader>
      )}
      ;
    </PollerMenu>
  );
};

export default LatencyStatus;
