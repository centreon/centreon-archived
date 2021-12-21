import * as React from 'react';

import { useTheme, Grid } from '@mui/material';

import { Props } from './Refresh';
import GlobalActionsSkeleton from './GlobalActionsSkeleton';
import ResourceActionsSkeleton from './ResourceActionsSkeleton';

const ResourceActions = React.lazy(() => import('./Resource'));
const GlobalActions = React.lazy(() => import('./Refresh'));

const Actions = ({ onRefresh }: Props): JSX.Element => {
  const theme = useTheme();

  return (
    <Grid container>
      <Grid item>
        <React.Suspense fallback={<ResourceActionsSkeleton />}>
          <ResourceActions />
        </React.Suspense>
      </Grid>
      <Grid item style={{ paddingLeft: theme.spacing(3) }}>
        <React.Suspense fallback={<GlobalActionsSkeleton />}>
          <GlobalActions onRefresh={onRefresh} />
        </React.Suspense>
      </Grid>
    </Grid>
  );
};

export default Actions;
