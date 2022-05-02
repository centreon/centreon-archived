import { lazy, Suspense } from 'react';

import { useTheme, Grid } from '@mui/material';

import { Props } from './Refresh';
import GlobalActionsSkeleton from './GlobalActionsSkeleton';
import ResourceActionsSkeleton from './ResourceActionsSkeleton';

const ResourceActions = lazy(() => import('./Resource'));
const GlobalActions = lazy(() => import('./Refresh'));

const Actions = ({ onRefresh }: Props): JSX.Element => {
  const theme = useTheme();

  return (
    <Grid container>
      <Grid item>
        <Suspense fallback={<ResourceActionsSkeleton />}>
          <ResourceActions />
        </Suspense>
      </Grid>
      <Grid item style={{ paddingLeft: theme.spacing(3) }}>
        <Suspense fallback={<GlobalActionsSkeleton />}>
          <GlobalActions onRefresh={onRefresh} />
        </Suspense>
      </Grid>
    </Grid>
  );
};

export default Actions;
