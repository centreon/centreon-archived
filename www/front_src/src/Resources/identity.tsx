import * as React from 'react';

const identity = (NextComponent) => (props): JSX.Element => (
  <NextComponent {...props} />
);

export default identity;
