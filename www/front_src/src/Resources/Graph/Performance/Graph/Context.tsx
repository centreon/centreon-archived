import * as React from 'react';

import { Annotations } from './useAnnotations';

export const AnnotationsContext = React.createContext<Annotations | undefined>(
  undefined,
);

const useAnnotationsContext = (): Annotations =>
  React.useContext(AnnotationsContext) as Annotations;

export default useAnnotationsContext;
