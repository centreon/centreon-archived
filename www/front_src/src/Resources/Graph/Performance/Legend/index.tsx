import * as React from 'react';

import clsx from 'clsx';
import { equals, find, lt, propOr } from 'ramda';

import {
  Typography,
  makeStyles,
  useTheme,
  fade,
  Theme,
} from '@material-ui/core';

import { ResourceContext, useResourceContext } from '../../../Context';
import { Line } from '../models';
import memoizeComponent from '../../../memoizedComponent';
import { useMetricsValueContext } from '../Graph/useMetricsValue';

import LegendMarker from './Marker';

const useStyles = makeStyles<Theme, { panelWidth: number }>((theme) => ({
  items: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, 160px)',
    width: '100%',
    justifyContent: 'center',
  },
  item: {
    display: 'grid',
    gridTemplateColumns: 'min-content 1fr',
    margin: theme.spacing(0, 1, 1, 0),
    height: theme.spacing(8.5),
  },
  icon: {
    width: 9,
    height: 9,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
  },
  caption: ({ panelWidth }) => ({
    marginRight: theme.spacing(1),
    color: fade(theme.palette.common.black, 0.6),
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    maxWidth: 0.85 * panelWidth,
  }),
  hidden: {
    color: theme.palette.text.disabled,
  },
  toggable: {
    cursor: 'pointer',
    '&:hover': {
      color: theme.palette.common.black,
    },
  },
  legendData: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between',
  },
  legendValue: {
    margin: 0,
  },
}));

interface Props {
  lines: Array<Line>;
  toggable: boolean;
  onToggle: (metric: string) => void;
  onHighlight: (metric: string) => void;
  onSelect: (metric: string) => void;
  onClearHighlight: () => void;
}

type LegendContentProps = Props & Pick<ResourceContext, 'panelWidth'>;

const maxCharactersToDisplay = 40;

const LegendContent = ({
  lines,
  onToggle,
  onSelect,
  toggable,
  onHighlight,
  onClearHighlight,
  panelWidth,
}: LegendContentProps): JSX.Element => {
  const classes = useStyles({ panelWidth });
  const theme = useTheme();
  const { metricsValue, getFormattedMetricData } = useMetricsValueContext();

  const getFormattedName = ({ name, unit }) => {
    if (lt(name.length, maxCharactersToDisplay)) {
      return name;
    }
    return `${name.substring(0, maxCharactersToDisplay - 8)}... (${unit})`;
  };

  const getLegendName = ({
    metric,
    name,
    display,
    unit,
  }: Line): JSX.Element => {
    return (
      <div
        onMouseEnter={(): void => onHighlight(metric)}
        onMouseLeave={(): void => onClearHighlight()}
      >
        <Typography
          className={clsx(
            {
              [classes.hidden]: !display,
              [classes.toggable]: toggable,
            },
            classes.caption,
          )}
          variant="body2"
          onClick={(event: React.MouseEvent): void => {
            if (!toggable) {
              return;
            }

            if (event.ctrlKey || event.metaKey) {
              onToggle(metric);
              return;
            }

            onSelect(metric);
          }}
        >
          {getFormattedName({
            name,
            unit,
          })}
        </Typography>
      </div>
    );
  };

  return (
    <div className={classes.items}>
      {lines.map((line) => {
        const { color, name, display } = line;

        const markerColor = display
          ? color
          : fade(theme.palette.text.disabled, 0.2);

        const metric = find(
          equals(line.metric),
          propOr([], 'metrics', metricsValue),
        );

        const formattedValue =
          metric && getFormattedMetricData(metric)?.formattedValue;

        return (
          <div className={classes.item} key={name}>
            <LegendMarker disabled={!display} color={markerColor} />
            <div className={classes.legendData}>
              {getLegendName(line)}
              {formattedValue && (
                <Typography variant="h6" className={classes.legendValue}>
                  {formattedValue}
                </Typography>
              )}
            </div>
          </div>
        );
      })}
    </div>
  );
};

const memoProps = ['panelWidth', 'lines', 'toggable'];

const MemoizedLegendContent = memoizeComponent<LegendContentProps>({
  memoProps,
  Component: LegendContent,
});

const Legend = (props: Props): JSX.Element => {
  const { panelWidth } = useResourceContext();

  return <MemoizedLegendContent {...props} panelWidth={panelWidth} />;
};

export default Legend;
