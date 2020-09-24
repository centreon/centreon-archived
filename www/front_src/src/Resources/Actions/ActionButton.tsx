import * as React from 'react';

import { Button, ButtonProps } from '@material-ui/core';

const ActionButton = (props: ButtonProps): JSX.Element => (
  <Button color="primary" size="small" {...props} />
);

export default ActionButton;
