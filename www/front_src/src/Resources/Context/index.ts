import * as React from 'react';

import { FilterState } from '../Filter/useFilter';
import { ActionsState } from '../Actions/useActions';
import { ListingState } from '../Listing/useListing';
import { DetailsState } from '../Details/useLoadDetails';

export type ResourceContext = FilterState &
  ActionsState &
  ListingState &
  DetailsState;

const Context = React.createContext<ResourceContext | undefined>(undefined);

const useResourceContext = (): ResourceContext =>
  React.useContext(Context) as ResourceContext;

export default Context;

export { useResourceContext };
