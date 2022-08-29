import { Dispatch, SetStateAction, ReactNode, useState } from 'react';

import Button from '@mui/material/Button';
import makeStyles from '@mui/styles/makeStyles';
import Paper from '@mui/material/Paper';
import Dialog from '@mui/material/Dialog';

import TimePeriodButtonGroup from '../TimePeriods';

import AnomalyDetectionSlider from './AnomalyDetectionSlider';

const useStyles = makeStyles((theme) => ({
  close: {
    display: 'flex',
    justifyContent: 'flex-end',
  },
  container: {
    '& .MuiDialog-paper': {
      backgroundColor: theme.palette.background.default,
      maxWidth: '80%',
      padding: theme.spacing(2),
      width: '100%',
    },
  },
  editEnvelopeSize: {
    display: 'flex',
    justifyContent: 'space-between',
  },
  envelopeSize: {
    flex: 1,
    marginRight: theme.spacing(1),
  },
  exclusionPeriod: {
    flex: 2,
    height: theme.spacing(20),
    marginLeft: theme.spacing(1),
  },
  spacing: {
    paddingBottom: theme.spacing(1),
  },
}));

interface Props {
  children?: (args: { factorsData: any }) => ReactNode;
  isOpen: boolean;
  setIsOpen: Dispatch<SetStateAction<boolean>>;
}

const EditAnomalyDetectionDataDialog = ({
  isOpen,
  setIsOpen,
  children,
}: Props): JSX.Element => {
  const classes = useStyles();

  const [factorsData, setFactorsData] = useState(null);

  const handleClose = (): void => {
    setIsOpen(false);
  };

  const getFactors = (data): void => {
    setFactorsData(data);
  };

  return (
    <Dialog className={classes.container} open={isOpen}>
      <div>
        <div className={classes.spacing}>
          <TimePeriodButtonGroup />
        </div>
        <div className={classes.spacing}>
          {children && children({ factorsData })}
        </div>
        <div className={classes.editEnvelopeSize}>
          <Paper className={classes.envelopeSize}>
            <AnomalyDetectionSlider getFactors={getFactors} />
          </Paper>
          <Paper className={classes.exclusionPeriod}>
            Exclusion of periods
          </Paper>
        </div>
        <div className={classes.close}>
          <Button onClick={handleClose}>Close</Button>
        </div>
      </div>
    </Dialog>
  );
};

export default EditAnomalyDetectionDataDialog;
