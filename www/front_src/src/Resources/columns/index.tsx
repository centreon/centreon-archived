import React from 'react';

import { Grid, Typography, makeStyles, IconButton } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';
import IconCheck from '@material-ui/icons/Sync';

import { TABLE_COLUMN_TYPES, StatusChip, SeverityCode } from '@centreon/ui';

import IconDowntime from './icons/Downtime';
import {
  labelResources,
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
  if (!row.severity) {
    return null;
  }
  return (
    <StatusChip
      label={row.severity.level.toString()}
      severityCode={SeverityCode.None}
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
  return (
    <Grid container spacing={0} alignItems="center">
      <Grid item>
        <IconButton
          size="small"
          color="primary"
          onClick={(): void => actions.onAcknowledge(row)}
          aria-label={`${labelAcknowledge} ${row.name}`}
        >
          <IconAcknowledge />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          size="small"
          color="primary"
          onClick={(): void => actions.onDowntime(row)}
          aria-label={`${labelSetDowntimeOn} ${row.name}`}
        >
          <IconDowntime />
        </IconButton>
      </Grid>
      <Grid item>
        <IconButton
          size="small"
          color="primary"
          onClick={(): void => actions.onCheck(row)}
          aria-label={`${labelCheck} ${row.name}`}
        >
          <IconCheck />
        </IconButton>
      </Grid>
      <Grid item>
        <StatusChip
          label={row.status.name[0]}
          severityCode={row.status.severity_code}
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
      style={{ width: 120 }}
      label={row.status.name}
      severityCode={row.status.severity_code}
    />
  );
};

const ResourcesColumn = ({ row }: ColumnProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid container spacing={1} className={classes.resourceDetailsCell}>
      <Grid item>
        {row.icon ? (
          <img src={row.icon.url} alt={row.icon.name} width={21} height={21} />
        ) : (
          <StatusChip label={row.short_type} severityCode={SeverityCode.None} />
        )}
      </Grid>
      <Grid item>
        <Typography>{row.name}</Typography>
      </Grid>
      {row.parent && (
        <Grid container spacing={1}>
          <Grid item xs={1} />
          <Grid item>
            <StatusChip severityCode={row.parent?.status?.severity_code || 0} />
          </Grid>
          <Grid item>{row.parent.name}</Grid>
        </Grid>
      )}
    </Grid>
  );
};

const getColumns = (actions): Array<Column> => [
  {
    id: 'severity',
    label: 'S',
    type: TABLE_COLUMN_TYPES.component,
    Component: SeverityColumn,
    sortable: false,
  },
  {
    id: 'status',
    label: labelStatus,
    type: TABLE_COLUMN_TYPES.component,
    Component: StatusColumn(actions),
    sortable: false,
    width: 125,
  },
  {
    id: 'resources',
    label: labelResources,
    type: TABLE_COLUMN_TYPES.component,
    Component: ResourcesColumn,
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
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ information }): string => information,
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
