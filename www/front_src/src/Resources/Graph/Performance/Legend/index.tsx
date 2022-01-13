/* eslint-disable hooks/sort */
import * as React from 'react';

import clsx from 'clsx';
import { equals, find, gt, includes, isNil, length, slice, split } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

import {
  Typography,
  makeStyles,
  useTheme,
  alpha,
  Theme,
  Tooltip,
  Box,
  Button,
} from '@material-ui/core';
import BarChartIcon from '@material-ui/icons/BarChart';
import { CreateCSSProperties } from '@material-ui/styles';

import { Line, TimeValue } from '../models';
import memoizeComponent from '../../../memoizedComponent';
import formatMetricValue from '../formatMetricValue/index';
import {
  labelAvg,
  labelDisplayCompleteGraph,
  labelMax,
  labelMin,
} from '../../../translatedLabels';
import { timeValueAtom } from '../Graph/mouseTimeValueAtoms';
import { getLineForMetric, getMetrics } from '../timeSeries';
import { panelWidthStorageAtom } from '../../../Details/detailsAtoms';

import LegendMarker from './Marker';

interface MakeStylesProps {
  limitLegendRows: boolean;
  panelWidth: number;
}

interface FormattedMetricData {
  color: string;
  formattedValue: string | null;
  name: string;
  unit: string;
}

const maxLinesDisplayed = 11;

const useStyles = makeStyles<Theme, MakeStylesProps, string>((theme) => ({
  caption: ({ panelWidth }): CreateCSSProperties<MakeStylesProps> => ({
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
  items: ({ limitLegendRows }): CreateCSSProperties<MakeStylesProps> => ({
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
    justifyContent: 'center',
    marginLeft: theme.spacing(0.5),
    maxHeight: limitLegendRows ? theme.spacing(19) : 'unset',
    overflowY: 'auto',
    width: '100%',
  }),
  legend: {
    maxHeight: theme.spacing(24),
    overflowX: 'hidden',
    overflowY: 'auto',
    width: '100%',
  },
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
    color: alpha(theme.palette.common.black, 0.6),
  },
  toggable: {
    cursor: 'pointer',
  },
}));

interface Props {
  base: number;
  displayCompleteGraph?: () => void;
  displayTimeValues: boolean;
  limitLegendRows?: boolean;
  lines: Array<Line>;
  onClearHighlight: () => void;
  onHighlight: (metric: string) => void;
  onSelect: (metric: string) => void;
  onToggle: (metric: string) => void;
  timeSeries: Array<TimeValue>;
  toggable: boolean;
}

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
  base,
  limitLegendRows = false,
  displayCompleteGraph,
  timeSeries,
  displayTimeValues,
}: Props): JSX.Element => {
  const panelWidth = useAtomValue(panelWidthStorageAtom);
  const classes = useStyles({ limitLegendRows, panelWidth });
  const theme = useTheme();
  const { t } = useTranslation();
  const timeValue = useAtomValue(timeValueAtom);

  const graphTimeValue = timeSeries.find((timeSerie) =>
    equals(timeSerie.timeTick, timeValue?.timeTick),
  );

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

  const getMetricsToDisplay = (): Array<string> => {
    if (isNil(graphTimeValue)) {
      return [];
    }
    const metrics = getMetrics(graphTimeValue as TimeValue);

    const metricsToDisplay = metrics.filter((metric) => {
      const line = getLineForMetric({ lines, metric });

      return !isNil(graphTimeValue[metric]) && !isNil(line);
    });

    return metricsToDisplay;
  };

  const getMetricValue = ({ value, unit }: GetMetricValueProps): string =>
    formatMetricValue({
      base,
      unit,
      value,
    }) || 'N/A';

  const getFormattedMetricData = (
    metric: string,
  ): FormattedMetricData | null => {
    if (isNil(graphTimeValue)) {
      return null;
    }
    const value = graphTimeValue[metric] as number;

    const { color, name, unit } = getLineForMetric({
      lines,
      metric,
    }) as Line;

    const formattedValue = formatMetricValue({
      base,
      unit,
      value,
    });

    return {
      color,
      formattedValue,
      name,
      unit,
    };
  };

  const displayedLines = limitLegendRows
    ? slice(0, maxLinesDisplayed, lines)
    : lines;

  const hasMoreLines = limitLegendRows && gt(length(lines), maxLinesDisplayed);

  const metrics = getMetricsToDisplay();

  return (
    <div className={classes.legend}>
      <div>
        <div className={classes.items}>
          {displayedLines.map((line) => {
            const {
              color,
              name,
              display,
              metric: metricLine,
              highlight,
            } = line;

            const markerColor = display
              ? color
              : alpha(theme.palette.text.disabled, 0.2);

            const metric = find(equals(line.metric), metrics);

            const formattedValue =
              displayTimeValues &&
              metric &&
              getFormattedMetricData(metric)?.formattedValue;

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
                          <Typography variant="caption">
                            {t(label)}:{' '}
                          </Typography>
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
            {t(labelDisplayCompleteGraph)}
          </Button>
        )}
      </div>
    </div>
  );
};

const memoProps = [
  'panelWidth',
  'lines',
  'toggable',
  'timeSeries',
  'displayTimeValues',
  'base',
];

const MemoizedLegendContent = memoizeComponent<Props>({
  Component: LegendContent,
  memoProps,
});

const Legend = (props: Props): JSX.Element => {
  return <MemoizedLegendContent {...props} />;
};

export default Legend;
