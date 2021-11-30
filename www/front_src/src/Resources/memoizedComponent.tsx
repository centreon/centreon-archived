import * as React from 'react';

import { equals, pick } from 'ramda';

interface memoizeComponentParameters {
  Component: (props) => JSX.Element | null;
  memoProps: Array<string>;
}

const memoizeComponent = <T extends unknown>({
  memoProps,
  Component,
}: memoizeComponentParameters): React.NamedExoticComponent<T> =>
  React.memo(Component, (prevProps, nextProps) =>
    equals(pick(memoProps, prevProps), pick(memoProps, nextProps)),
  );

export default memoizeComponent;
