import React from 'react';

import { pipe, split, head, propOr } from 'ramda';

import { Grid, Typography, makeStyles } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import { ColumnType, StatusChip, SeverityCode, IconButton } from '@centreon/ui';

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
} from '../../translatedLabels';
import { Resource } from '../../models';
import StateColumn from './State';
import GraphColumn from './Graph';
import useAclQuery from '../../Actions/Resource/aclQuery';

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
    height: 18,
  },
  smallChipLabel: {
    padding: theme.spacing(0.5),
  },
}));

export interface Column {
  id: string;
  getFormattedString?: (details) => string;
  label: string;
  type: number;
  Component?: (props) => JSX.Element | null;
  sortable?: boolean;
  sortField?: string;
  clickable?: boolean;
  width?: number;
}

export interface ColumnProps {
  row: Resource;
  isRowSelected: boolean;
  isHovered: boolean;
  style;
  onClick;
}

const SeverityColumn = ({ row }: ColumnProps): JSX.Element | null => {
  const classes = useStyles();

  if (!row.severity) {
    return null;
  }

  return (
    <StatusChip
      label={row.severity.level.toString()}
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
} & Pick<ColumnProps, 'row'>;

const StatusColumnOnHover = ({
  actions,
  row,
}: StatusColumnProps): JSX.Element => {
  const classes = useStyles();
  const { canAcknowledge, canDowntime, canCheck } = useAclQuery();

  const disableAcknowledge = !canAcknowledge([row]);
  const disableDowntime = !canDowntime([row]);
  const disableCheck = !canCheck([row]);

  return (
    <Grid container spacing={1} alignItems="center">
      <Grid item>
        <IconButton
          title={labelAcknowledge}
          disabled={disableAcknowledge}
          color="primary"
          onClick={(): void => actions.onAcknowledge(row)}
          ariaLabel={`${labelAcknowledge} ${row.name}`}
        >
          <IconAcknowledge fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          title={labelSetDowntime}
          disabled={disableDowntime}
          onClick={(): void => actions.onDowntime(row)}
          ariaLabel={`${labelSetDowntimeOn} ${row.name}`}
        >
          <IconDowntime fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          title={labelCheck}
          disabled={disableCheck}
          onClick={(): void => actions.onCheck(row)}
          ariaLabel={`${labelCheck} ${row.name}`}
        >
          <IconCheck fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <StatusChip
          label={row.status.name[0]}
          severityCode={row.status.severity_code}
          classes={{
            root: classes.smallChipContainer,
            label: classes.smallChipLabel,
          }}
        />
      </Grid>
    </Grid>
  );
};

const StatusColumn = (actions) => ({
  row,
  isHovered,
}: ColumnProps): JSX.Element => {
  return isHovered ? (
    <StatusColumnOnHover actions={actions} row={row} />
  ) : (
    <StatusChip
      style={{ width: 100, height: 20, margin: 2 }}
      label={row.status.name}
      severityCode={row.status.severity_code}
    />
  );
};

const ResourceColumn = ({ row }: ColumnProps): JSX.Element => {
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

const ParentResourceColumn = ({ row }: ColumnProps): JSX.Element | null => {
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

export const getColumns = ({ actions, t }): Array<Column> => [
  {
    id: 'severity',
    label: 'S',
    type: ColumnType.component,
    Component: SeverityColumn,
    sortField: 'severity_level',
    width: 50,
  },
  {
    id: 'status',
    label: t(labelStatus),
    type: ColumnType.component,
    Component: StatusColumn(actions),
    sortField: 'status_severity_code',
    clickable: true,
    width: 145,
  },
  {
    id: 'resource',
    label: t(labelResource),
    type: ColumnType.component,
    Component: ResourceColumn,
    sortField: 'name',
    width: 200,
  },
  {
    id: 'parent_resource',
    label: '',
    type: ColumnType.component,
    Component: ParentResourceColumn,
    sortable: false,
    width: 200,
  },
  {
    id: 'graph',
    label: '',
    type: ColumnType.component,
    Component: GraphColumn({ onClick: actions.onDisplayGraph }),
    sortable: false,
    width: 50,
  },
  {
    id: 'duration',
    label: t(labelDuration),
    type: ColumnType.string,
    getFormattedString: ({ duration }): string => duration,
    sortField: 'last_status_change',
    width: 125,
  },
  {
    id: 'tries',
    label: t(labelTries),
    type: ColumnType.string,
    getFormattedString: ({ tries }): string => tries,
    width: 125,
  },
  {
    id: 'last_check',
    label: t(labelLastCheck),
    type: ColumnType.string,
    getFormattedString: ({ last_check }): string => last_check,
    width: 125,
  },
  {
    id: 'information',
    label: t(labelInformation),
    type: ColumnType.string,
    getFormattedString: pipe(propOr('', 'information'), split('\n'), head) as (
      row,
    ) => string,
  },
  {
    id: 'state',
    label: t(labelState),
    type: ColumnType.component,
    Component: StateColumn,
    sortable: false,
    width: 80,
  },
];

export const defaultSortField = 'status_severity_code';
export const defaultSortOrder = 'asc';
