import * as React from 'react';

import { path, isNil } from 'ramda';

import { makeStyles, Paper } from '@material-ui/core';
import IconGraph from '@material-ui/icons/BarChart';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

import { labelGraph } from '../../translatedLabels';
import PerformanceGraph from '../../Graph/Performance';

import HoverChip from './HoverChip';
import IconColumn from './IconColumn';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    maxHeight: 270,
    width: 575,
    padding: theme.spacing(2),
    overflow: 'auto',
  },
}));

const GraphColumn = ({
  onClick,
}: {
  onClick: (row) => void;
}): ((props: ComponentColumnProps) => JSX.Element | null) => {
  const GraphHoverChip = ({
    row,
  }: ComponentColumnProps): JSX.Element | null => {
    const classes = useStyles();

    const endpoint = path<string | undefined>(
      ['links', 'endpoints', 'performance_graph'],
      row,
    );

    if (isNil(endpoint)) {
      return null;
    }

    return (
      <IconColumn>
        <HoverChip
          Chip={(): JSX.Element => (
            <IconButton
              title={labelGraph}
              onClick={(): void => onClick(row)}
              ariaLabel={labelGraph}
            >
              <IconGraph fontSize="small" />
            </IconButton>
          )}
          label={labelGraph}
        >
          <Paper className={classes.graph}>
            <PerformanceGraph
              endpoint={endpoint}
              graphHeight={150}
              resource={row}
              timeline={[]}
              displayTitle={false}
            />
          </Paper>
        </HoverChip>
      </IconColumn>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
