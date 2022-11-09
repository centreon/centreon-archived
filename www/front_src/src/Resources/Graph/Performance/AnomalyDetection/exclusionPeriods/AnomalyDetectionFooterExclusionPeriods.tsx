import { makeStyles } from 'tss-react/mui';

import { Button } from '@mui/material';

const useStyles = makeStyles()((theme) => ({
  confirmButton: {
    marginLeft: theme.spacing(2),
  },
  container: { margin: theme.spacing(0, 0, 2, 0) },
  footer: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
}));

const AnomalyDetectionFooterExclusionPeriods = ({
  setOpen,
  confirmExcluderPeriods,
  isError,
  dateExisted,
}: any): JSX.Element => {
  const { classes } = useStyles();

  const cancel = (): void => {
    console.log('cancel');
    setOpen(false);
  };

  const confirm = (): void => {
    console.log('save');
  };

  return (
    <div className={classes.footer}>
      <Button data-testid="cancel" size="small" variant="text" onClick={cancel}>
        Cancel
      </Button>
      <Button
        className={classes.confirmButton}
        data-testid="save"
        disabled={isError || !dateExisted}
        size="small"
        variant="contained"
        onClick={confirmExcluderPeriods}
      >
        Confirm
      </Button>
    </div>
  );
};

export default AnomalyDetectionFooterExclusionPeriods;
