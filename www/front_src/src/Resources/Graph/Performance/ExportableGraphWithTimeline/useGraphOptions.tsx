import * as React from 'react';

import { GraphOptions, GraphTabParameters } from '../../../Details/models';
import {
  labelDisplayEvents,
  labelDisplayTooltips,
} from '../../../translatedLabels';
import { GraphOptionId } from '../models';

interface GraphOptionsProps {
  changeGraphOptions: (graphOptionId: GraphOptionId) => () => void;
  graphOptions: GraphOptions;
}

export const GraphOptionsContext = React.createContext<
  GraphOptionsProps | undefined
>(undefined);

export const useGraphOptionsContext = (): GraphOptionsProps =>
  React.useContext(GraphOptionsContext) as GraphOptionsProps;

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
  changeTabGraphOptions: (graphOptions: GraphOptions) => void;
  graphTabParameters?: GraphTabParameters;
}

const useGraphOptions = ({
  graphTabParameters,
  changeTabGraphOptions,
}: UseGraphOptionsProps): GraphOptionsProps => {
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
