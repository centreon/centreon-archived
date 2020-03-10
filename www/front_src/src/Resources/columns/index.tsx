import React from 'react';

import { Grid, Typography, makeStyles } from '@material-ui/core';

import { TABLE_COLUMN_TYPES, StatusChip, StatusCode } from '@centreon/ui';

import {
  labelResources,
  labelStatus,
  labelDuration,
  labelTries,
  labelInformation,
  labelState,
  labelLastCheck,
} from '../translatedLabels';
import { Resource } from '../models';
import StateColumn from './State';
import GraphColumn from './Graph';

const useStyles = makeStyles((theme) => ({
  resourceDetailsCell: {
    padding: theme.spacing(0.5),
  },
}));

export interface ColumnProps {
  row: Resource;
  Cell: ({ children, width }: { children; width? }) => JSX.Element;
  isRowSelected: boolean;
  style;
  onClick;
}

const SeverityColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  return (
    <Cell>
      {row.severity && (
        <StatusChip
          label={row.severity.level.toString()}
          statusCode={StatusCode.None}
        />
      )}
    </Cell>
  );
};

const StatusColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  return (
    <Cell>
      <StatusChip label={row.status.name} statusCode={row.status.code} />
    </Cell>
  );
};

const ResourcesColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Cell>
      <Grid container spacing={1} className={classes.resourceDetailsCell}>
        <Grid item>
          {row.icon ? (
            <img
              src={row.icon.url}
              alt={row.icon.name}
              width={21}
              height={21}
            />
          ) : (
            <StatusChip label={row.short_type} statusCode={StatusCode.None} />
          )}
        </Grid>
        <Grid item>
          <Typography>{row.name}</Typography>
        </Grid>
        {row.parent && (
          <Grid container spacing={1}>
            <Grid item xs={1} />
            <Grid item>
              <StatusChip statusCode={row.parent?.status?.code || 0} />
            </Grid>
            <Grid item>{row.parent.name}</Grid>
          </Grid>
        )}
      </Grid>
    </Cell>
  );
};

const columns = [
  {
    id: 'severity',
    label: 'S',
    type: TABLE_COLUMN_TYPES.component,
    Component: SeverityColumn,
    clickable: false,
    sortable: false,
  },
  {
    id: 'status',
    label: labelStatus,
    type: TABLE_COLUMN_TYPES.component,
    Component: StatusColumn,
    clickable: false,
    sortable: false,
  },
  {
    id: 'resources',
    label: labelResources,
    type: TABLE_COLUMN_TYPES.component,
    Component: ResourcesColumn,
    clickable: false,
    sortable: false,
  },
  {
    id: 'graph',
    label: '',
    type: TABLE_COLUMN_TYPES.component,
    Component: GraphColumn,
    clickable: false,
    sortable: false,
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
    clickable: false,
    sortable: false,
  },
];

export default columns;
