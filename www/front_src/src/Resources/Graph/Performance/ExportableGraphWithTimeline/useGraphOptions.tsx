import * as React from 'react';

import { equals, isNil, or } from 'ramda';

import { GraphOptions, GraphTabParameters } from '../../../Details/models';
import { labelToggleTooltipValues } from '../../../translatedLabels';
import { GraphOptionId } from '../models';

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

interface UseGraphOptionsProps {
  graphTabParameters?: GraphTabParameters;
  changeTabGraphOptions: (graphOptions: GraphOptions) => void;
}

const useGraphOptions = ({
  graphTabParameters,
  changeTabGraphOptions,
}: UseGraphOptionsProps): UseGraphOptions => {
  const [graphOptions, setGraphOptions] = React.useState<GraphOptions>(
    graphTabParameters?.graphOptions || defaultGraphOptions,
  );

  const changeGraphOptions = (graphOptionId: GraphOptionId) => () => {
    const newGraphOptions = {
      ...graphOptions,
      [graphOptionId]: {
        ...graphOptions[graphOptionId],
        value: !graphOptions[graphOptionId].value,
      },
    };
    setGraphOptions(newGraphOptions);
    changeTabGraphOptions(newGraphOptions);
  };

  React.useEffect(() => {
    if (
      or(
        isNil(graphTabParameters?.graphOptions),
        equals(graphTabParameters?.graphOptions, graphOptions),
      )
    ) {
      return;
    }

    setGraphOptions(graphTabParameters?.graphOptions as GraphOptions);
  }, [graphTabParameters?.graphOptions]);

  return {
    graphOptions,
    changeGraphOptions,
  };
};

export default useGraphOptions;
