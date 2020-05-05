import * as React from 'react';

import { isEmpty } from 'ramda';

import { useTheme, Grid } from '@material-ui/core';

import ResourceActions from './Resource';
import GlobalActions from './Refresh';
import { useResourceContext } from '../Context';

const Actions = ({ onRefresh }): JSX.Element => {
  const theme = useTheme();

  const { setEnabledAutorefresh, enabledAutorefresh } = useResourceContext();

  const toggleAutorefresh = (): void => {
    setEnabledAutorefresh(!enabledAutorefresh);
  };

  return (
    <Grid container>
      <Grid item>
        <ResourceActions />
      </Grid>
      <Grid item style={{ paddingLeft: theme.spacing(3) }}>
        <GlobalActions
          enabledAutorefresh={enabledAutorefresh}
          onRefresh={onRefresh}
          toggleAutorefresh={toggleAutorefresh}
        />
      </Grid>
    </Grid>
  );
};

export default Actions;
