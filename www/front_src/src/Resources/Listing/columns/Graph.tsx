import * as React from 'react';

import { path, isNil, not } from 'ramda';

import { makeStyles, Paper } from '@material-ui/core';
import IconGraph from '@material-ui/icons/BarChart';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

import { labelGraph, labelServiceGraphs } from '../../translatedLabels';
import PerformanceGraph from '../../Graph/Performance';
import { ResourceDetails } from '../../Details/models';
import { Resource } from '../../models';
import useTimePeriod from '../../Graph/Performance/TimePeriods/useTimePeriod';

import HoverChip from './HoverChip';
import IconColumn from './IconColumn';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    overflow: 'auto',
    padding: theme.spacing(1),
    width: 575,
  },
}));

interface GraphProps {
  displayCompleteGraph: () => void;
  endpoint?: string;
  row: Resource | ResourceDetails;
}

const Graph = ({
  row,
  endpoint,
  displayCompleteGraph,
}: GraphProps): JSX.Element => {
  const { periodQueryParameters } = useTimePeriod({});

  return (
    <PerformanceGraph
      limitLegendRows
      displayCompleteGraph={displayCompleteGraph}
      displayTitle={false}
      endpoint={`${endpoint}${periodQueryParameters}`}
      graphHeight={150}
      resource={row}
      timeline={[]}
    />
  );
};

const GraphColumn = ({
  onClick,
}: {
  onClick: (row) => void;
}): ((props: ComponentColumnProps) => JSX.Element | null) => {
  const GraphHoverChip = ({
    row,
    isHovered,
  }: ComponentColumnProps): JSX.Element | null => {
    const classes = useStyles();

    const { type } = row;

    const isHost = type === 'host';

    const endpoint = path<string | undefined>(
      ['links', 'endpoints', 'performance_graph'],
      row,
    );

    if (isNil(endpoint) && !isHost) {
      return null;
    }

    const label = isHost ? labelServiceGraphs : labelGraph;

    return (
      <IconColumn>
        <HoverChip
          Chip={(): JSX.Element => (
            <IconButton
              ariaLabel={label}
              title={label}
              onClick={(): void => onClick(row)}
            >
              <IconGraph fontSize="small" />
            </IconButton>
          )}
          isHovered={isHovered}
          label={label}
        >
          {({ close, isChipHovered }): JSX.Element => {
            if (isHost || not(isChipHovered) || not(isHovered)) {
              return <div />;
            }

            return (
              <Paper className={classes.graph}>
                <Graph
                  displayCompleteGraph={(): void => {
                    onClick(row);
                    close();
                  }}
                  endpoint={endpoint}
                  row={row}
                />
              </Paper>
            );
          }}
        </HoverChip>
      </IconColumn>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
