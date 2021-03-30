import React from 'react';

import { pipe, split, head, propOr, T, pathEq } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Typography, makeStyles } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import {
  ColumnType,
  StatusChip,
  SeverityCode,
  IconButton,
  Column,
  ComponentColumnProps,
} from '@centreon/ui';

import IconDowntime from '../../icons/Downtime';
import {
  labelResource,
  labelStatus,
  labelDuration,
  labelTries,
  labelInformation,
  labelState,
  labelLastCheck,
  labelAcknowledge,
  labelSetDowntimeOn,
  labelCheck,
  labelSetDowntime,
  labelParent,
} from '../../translatedLabels';
import useAclQuery from '../../Actions/Resource/aclQuery';
import truncate from '../../truncate';

import StateColumn from './State';
import GraphColumn from './Graph';
import NotesUrlColumn from './Url/Notes';
import ActionUrlColumn from './Url/Action';

const useStyles = makeStyles((theme) => ({
  resourceDetailsCell: {
    padding: theme.spacing(0, 0.5),
    display: 'flex',
    flexWrap: 'nowrap',
    alignItems: 'center',
  },
  resourceNameItem: {
    marginLeft: theme.spacing(1),
    whiteSpace: 'nowrap',
  },
  extraSmallChipContainer: {
    height: 19,
  },
  smallChipContainer: {
    height: theme.spacing(2.5),
    width: theme.spacing(2.5),
    fontSize: 10,
  },
  smallChipLabel: {
    padding: theme.spacing(0.5),
  },
  statusColumn: {
    display: 'flex',
    alignItems: 'center',
    width: '100%',
  },
  actions: {
    display: 'flex',
    flexWrap: 'nowrap',
    gridGap: theme.spacing(0.75),
    alignItems: 'center',
    justifyContent: 'center',
  },
}));

const SeverityColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const classes = useStyles();

  if (!row.severity_level) {
    return null;
  }

  return (
    <StatusChip
      label={row.severity_level?.toString()}
      severityCode={SeverityCode.None}
      classes={{
        root: classes.extraSmallChipContainer,
        label: classes.smallChipLabel,
      }}
    />
  );
};

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
        title={t(labelAcknowledge)}
        disabled={disableAcknowledge}
        color="primary"
        onClick={(): void => actions.onAcknowledge(row)}
        ariaLabel={`${t(labelAcknowledge)} ${row.name}`}
      >
        <IconAcknowledge fontSize="small" />
      </IconButton>
      <IconButton
        title={t(labelSetDowntime)}
        disabled={disableDowntime}
        onClick={(): void => actions.onDowntime(row)}
        ariaLabel={`${t(labelSetDowntimeOn)} ${row.name}`}
      >
        <IconDowntime fontSize="small" />
      </IconButton>
      <IconButton
        title={t(labelCheck)}
        disabled={disableCheck}
        onClick={(): void => actions.onCheck(row)}
        ariaLabel={`${t(labelCheck)} ${row.name}`}
      >
        <IconCheck fontSize="small" />
      </IconButton>
    </div>
  );
};

const StatusColumn = ({ actions, t }) => {
  const Status = ({ row, isHovered }: ComponentColumnProps): JSX.Element => {
    const classes = useStyles();

    const statusName = row.status.name;

    return (
      <div className={classes.statusColumn}>
        {isHovered ? (
          <StatusColumnOnHover actions={actions} row={row} />
        ) : (
          <StatusChip
            style={{ height: 20, width: '100%' }}
            label={t(statusName)}
            severityCode={row.status.severity_code}
          />
        )}
      </div>
    );
  };

  return Status;
};

const ResourceColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.resourceDetailsCell}>
      {row.icon ? (
        <img src={row.icon.url} alt={row.icon.name} width={16} height={16} />
      ) : (
        <StatusChip
          label={row.short_type}
          severityCode={SeverityCode.None}
          classes={{
            root: classes.extraSmallChipContainer,
            label: classes.smallChipLabel,
          }}
        />
      )}
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.name}</Typography>
      </div>
    </div>
  );
};

const ParentResourceColumn = ({
  row,
}: ComponentColumnProps): JSX.Element | null => {
  const classes = useStyles();

  if (!row.parent) {
    return null;
  }

  return (
    <div className={classes.resourceDetailsCell}>
      <StatusChip severityCode={row.parent?.status?.severity_code || 0} />
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.parent.name}</Typography>
      </div>
    </div>
  );
};

interface ColumnsProps {
  actions;
  t: (value: string) => string;
}

export const getColumns = ({ actions, t }: ColumnsProps): Array<Column> => [
  {
    id: 'severity',
    label: 'S',
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: SeverityColumn,
    sortField: 'severity_level',
  },
  {
    id: 'status',
    label: t(labelStatus),
    type: ColumnType.component,
    Component: StatusColumn({ actions, t }),
    hasHoverableComponent: true,
    getRenderComponentOnRowUpdateCondition: T,
    sortField: 'status_severity_code',
    clickable: true,
    width: 'minmax(100px, max-content)',
  },
  {
    id: 'resource',
    label: t(labelResource),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ResourceColumn,
    sortField: 'name',
  },
  {
    id: 'parent_resource',
    label: t(labelParent),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ParentResourceColumn,
    sortField: 'parent_name',
  },
  {
    id: 'notes_url',
    label: '',
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: NotesUrlColumn,
    sortable: false,
  },
  {
    id: 'action_url',
    label: '',
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: ActionUrlColumn,
    sortable: false,
  },
  {
    id: 'graph',
    label: '',
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: GraphColumn({ onClick: actions.onDisplayGraph }),
    sortable: false,
  },
  {
    id: 'duration',
    label: t(labelDuration),
    type: ColumnType.string,
    getFormattedString: ({ duration }): string => duration,
    sortField: 'last_status_change',
  },
  {
    id: 'tries',
    label: t(labelTries),
    type: ColumnType.string,
    getFormattedString: ({ tries }): string => tries,
  },
  {
    id: 'last_check',
    label: t(labelLastCheck),
    type: ColumnType.string,
    getFormattedString: ({ last_check }): string => last_check,
  },
  {
    id: 'information',
    label: t(labelInformation),
    type: ColumnType.string,
    sortable: false,
    width: '1fr',
    getFormattedString: pipe(
      propOr('', 'information'),
      split('\n'),
      head,
      truncate,
    ) as (row) => string,
  },
  {
    id: 'state',
    label: t(labelState),
    type: ColumnType.component,
    getRenderComponentOnRowUpdateCondition: T,
    Component: StateColumn,
    sortable: false,
  },
];
