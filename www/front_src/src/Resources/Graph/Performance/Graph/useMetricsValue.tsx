import * as React from 'react';

import { isEmpty, isNil, not, or } from 'ramda';
import { useTooltip } from '@visx/visx';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';
import { getLineForMetric } from '../timeSeries';

import MetricsTooltip from './MetricsTooltip';

interface MetricsValue {
  base: number;
  lines: Array<Line>;
  metrics: Array<string>;
  timeValue: TimeValue;
  x: number;
  y: number;
}

interface FormattedMetricData {
  color: string;
  formattedValue: string | null;
  name: string;
  unit: string;
}

interface UseMetricsValue {
  changeMetricsValue: ({ newMetricsValue, displayTooltipValues }) => void;
  formatDate: () => string;
  getFormattedMetricData: (metric: string) => FormattedMetricData | null;
  hideTooltip;
  metricsValue: MetricsValue | null;
  tooltipData;
  tooltipLeft;
  tooltipOpen;
  tooltipTop;
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
      tooltipData: isEmpty(newMetricsValue?.metrics) ? undefined : (
        <MetricsTooltip />
      ),
      tooltipLeft: newMetricsValue?.x || 0,
      tooltipTop: newMetricsValue?.y || 0,
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
      base: metricsValue.base,
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

  return {
    changeMetricsValue,
    formatDate,
    getFormattedMetricData,
    hideTooltip,
    metricsValue,
    tooltipData,
    tooltipLeft,
    tooltipOpen,
    tooltipTop,
  };
};

export const MetricsValueContext = React.createContext<
  UseMetricsValue | undefined
>(undefined);

export const useMetricsValueContext = (): UseMetricsValue =>
  React.useContext(MetricsValueContext) as UseMetricsValue;

export default useMetricsValue;
