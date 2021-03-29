import * as React from 'react';

import { isEmpty, isNil, not, or } from 'ramda';
import { useTooltip } from '@visx/visx';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';
import { getLineForMetric } from '../timeSeries';

import MetricsTooltip from './MetricsTooltip';

interface MetricsValue {
  x: number;
  y: number;
  timeValue: TimeValue;
  metrics: Array<string>;
  lines: Array<Line>;
  base: number;
}

interface FormattedMetricData {
  color: string;
  name: string;
  unit: string;
  formattedValue: string | null;
}

interface UseMetricsValue {
  metricsValue: MetricsValue | null;
  tooltipData;
  tooltipOpen;
  tooltipLeft;
  tooltipTop;
  formatDate: () => string;
  getFormattedMetricData: (metric: string) => FormattedMetricData | null;
  changeMetricsValue: ({ newMetricsValue, displayTooltipValues }) => void;
  hideTooltip;
}

const useMetricsValue = (): UseMetricsValue => {
  const [metricsValue, setMetricsValue] = React.useState<MetricsValue | null>(
    null,
  );
  const { format } = useLocaleDateTimeFormat();
  const {
    tooltipData,
    tooltipLeft,
    tooltipTop,
    tooltipOpen,
    showTooltip,
    hideTooltip,
  } = useTooltip();

  const formatDate = () =>
    format({
      date: new Date(metricsValue?.timeValue.timeTick || 0),
      formatString: dateTimeFormat,
    });

  const changeMetricsValue = ({ newMetricsValue, displayTooltipValues }) => {
    setMetricsValue(newMetricsValue);
    if (or(not(displayTooltipValues), isNil(newMetricsValue))) {
      hideTooltip();
      return;
    }
    showTooltip({
      tooltipLeft: newMetricsValue?.x || 0,
      tooltipTop: newMetricsValue?.y || 0,
      tooltipData: isEmpty(newMetricsValue?.metrics) ? undefined : (
        <MetricsTooltip />
      ),
    });
  };

  const getFormattedMetricData = (metric: string) => {
    if (isNil(metricsValue)) {
      return null;
    }
    const value = metricsValue?.timeValue[metric] as number;

    const { color, name, unit } = getLineForMetric({
      lines: metricsValue.lines,
      metric,
    }) as Line;

    const formattedValue = formatMetricValue({
      value,
      unit,
      base: metricsValue.base,
    });

    return {
      color,
      name,
      formattedValue,
      unit,
    };
  };

  return {
    metricsValue,
    tooltipData,
    tooltipOpen,
    tooltipLeft,
    tooltipTop,
    formatDate,
    getFormattedMetricData,
    changeMetricsValue,
    hideTooltip,
  };
};

export const MetricsValueContext = React.createContext<
  UseMetricsValue | undefined
>(undefined);

export const useMetricsValueContext = (): UseMetricsValue =>
  React.useContext(MetricsValueContext) as UseMetricsValue;

export default useMetricsValue;
