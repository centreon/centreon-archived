import { atom } from 'jotai';

import { GraphOptions } from '../../../Details/models';
import { labelDisplayEvents } from '../../../translatedLabels';
import { GraphOptionId } from '../models';

export const defaultGraphOptions = {
  [GraphOptionId.displayEvents]: {
    id: GraphOptionId.displayEvents,
    label: labelDisplayEvents,
    value: false
  }
};

export const graphOptionsAtom = atom<GraphOptions>(defaultGraphOptions);

export const changeGraphOptionsDerivedAtom = atom(
  null,
  (get, set, { graphOptionId, changeTabGraphOptions }) => {
    const graphOptions = get(graphOptionsAtom);

    const newGraphOptions = {
      ...graphOptions,
      [graphOptionId]: {
        ...graphOptions[graphOptionId],
        value: !graphOptions[graphOptionId].value
      }
    };
    set(graphOptionsAtom, newGraphOptions);
    changeTabGraphOptions(newGraphOptions);
  }
);
