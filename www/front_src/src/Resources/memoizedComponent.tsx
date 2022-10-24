import { memo, NamedExoticComponent } from 'react';

import { equals, pick } from 'ramda';

interface memoizeComponentParameters {
  Component: (props) => JSX.Element | null;
  memoProps: Array<string>;
}

const memoizeComponent = <T,>({
  memoProps,
  Component
}: memoizeComponentParameters): NamedExoticComponent<T> =>
  memo(Component, (prevProps, nextProps) =>
    equals(pick(memoProps, prevProps), pick(memoProps, nextProps))
  );

export default memoizeComponent;
