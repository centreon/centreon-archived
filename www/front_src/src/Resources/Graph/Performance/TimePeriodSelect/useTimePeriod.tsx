import * as React from 'react';

import {
  last24hPeriod,
  TimePeriod,
  getTimePeriodById,
} from '../../../Details/tabs/Graph/models';

interface TimePeriodState {
  changeSelectedTimePeriod: (event) => void;
  selectedTimePeriod: TimePeriod;
  periodQueryParameters: string;
  getIntervalDates: () => [string, string];
}

const useTimePeriod = (): TimePeriodState => {
  const [
    selectedTimePeriod,
    setSelectedTimePeriod,
  ] = React.useState<TimePeriod>(last24hPeriod);

  const getIntervalDates = (timePeriod): [string, string] => {
    return [
      timePeriod.getStart().toISOString(),
      new Date(Date.now()).toISOString(),
    ];
  };

  const getGraphQueryParameters = (timePeriod): string => {
    const [start, end] = getIntervalDates(timePeriod);

    return `?start=${start}&end=${end}`;
  };

  const [periodQueryParameters, setPeriodQueryParameters] = React.useState(
    getGraphQueryParameters(selectedTimePeriod),
  );

  const changeSelectedTimePeriod = (event): void => {
    const timePeriodId = event.target.value;
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);

    const queryParamsForSelectedPeriodId = getGraphQueryParameters(timePeriod);
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
  };

  return {
    changeSelectedTimePeriod,
    selectedTimePeriod,
    periodQueryParameters,
    getIntervalDates: (): [string, string] =>
      getIntervalDates(selectedTimePeriod),
  };
};

export default useTimePeriod;
