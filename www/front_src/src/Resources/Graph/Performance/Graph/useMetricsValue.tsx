import * as React from 'react';

import { isNil } from 'ramda';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';
import { getLineForMetric } from '../timeSeries';

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
  setMetricsValue: React.Dispatch<React.SetStateAction<MetricsValue | null>>;
  formatDate: () => string;
  getFormattedMetricData: (metric: string) => FormattedMetricData | null;
}

const useMetricsValue = (): UseMetricsValue => {
  const [metricsValue, setMetricsValue] = React.useState<MetricsValue | null>(
    null,
  );
  const { format } = useLocaleDateTimeFormat();

  const formatDate = () =>
    format({
      date: new Date(metricsValue?.timeValue.timeTick || 0),
      formatString: dateTimeFormat,
    });

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
    setMetricsValue,
    formatDate,
    getFormattedMetricData,
  };
};

export const MetricsValueContext = React.createContext<
  UseMetricsValue | undefined
>(undefined);

export const useMetricsValueContext = (): UseMetricsValue =>
  React.useContext(MetricsValueContext) as UseMetricsValue;

export default useMetricsValue;
