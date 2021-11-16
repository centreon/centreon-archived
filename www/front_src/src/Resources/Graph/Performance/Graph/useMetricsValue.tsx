import * as React from 'react';

import { isNil, not } from 'ramda';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

import formatMetricValue from '../formatMetricValue';
import { Line, TimeValue } from '../models';
import { getLineForMetric, getMetrics } from '../timeSeries';

export type MousePosition = [number, number] | null;

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

interface ChangeMousePositionAndMetricsValueProps {
  base: number;
  lines: Array<Line>;
  position: MousePosition;
  timeValue: TimeValue | null;
}

interface ChangeMetricsValueProps {
  newMetricsValue: MetricsValue | null;
}

interface MetricsValueState {
  changeMetricsValue: ({ newMetricsValue }: ChangeMetricsValueProps) => void;
  changeMousePositionAndMetricsValue: (
    props: ChangeMousePositionAndMetricsValueProps,
  ) => void;
  formatDate: () => string;
  getFormattedMetricData: (metric: string) => FormattedMetricData | null;
  metricsValue: MetricsValue | null;
  mousePosition: MousePosition;
  setMousePosition: React.Dispatch<React.SetStateAction<MousePosition>>;
}

const useMetricsValue = (isInViewPort?: boolean): MetricsValueState => {
  const [metricsValue, setMetricsValue] = React.useState<MetricsValue | null>(
    null,
  );
  const [mousePosition, setMousePosition] = React.useState<MousePosition>(null);
  const { format } = useLocaleDateTimeFormat();

  const formatDate = (): string =>
    format({
      date: new Date(metricsValue?.timeValue.timeTick || 0),
      formatString: dateTimeFormat,
    });

  const changeMetricsValue = ({
    newMetricsValue,
  }: ChangeMetricsValueProps): void => {
    if (not(isInViewPort)) {
      return;
    }
    setMetricsValue(newMetricsValue);
  };

  const changeMousePositionAndMetricsValue = ({
    position,
    timeValue,
    lines,
    base,
  }: ChangeMousePositionAndMetricsValueProps): void => {
    if (isNil(position) || isNil(timeValue)) {
      setMousePosition(null);
      setMetricsValue(null);

      return;
    }
    setMousePosition(position);

    const metrics = getMetrics(timeValue);

    const metricsToDisplay = metrics.filter((metric) => {
      const line = getLineForMetric({ lines, metric });

      return !isNil(timeValue[metric]) && !isNil(line);
    });

    changeMetricsValue({
      newMetricsValue: {
        base,
        lines,
        metrics: metricsToDisplay,
        timeValue,
      },
    });
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
    changeMousePositionAndMetricsValue,
    formatDate,
    getFormattedMetricData,
    metricsValue,
    mousePosition,
    setMousePosition,
  };
};

export const MetricsValueContext = React.createContext<
  MetricsValueState | undefined
>(undefined);

export const useMetricsValueContext = (): MetricsValueState =>
  React.useContext(MetricsValueContext) as MetricsValueState;

export default useMetricsValue;
