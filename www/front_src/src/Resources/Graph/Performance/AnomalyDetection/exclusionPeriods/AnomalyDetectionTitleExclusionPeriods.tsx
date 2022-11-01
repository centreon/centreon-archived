import { makeStyles } from 'tss-react/mui';

import { Divider, Typography } from '@mui/material';

const useStyles = makeStyles()((theme) => ({
  container: { margin: theme.spacing(0, 0, 2, 0) },
  divider: {},
}));

const AnomalyDetectionTitleExclusionPeriods = (): JSX.Element => {
  const { classes } = useStyles();

  return (
    <div className={classes.container}>
      <Typography variant="h6">Exclusion of periods</Typography>
      <Typography variant="caption">
        Attention, the excluded of periods will be applied immediately.
      </Typography>
      <Divider className={classes.divider} />
    </div>
  );
};

export default AnomalyDetectionTitleExclusionPeriods;
