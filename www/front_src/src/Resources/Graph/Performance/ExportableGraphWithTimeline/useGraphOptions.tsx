import * as React from 'react';

import { GraphOptions, GraphTabParameters } from '../../../Details/models';
import {
  labelDisplayEvents,
  labelDisplayTooltips,
} from '../../../translatedLabels';
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

export const defaultGraphOptions = {
  [GraphOptionId.displayTooltips]: {
    id: GraphOptionId.displayTooltips,
    label: labelDisplayTooltips,
    value: false,
  },
  [GraphOptionId.displayEvents]: {
    id: GraphOptionId.displayEvents,
    label: labelDisplayEvents,
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
    graphOptions,
    changeGraphOptions,
  };
};

export default useGraphOptions;
