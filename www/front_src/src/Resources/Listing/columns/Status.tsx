import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { pathEq } from 'ramda';

import { makeStyles } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import {
  ComponentColumnProps,
  SeverityCode,
  StatusChip,
  IconButton,
} from '@centreon/ui';

import useAclQuery from '../../Actions/Resource/aclQuery';
import IconDowntime from '../../icons/Downtime';
import {
  labelAcknowledge,
  labelCheck,
  labelSetDowntime,
  labelSetDowntimeOn,
} from '../../translatedLabels';

import { ColumnProps } from '.';

const useStyles = makeStyles((theme) => ({
  actions: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'nowrap',
    gridGap: theme.spacing(0.75),
    justifyContent: 'center',
  },
  statusColumn: {
    alignItems: 'center',
    display: 'flex',
    width: '100%',
  },
}));

type StatusColumnProps = {
  actions;
} & Pick<ComponentColumnProps, 'row'>;

const StatusColumnOnHover = ({
  actions,
  row,
}: StatusColumnProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { canAcknowledge, canDowntime, canCheck } = useAclQuery();

  const isResourceOk = pathEq(
    ['status', 'severity_code'],
    SeverityCode.Ok,
    row,
  );

  const disableAcknowledge = !canAcknowledge([row]) || isResourceOk;
  const disableDowntime = !canDowntime([row]);
  const disableCheck = !canCheck([row]);

  return (
    <div className={classes.actions}>
      <IconButton
        ariaLabel={`${t(labelAcknowledge)} ${row.name}`}
        color="primary"
        disabled={disableAcknowledge}
        title={t(labelAcknowledge)}
        onClick={(): void => actions.onAcknowledge(row)}
      >
        <IconAcknowledge fontSize="small" />
      </IconButton>
      <IconButton
        ariaLabel={`${t(labelSetDowntimeOn)} ${row.name}`}
        disabled={disableDowntime}
        title={t(labelSetDowntime)}
        onClick={(): void => actions.onDowntime(row)}
      >
        <IconDowntime fontSize="small" />
      </IconButton>
      <IconButton
        ariaLabel={`${t(labelCheck)} ${row.name}`}
        disabled={disableCheck}
        title={t(labelCheck)}
        onClick={(): void => actions.onCheck(row)}
      >
        <IconCheck fontSize="small" />
      </IconButton>
    </div>
  );
};

const StatusColumn = ({
  actions,
  t,
}: ColumnProps): ((props: ComponentColumnProps) => JSX.Element) => {
  const Status = ({ row, isHovered }: ComponentColumnProps): JSX.Element => {
    const classes = useStyles();

    const statusName = row.status.name;

    return (
      <div className={classes.statusColumn}>
        {isHovered ? (
          <StatusColumnOnHover actions={actions} row={row} />
        ) : (
          <StatusChip
            label={t(statusName)}
            severityCode={row.status.severity_code}
            style={{ height: 20, width: '100%' }}
          />
        )}
      </div>
    );
  };

  return Status;
};

export default StatusColumn;
