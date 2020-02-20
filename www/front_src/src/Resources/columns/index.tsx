import React from 'react';

import { distanceInWordsStricts } from 'date-fns/esm';

import { TABLE_COLUMN_TYPES, StatusChip, StatusCode } from '@centreon/ui';

import { Grid, Typography } from '@material-ui/core';
import {
  labelResources,
  labelSeverity,
  labelStatus,
  labelDuration,
  labelTries,
} from '../translatedLabels';

const ClickableChip = (props): JSX.Element => {
  const click = (e): void => {
    e.preventDefault();
    e.stopPropagation();
  };

  return (
    <StatusChip style={{ cursor: 'pointer' }} onClick={click} {...props} />
  );
};

interface ColumnProps {
  row;
  Cell: ({ children }) => JSX.Element;
}

const SeverityColumn = ({ Cell }: ColumnProps): JSX.Element => {
  return (
    <Cell>
      <ClickableChip label="1" statusCode={StatusCode.None} />
    </Cell>
  );
};

const StatusColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  return (
    <Cell>
      <ClickableChip label={row.status.name} statusCode={row.status.code} />
    </Cell>
  );
};

const ResourcesColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  return (
    <Cell>
      <Grid container>
        <Grid item xs={1}>
          {/* <img src={row.icon.url} alt={row.icon.name} /> */}
        </Grid>
        <Grid item xs={11}>
          <Typography>{row.name}</Typography>
        </Grid>
        {row.parent && (
          <>
            <Grid item xs={1} />
            <Grid item xs={11} />
          </>
        )}
      </Grid>
    </Cell>
  );
};

const columns = [
  {
    id: 'severity',
    label: labelSeverity,
    type: TABLE_COLUMN_TYPES.component,
    Component: SeverityColumn,
  },
  {
    id: 'status',
    label: labelStatus,
    type: TABLE_COLUMN_TYPES.component,
    Component: StatusColumn,
  },
  {
    id: 'resources',
    label: labelResources,
    type: TABLE_COLUMN_TYPES.component,
    Component: ResourcesColumn,
  },
  {
    id: 'duration',
    label: labelDuration,
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ last_status_change }): string => last_status_change,
  },
  {
    id: 'tries',
    label: labelTries,
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ tries }): string => tries,
  },
  {
    id: 'lastCheck',
    label: 'Last check',
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ last_check }): string => last_check,
  },
  {
    id: 'information',
    label: 'Information',
    type: TABLE_COLUMN_TYPES.string,
    getFormattedString: ({ information }): string => information,
  },
];

export default columns;
