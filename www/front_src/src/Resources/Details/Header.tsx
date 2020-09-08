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
  <Grid container spacing={2} alignItems="center" item style={{ flexGrow: 1 }}>
    <Grid item>
      <Skeleton variant="circle" width={25} height={25} />
    </Grid>
    <Grid item>
      <Skeleton width={250} height={25} />
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
        {details.severity && (
          <StatusChip
            severityCode={SeverityCode.None}
            label={details.severity.level?.toString()}
          />
        )}
      </Grid>
      <Grid item>
        <StatusChip
          severityCode={details.status.severity_code}
          label={details.status.name}
        />
      </Grid>
      <Grid item style={{ flexGrow: 1 }}>
        <Grid container direction="column">
          <Grid item>
            <Typography>{details.display_name}</Typography>
          </Grid>
          {details.parent && (
            <Grid item container spacing={1}>
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
      spacing={2}
      alignItems="center"
      className={classes.header}
    >
      <HeaderContent details={details} />
    </Grid>
  );
};

export default Header;
