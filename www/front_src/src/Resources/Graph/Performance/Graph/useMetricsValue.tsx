import * as React from 'react';

import { isNil, not } from 'ramda';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';
import { getLineForMetric } from '../timeSeries';

interface MetricsValue {
  base: number;
  lines: Array<Line>;
  metrics: Array<string>;
  timeValue: TimeValue;
}

interface FormattedMetricData {
  color: string;
  formattedValue: string | null;
  name: string;
  unit: string;
}

interface MetricsValueState {
  changeMetricsValue: ({ newMetricsValue }) => void;
  formatDate: () => string;
  getFormattedMetricData: (metric: string) => FormattedMetricData | null;
  metricsValue: MetricsValue | null;
}

const useMetricsValue = (isInViewPort?: boolean): MetricsValueState => {
  const [metricsValue, setMetricsValue] = React.useState<MetricsValue | null>(
    null,
  );
  const { format } = useLocaleDateTimeFormat();

  const formatDate = (): string =>
    format({
      date: new Date(metricsValue?.timeValue.timeTick || 0),
      formatString: dateTimeFormat,
    });

  const changeMetricsValue = ({ newMetricsValue }): void => {
    if (not(isInViewPort)) {
      return;
    }
    setMetricsValue(newMetricsValue);
  };

  const getFormattedMetricData = (
    metric: string,
  ): FormattedMetricData | null => {
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
    metricsValue,
  };
};

export const MetricsValueContext = React.createContext<
  MetricsValueState | undefined
>(undefined);

export const useMetricsValueContext = (): MetricsValueState =>
  React.useContext(MetricsValueContext) as MetricsValueState;

export default useMetricsValue;
