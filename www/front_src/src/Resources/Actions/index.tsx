import * as React from 'react';

import { useTheme, Grid } from '@material-ui/core';

import ResourceActions from './Resource';
import GlobalActions, { Props } from './Refresh';

const Actions = ({ onRefresh }: Props): JSX.Element => {
  const theme = useTheme();

  return (
    <Grid container>
      <Grid item>
        <ResourceActions />
      </Grid>
      <Grid item style={{ paddingLeft: theme.spacing(3) }}>
        <GlobalActions onRefresh={onRefresh} />
      </Grid>
    </Grid>
  );
};

export default Actions;
