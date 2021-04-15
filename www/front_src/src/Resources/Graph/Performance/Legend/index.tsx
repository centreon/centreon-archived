import * as React from 'react';

import clsx from 'clsx';
import { equals, find, includes, propOr, split } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  Typography,
  makeStyles,
  useTheme,
  fade,
  Theme,
  Tooltip,
} from '@material-ui/core';

import { ResourceContext, useResourceContext } from '../../../Context';
import { Line } from '../models';
import memoizeComponent from '../../../memoizedComponent';
import { useMetricsValueContext } from '../Graph/useMetricsValue';
import formatMetricValue from '../formatMetricValue/index';
import { labelAvg, labelMax, labelMin } from '../../../translatedLabels';

import LegendMarker from './Marker';

interface MakeStylesProps {
  limitLegendRows: boolean;
  panelWidth: number;
}

const useStyles = makeStyles<Theme, MakeStylesProps, string>((theme) => ({
  caption: ({ panelWidth }) => ({
    color: fade(theme.palette.common.black, 0.6),
    lineHeight: 1.2,
    marginRight: theme.spacing(1),
    maxWidth: 0.85 * panelWidth,
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  }),
  hidden: {
    color: theme.palette.text.disabled,
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
    maxHeight: limitLegendRows ? theme.spacing(16) : 'unset',
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
  toggable: {
    '&:hover': {
      color: theme.palette.common.black,
    },
    cursor: 'pointer',
  },
}));

interface Props {
  base: number;
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
}: LegendContentProps): JSX.Element => {
  const classes = useStyles({ limitLegendRows, panelWidth });
  const theme = useTheme();
  const { metricsValue, getFormattedMetricData } = useMetricsValueContext();
  const { t } = useTranslation();

  const getLegendName = ({
    metric,
    legend,
    name,
    display,
  }: Line): JSX.Element => {
    const legendName = legend || name;
    const metricName = includes('#', legendName)
      ? split('#')(legendName)[1]
      : legendName;
    return (
      <div
        onMouseEnter={(): void => onHighlight(metric)}
        onMouseLeave={(): void => onClearHighlight()}
      >
        <Tooltip placement="top" title={legendName}>
          <Typography
            className={clsx(
              {
                [classes.hidden]: !display,
                [classes.toggable]: toggable,
              },
              classes.caption,
            )}
            component="p"
            variant="caption"
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

        return (
          <div className={classes.item} key={name}>
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
          </div>
        );
      })}
    </div>
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
