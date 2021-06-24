import * as React from 'react';

import clsx from 'clsx';
import {
  equals,
  find,
  gt,
  includes,
  length,
  propOr,
  slice,
  split,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  Typography,
  makeStyles,
  useTheme,
  fade,
  Theme,
  Tooltip,
  Box,
  Button,
} from '@material-ui/core';
import BarChartIcon from '@material-ui/icons/BarChart';

import { ResourceContext, useResourceContext } from '../../../Context';
import { Line } from '../models';
import memoizeComponent from '../../../memoizedComponent';
import { useMetricsValueContext } from '../Graph/useMetricsValue';
import formatMetricValue from '../formatMetricValue/index';
import {
  labelAvg,
  labelDisplayAllLines,
  labelMax,
  labelMin,
} from '../../../translatedLabels';

import LegendMarker from './Marker';

interface MakeStylesProps {
  limitLegendRows: boolean;
  panelWidth: number;
}

const maxLinesDisplayed = 12;

const useStyles = makeStyles<Theme, MakeStylesProps, string>((theme) => ({
  caption: ({ panelWidth }) => ({
    lineHeight: 1.2,
    marginRight: theme.spacing(1),
    maxWidth: 0.85 * panelWidth,
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  }),
  highlight: {
    color: theme.typography.body1.color,
  },
  item: {
    display: 'grid',
    gridTemplateColumns: 'min-content minmax(50px, 1fr)',
    marginBottom: theme.spacing(1),
  },
  items: ({ limitLegendRows }) => ({
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
    justifyContent: 'center',
    marginLeft: theme.spacing(0.5),
    maxHeight: limitLegendRows ? theme.spacing(28) : 'unset',
    overflowY: 'auto',
    width: '100%',
  }),
  legendData: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between',
  },
  legendValue: {
    fontWeight: theme.typography.body1.fontWeight,
  },
  minMaxAvgContainer: {
    columnGap: theme.spacing(0.5),
    display: 'grid',
    gridAutoRows: `${theme.spacing(2)}px`,
    gridTemplateColumns: 'repeat(2, min-content)',
    whiteSpace: 'nowrap',
  },
  minMaxAvgValue: { fontWeight: 600 },
  normal: {
    color: fade(theme.palette.common.black, 0.6),
  },
  toggable: {
    cursor: 'pointer',
  },
}));

interface Props {
  base: number;
  displayCompleteGraph?: () => void;
  limitLegendRows?: boolean;
  lines: Array<Line>;
  onClearHighlight: () => void;
  onHighlight: (metric: string) => void;
  onSelect: (metric: string) => void;
  onToggle: (metric: string) => void;
  toggable: boolean;
}

type LegendContentProps = Props & Pick<ResourceContext, 'panelWidth'>;

interface GetMetricValueProps {
  unit: string;
  value: number | null;
}

const LegendContent = ({
  lines,
  onToggle,
  onSelect,
  toggable,
  onHighlight,
  onClearHighlight,
  panelWidth,
  base,
  limitLegendRows = false,
  displayCompleteGraph,
}: LegendContentProps): JSX.Element => {
  const classes = useStyles({ limitLegendRows, panelWidth });
  const theme = useTheme();
  const { metricsValue, getFormattedMetricData } = useMetricsValueContext();
  const { t } = useTranslation();

  const getLegendName = ({ legend, name }: Line): JSX.Element => {
    const legendName = legend || name;
    const metricName = includes('#', legendName)
      ? split('#')(legendName)[1]
      : legendName;
    return (
      <div>
        <Tooltip placement="top" title={legendName}>
          <Typography
            className={clsx(classes.caption)}
            component="p"
            variant="caption"
          >
            {metricName}
          </Typography>
        </Tooltip>
      </div>
    );
  };

  const getMetricValue = ({ value, unit }: GetMetricValueProps): string =>
    formatMetricValue({
      base,
      unit,
      value,
    }) || 'N/A';

  const displayedLines = limitLegendRows
    ? slice(0, maxLinesDisplayed, lines)
    : lines;

  const hasMoreLines = limitLegendRows && gt(length(lines), maxLinesDisplayed);

  return (
    <>
      <div className={classes.items}>
        {displayedLines.map((line) => {
          const { color, name, display, metric: metricLine, highlight } = line;

          const markerColor = display
            ? color
            : fade(theme.palette.text.disabled, 0.2);

          const metric = find(
            equals(line.metric),
            propOr([], 'metrics', metricsValue),
          );

          const formattedValue =
            metric && getFormattedMetricData(metric)?.formattedValue;

          const minMaxAvg = [
            {
              label: labelMin,
              value: line.minimum_value,
            },
            {
              label: labelMax,
              value: line.maximum_value,
            },
            {
              label: labelAvg,
              value: line.average_value,
            },
          ];

          const selectMetricLine = (event: React.MouseEvent): void => {
            if (!toggable) {
              return;
            }

            if (event.ctrlKey || event.metaKey) {
              onToggle(metricLine);
              return;
            }

            onSelect(metricLine);
          };

          return (
            <Box
              className={clsx(
                classes.item,
                highlight ? classes.highlight : classes.normal,
                toggable && classes.toggable,
              )}
              key={name}
              onClick={selectMetricLine}
              onMouseEnter={(): void => onHighlight(metricLine)}
              onMouseLeave={(): void => onClearHighlight()}
            >
              <LegendMarker color={markerColor} disabled={!display} />
              <div className={classes.legendData}>
                <div>
                  {getLegendName(line)}
                  <Typography
                    className={classes.caption}
                    component="p"
                    variant="caption"
                  >
                    {line.unit && `(${line.unit})`}
                  </Typography>
                </div>
                {formattedValue ? (
                  <Typography className={classes.legendValue} variant="h6">
                    {formattedValue}
                  </Typography>
                ) : (
                  <div className={classes.minMaxAvgContainer}>
                    {minMaxAvg.map(({ label, value }) => (
                      <div aria-label={t(label)} key={label}>
                        <Typography variant="caption">{t(label)}: </Typography>
                        <Typography
                          className={classes.minMaxAvgValue}
                          variant="caption"
                        >
                          {getMetricValue({
                            unit: line.unit,
                            value,
                          })}
                        </Typography>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </Box>
          );
        })}
      </div>
      {hasMoreLines && (
        <Button
          fullWidth
          color="primary"
          size="small"
          onClick={displayCompleteGraph}
        >
          <BarChartIcon fontSize="small" />
          {t(labelDisplayAllLines)}
        </Button>
      )}
    </>
  );
};

const memoProps = ['panelWidth', 'lines', 'toggable'];

const MemoizedLegendContent = memoizeComponent<LegendContentProps>({
  Component: LegendContent,
  memoProps,
});

const Legend = (props: Props): JSX.Element => {
  const { panelWidth } = useResourceContext();

  return <MemoizedLegendContent {...props} panelWidth={panelWidth} />;
};

export default Legend;
