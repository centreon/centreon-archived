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

const useStyles = makeStyles<Theme, { panelWidth: number }>((theme) => ({
  items: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
    width: '100%',
    justifyContent: 'center',
  },
  item: {
    display: 'grid',
    gridTemplateColumns: 'min-content minmax(50px, 1fr)',
    margin: theme.spacing(0, 1, 1, 1),
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
    maxWidth: 0.85 * panelWidth,
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    lineHeight: 1.2,
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
    fontWeight: theme.typography.body1.fontWeight,
  },
}));

interface Props {
  lines: Array<Line>;
  toggable: boolean;
  base: number;
  onToggle: (metric: string) => void;
  onHighlight: (metric: string) => void;
  onSelect: (metric: string) => void;
  onClearHighlight: () => void;
}

type LegendContentProps = Props & Pick<ResourceContext, 'panelWidth'>;

interface GetMetricValueProps {
  value: string | null;
  unit: string;
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
}: LegendContentProps): JSX.Element => {
  const classes = useStyles({ panelWidth });
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
    const metricName = includes('#', legendName) ? split('#')[1] : legendName;
    return (
      <div
        onMouseEnter={(): void => onHighlight(metric)}
        onMouseLeave={(): void => onClearHighlight()}
      >
        <Tooltip title={legendName} placement="top">
          <Typography
            className={clsx(
              {
                [classes.hidden]: !display,
                [classes.toggable]: toggable,
              },
              classes.caption,
            )}
            variant="caption"
            component="p"
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
      value: value ? parseInt(value, 10) : null,
      unit,
      base,
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
            value: line.min,
          },
          {
            label: labelMax,
            value: line.max,
          },
          {
            label: labelAvg,
            value: line.average,
          },
        ];

        return (
          <div className={classes.item} key={name}>
            <LegendMarker disabled={!display} color={markerColor} />
            <div className={classes.legendData}>
              <div>
                {getLegendName(line)}
                <Typography
                  variant="caption"
                  component="p"
                  className={classes.caption}
                >
                  {`(${line.unit})`}
                </Typography>
              </div>
              {formattedValue ? (
                <Typography variant="h6" className={classes.legendValue}>
                  {formattedValue}
                </Typography>
              ) : (
                <div
                  style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(2, min-content)',
                    gridAutoRows: `${theme.spacing(2)}px`,
                    columnGap: '8px',
                    whiteSpace: 'nowrap',
                  }}
                >
                  {minMaxAvg.map(({ label, value }) => (
                    <div key={label} aria-label={t(label)}>
                      <Typography variant="caption">{t(label)}: </Typography>
                      <Typography variant="caption" style={{ fontWeight: 600 }}>
                        {getMetricValue({
                          value,
                          unit: line.unit,
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
  memoProps,
  Component: LegendContent,
});

const Legend = (props: Props): JSX.Element => {
  const { panelWidth } = useResourceContext();

  return <MemoizedLegendContent {...props} panelWidth={panelWidth} />;
};

export default Legend;
