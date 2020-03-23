import * as React from 'react';

import { Grid, Typography, IconButton } from '@material-ui/core';
import IconClose from '@material-ui/icons/Clear';

import { StatusChip, SeverityCode } from '@centreon/ui';
import { DetailsSectionProps } from '.';

type HeaderProps = DetailsSectionProps & { onClickClose };

const Header = ({ details, onClickClose }: HeaderProps): JSX.Element => (
  <Grid container item spacing={2} alignItems="center">
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
              <StatusChip severityCode={details.parent.status.severity_code} />
            </Grid>
            <Grid item>
              <Typography variant="caption">{details.parent.name}</Typography>
            </Grid>
          </Grid>
        )}
      </Grid>
    </Grid>
    <Grid item>
      <IconButton onClick={onClickClose}>
        <IconClose />
      </IconButton>
    </Grid>
  </Grid>
);

export default Header;
