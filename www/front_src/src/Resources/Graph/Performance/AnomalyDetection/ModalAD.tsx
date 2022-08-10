import { Dispatch, SetStateAction } from 'react';

import { useUpdateAtom } from 'jotai/utils';

import Modal from '@mui/material/Modal';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import makeStyles from '@mui/styles/makeStyles';
import Paper from '@mui/material/Paper';

import TimePeriodButtonGroup from '../TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../ExportableGraphWithTimeline';
import { Resource } from '../../../models';
import { ResourceDetails } from '../../../Details/models';

import { openModalADAtom } from './anomalyDetectionAtom';

interface Props {
  details: Resource | ResourceDetails;
  isOpen: boolean;
  setIsOpen: Dispatch<SetStateAction<boolean>>;
}

const useStyles = makeStyles((theme) => ({
  close: {
    alignItems: 'flex-end',
    display: 'flex',
    justifyContent: 'flex-end',
  },
  container: {
    backgroundColor: theme.palette.background.default,
    left: '50%',
    padding: theme.spacing(2),
    position: 'absolute',
    top: '50%',
    transform: 'translate(-50%, -50%)',
    width: '90%',
  },
  envelop: {
    flex: 1,
    height: theme.spacing(15),
    marginRight: theme.spacing(1),
  },
  exclusionPeriod: {
    flex: 2,
    height: theme.spacing(20),
    marginLeft: theme.spacing(1),
  },
  footer: {
    display: 'flex',
    justifyContent: 'space-between',
  },
  spacing: {
    paddingBottom: theme.spacing(1),
  },
}));

const ModalAD = ({ isOpen, setIsOpen, details }: Props): JSX.Element => {
  const classes = useStyles();

  const setIsOpenedModalAD = useUpdateAtom(openModalADAtom);

  const handleClose = (): void => {
    setIsOpenedModalAD(false);
    setIsOpen(false);
  };

  return (
    <Modal open={isOpen}>
      <Box className={classes.container}>
        <div className={classes.spacing}>
          <TimePeriodButtonGroup />
        </div>
        <div className={classes.spacing}>
          <ExportablePerformanceGraphWithTimeline
            graphHeight={180}
            resource={details}
          />
        </div>
        <div className={classes.footer}>
          <Paper className={classes.envelop}>Manage envelope size</Paper>
          <Paper className={classes.exclusionPeriod}>
            Exclusion of periods
          </Paper>
          <div className={classes.close}>
            <Button onClick={handleClose}>Close</Button>
          </div>
        </div>
      </Box>
    </Modal>
  );
};

export default ModalAD;
