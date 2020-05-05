import * as React from 'react';
import { FilterState } from './Filter/useFilter';

const Context = React.createContext<FilterState | null>(null);

const useResourceContext = (): FilterState | null => React.useContext(Context);

export default Context;

export { useResourceContext };
