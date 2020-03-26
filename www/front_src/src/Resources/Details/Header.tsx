import * as React from 'react';

import {
  Grid,
  Typography,
  IconButton,
  makeStyles,
  Theme,
  fade,
} from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import IconClose from '@material-ui/icons/Clear';
import { CreateCSSProperties } from '@material-ui/core/styles/withStyles';

import { StatusChip, SeverityCode } from '@centreon/ui';

import { DetailsSectionProps, ResourceDetails } from '.';
import { rowColorConditions } from '../colors';

const useStyles = makeStyles<Theme, ResourceDetails>((theme) => ({
  header: ({ downtimes, acknowledgement }): CreateCSSProperties => {
    const backgroundColor = rowColorConditions.find(({ condition }) =>
      condition({
        in_downtime: downtimes !== undefined,
        acknowledged: acknowledgement !== undefined,
      }),
    )?.color;

    return {
      backgroundColor: backgroundColor
        ? fade(backgroundColor, 0.2)
        : theme.palette.common.white,
    };
  },
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container spacing={2} alignItems="center">
    <Grid item>
      <Skeleton variant="circle" width={25} height={25} />
    </Grid>
    <Grid item>
      <Skeleton width={250} height={25} />
    </Grid>
  </Grid>
);

type HeaderProps = DetailsSectionProps & { onClickClose };

const Header = ({ details, onClickClose }: HeaderProps): JSX.Element => {
  const classes = useStyles(details);

  const loading = details === undefined;

  return (
    <Grid
      container
      item
      spacing={2}
      alignItems="center"
      className={classes.header}
    >
      {loading ? (
        <Grid item style={{ flexGrow: 1 }}>
          <LoadingSkeleton />
        </Grid>
      ) : (
        <>
          <Grid item>
            <StatusChip
              severityCode={SeverityCode.None}
              label={details.criticality?.toString()}
            />
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
                <Typography>{details.name}</Typography>
              </Grid>
              {details.parent && (
                <Grid item container spacing={1}>
                  <Grid item>
                    <StatusChip
                      severityCode={details.parent.status.severity_code}
                    />
                  </Grid>
                  <Grid item>
                    <Typography variant="caption">
                      {details.parent.name}
                    </Typography>
                  </Grid>
                </Grid>
              )}
            </Grid>
          </Grid>
        </>
      )}
      <Grid item>
        <IconButton onClick={onClickClose}>
          <IconClose />
        </IconButton>
      </Grid>
    </Grid>
  );
};

export default Header;
