import * as React from 'react';

import { Grid, Typography, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import { StatusChip, SeverityCode } from '@centreon/ui';

import { DetailsSectionProps } from '.';

const useStyles = makeStyles(() => ({
  header: {
    height: 60,
  },
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container item alignItems="center" spacing={2} style={{ flexGrow: 1 }}>
    <Grid item>
      <Skeleton height={25} variant="circle" width={25} />
    </Grid>
    <Grid item>
      <Skeleton height={25} width={250} />
    </Grid>
  </Grid>
);

const HeaderContent = ({ details }: DetailsSectionProps): JSX.Element => {
  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  return (
    <>
      <Grid item>
        {details.criticality && (
          <StatusChip
            label={details.criticality.toString()}
            severityCode={SeverityCode.None}
          />
        )}
      </Grid>
      <Grid item>
        <StatusChip
          label={details.status.name}
          severityCode={details.status.severity_code}
        />
      </Grid>
      <Grid item style={{ flexGrow: 1 }}>
        <Grid container direction="column">
          <Grid item>
            <Typography>{details.display_name}</Typography>
          </Grid>
          {details.parent && (
            <Grid container item spacing={1}>
              <Grid item>
                <StatusChip
                  severityCode={details.parent.status?.severity_code}
                />
              </Grid>
              <Grid item>
                <Typography variant="caption">{details.parent.name}</Typography>
              </Grid>
            </Grid>
          )}
        </Grid>
      </Grid>
    </>
  );
};

const Header = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid
      container
      item
      alignItems="center"
      className={classes.header}
      spacing={2}
    >
      <HeaderContent details={details} />
    </Grid>
  );
};

export default Header;
