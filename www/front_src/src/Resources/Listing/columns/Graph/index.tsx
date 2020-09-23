import * as React from 'react';

import { makeStyles, Paper } from '@material-ui/core';
import IconGraph from '@material-ui/icons/BarChart';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

import { path, isNil } from 'ramda';
import { labelGraph } from '../../../translatedLabels';
import HoverChip from '../HoverChip';
import PerformanceGraph from '../../../Graph/Performance';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    height: 200,
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
          <PerformanceGraph endpoint={endpoint} graphHeight={150} />
        </Paper>
      </HoverChip>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
