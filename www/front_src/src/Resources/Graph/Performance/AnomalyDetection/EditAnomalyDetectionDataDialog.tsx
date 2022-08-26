import {
  Dispatch,
  SetStateAction,
  ReactNode,
  useState,
  cloneElement,
} from 'react';

import Button from '@mui/material/Button';
import makeStyles from '@mui/styles/makeStyles';
import Paper from '@mui/material/Paper';
import Dialog from '@mui/material/Dialog';

import TimePeriodButtonGroup from '../TimePeriods';

import AnomalyDetectionSlider from './AnomalyDetectionSlider';

interface Props {
  children?: (args: { isResizeEnvelope: boolean }) => ReactNode;
  isOpen: boolean;
  setIsOpen: Dispatch<SetStateAction<boolean>>;
}

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

const EditAnomalyDetectionDataDialog = ({
  isOpen,
  setIsOpen,
  children,
}: Props): JSX.Element => {
  const classes = useStyles();
  const [isResizeEnvelope, setIsResizeEnvelope] = useState(false);

  const handleClose = (): void => {
    setIsOpen(false);
  };

  const getIsResizeEnvelope = (value: boolean): void => {
    setIsResizeEnvelope(value);
  };

  return (
    <Dialog className={classes.container} open={isOpen}>
      <div>
        <div className={classes.spacing}>
          <TimePeriodButtonGroup />
        </div>
        <div className={classes.spacing}>
          {children && children({ isResizeEnvelope })}
        </div>
        <div className={classes.editEnvelopeSize}>
          <Paper className={classes.envelopeSize}>
            {/* send action of resize */}
            <AnomalyDetectionSlider getIsResizeEnvelope={getIsResizeEnvelope} />
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
