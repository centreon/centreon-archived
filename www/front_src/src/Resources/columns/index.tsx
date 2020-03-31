import React from 'react';

import { Grid, Typography, makeStyles, IconButton } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import { TABLE_COLUMN_TYPES, StatusChip, SeverityCode } from '@centreon/ui';

import IconDowntime from '../icons/Downtime';
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
} from '../translatedLabels';
import { Resource } from '../models';
import StateColumn from './State';
import GraphColumn from './Graph';

const useStyles = makeStyles((theme) => ({
  resourceDetailsCell: {
    padding: theme.spacing(0, 0.5),
  },
  resourceNameItem: {
    display: 'flex',
    alignItems: 'center',
    paddingLeft: theme.spacing(2),
  },
  iconButton: {
    padding: 0,
  },
  extraSmallChipContainer: {
    height: 16,
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

  return (
    <Grid container spacing={1} alignItems="center">
      <Grid item>
        <IconButton
          className={classes.iconButton}
          color="primary"
          onClick={(): void => actions.onAcknowledge(row)}
          aria-label={`${labelAcknowledge} ${row.name}`}
        >
          <IconAcknowledge fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          className={classes.iconButton}
          color="primary"
          onClick={(): void => actions.onDowntime(row)}
          aria-label={`${labelSetDowntimeOn} ${row.name}`}
        >
          <IconDowntime fontSize="small" />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          className={classes.iconButton}
          color="primary"
          onClick={(): void => actions.onCheck(row)}
          aria-label={`${labelCheck} ${row.name}`}
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
      style={{ width: 100, height: 20, margin: 0 }}
      label={row.status.name}
      severityCode={row.status.severity_code}
    />
  );
};

const ResourceColumn = ({ row }: ColumnProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid container spacing={0} className={classes.resourceDetailsCell}>
      <Grid item>
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
      </Grid>
      <Grid item className={classes.resourceNameItem}>
        <Typography variant="body2">{row.name}</Typography>
      </Grid>
    </Grid>
  );
};

const ParentResourceColumn = ({ row }: ColumnProps): JSX.Element | null => {
  if (!row.parent) {
    return null;
  }

  return (
    <Grid container spacing={1}>
      <Grid item xs={1} />
      <Grid item>
        <StatusChip severityCode={row.parent?.status?.severity_code || 0} />
      </Grid>
      <Grid item>
        <Typography variant="body2">{row.parent.name}</Typography>
      </Grid>
    </Grid>
  );
};

const InformationColumn = ({ row }: ColumnProps): JSX.Element | null => {
  return (
    <Typography variant="body2" noWrap style={{ maxWidth: 400 }}>
      {row.information || ''}
    </Typography>
  );
};

const getColumns = (actions): Array<Column> => [
  {
    id: 'severity',
    label: 'S',
    type: TABLE_COLUMN_TYPES.component,
    Component: SeverityColumn,
    sortField: 'severity_level',
  },
  {
    id: 'status',
    label: labelStatus,
    type: TABLE_COLUMN_TYPES.component,
    Component: StatusColumn(actions),
    sortField: 'severity_code',
    clickable: true,
    width: 125,
  },
  {
    id: 'resource',
    label: labelResource,
    type: TABLE_COLUMN_TYPES.component,
    Component: ResourceColumn,
    sortField: 'name',
  },
  {
    id: 'parent_resource',
    label: '',
    type: TABLE_COLUMN_TYPES.component,
    Component: ParentResourceColumn,
    sortable: false,
  },
  {
    id: 'graph',
    label: '',
    type: TABLE_COLUMN_TYPES.component,
    Component: GraphColumn,
    sortable: false,
    width: 50,
  },
  {
    id: 'duration',
    label: labelDuration,
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ duration }): string => duration,
  },
  {
    id: 'tries',
    label: labelTries,
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ tries }): string => tries,
  },
  {
    id: 'last_check',
    label: labelLastCheck,
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ last_check }): string => last_check,
  },
  {
    id: 'information',
    label: labelInformation,
    type: TABLE_COLUMN_TYPES.component,
    Component: InformationColumn,
    width: 400,
  },
  {
    id: 'state',
    label: labelState,
    type: TABLE_COLUMN_TYPES.component,
    Component: StateColumn,
    sortable: false,
    width: 80,
  },
];

export default getColumns;
