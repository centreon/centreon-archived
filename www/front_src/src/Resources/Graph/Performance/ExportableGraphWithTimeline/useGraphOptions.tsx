import * as React from 'react';

import { GraphOptions, GraphTabParameters } from '../../../Details/models';
import { labelDisplayEvents } from '../../../translatedLabels';
import { GraphOptionId } from '../models';

interface GraphOptionsState {
  changeGraphOptions: (graphOptionId: GraphOptionId) => () => void;
  graphOptions: GraphOptions;
}

export const GraphOptionsContext =
  React.createContext<GraphOptionsState | undefined>(undefined);

export const useGraphOptionsContext = (): GraphOptionsState =>
  React.useContext(GraphOptionsContext) as GraphOptionsState;

export const defaultGraphOptions = {
  [GraphOptionId.displayEvents]: {
    id: GraphOptionId.displayEvents,
    label: labelDisplayEvents,
    value: false,
  },
};

interface UseGraphOptionsState {
  changeTabGraphOptions: (graphOptions: GraphOptions) => void;
  graphTabParameters?: GraphTabParameters;
}

const useGraphOptions = ({
  graphTabParameters,
  changeTabGraphOptions,
}: UseGraphOptionsState): GraphOptionsState => {
  const [graphOptions, setGraphOptions] = React.useState<GraphOptions>({
    ...defaultGraphOptions,
    ...graphTabParameters?.graphOptions,
  });

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

  return {
    changeGraphOptions,
    graphOptions,
  };
};

export default useGraphOptions;
