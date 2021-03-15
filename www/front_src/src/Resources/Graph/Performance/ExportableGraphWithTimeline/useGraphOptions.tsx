import * as React from 'react';

import { labelToggleTooltipValues } from '../../../translatedLabels';
import { GraphOptionId } from '../models';

interface GraphOption {
  id: GraphOptionId;
  label: string;
  value: boolean;
}

interface GraphOptions {
  tooltipValues: GraphOption;
}

interface UseGraphOptions {
  graphOptions: GraphOptions;
  changeGraphOptions: (graphOptionId: GraphOptionId) => () => void;
}

export const GraphOptionsContext = React.createContext<
  UseGraphOptions | undefined
>(undefined);

export const useGraphOptionsContext = (): UseGraphOptions =>
  React.useContext(GraphOptionsContext) as UseGraphOptions;

const defaultGraphOptions = {
  tooltipValues: {
    id: GraphOptionId.tooltipValues,
    label: labelToggleTooltipValues,
    value: false,
  },
};

const useGraphOptions = (): UseGraphOptions => {
  const [graphOptions, setGraphOptions] = React.useState<GraphOptions>(
    defaultGraphOptions,
  );

  const changeGraphOptions = (graphOptionId: GraphOptionId) => () =>
    setGraphOptions((currentGraphOptions) => {
      return {
        ...currentGraphOptions,
        [graphOptionId]: {
          ...currentGraphOptions[graphOptionId],
          value: !currentGraphOptions[graphOptionId].value,
        },
      };
    });

  return {
    graphOptions,
    changeGraphOptions,
  };
};

export default useGraphOptions;
